<?php
declare(strict_types=1);

namespace NetglueMail;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventsCapableInterface;
use Zend\Mail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime;

class Dispatcher implements EventsCapableInterface, EventManagerAwareInterface
{

    use EventManagerAwareTrait;

    /**
     *
     * @var ModuleOptions
     */
    private $options;

    /**
     *
     * @var TemplateService
     */
    private $templateService;

    /**
     *
     * @var TransportInterface
     */
    private $transport;

    public function __construct(
        TransportInterface $transport,
        TemplateService $templateService,
        ModuleOptions $options
    ) {
        $this->transport = $transport;
        $this->templateService = $templateService;
        $this->options = $options;
    }

    /**
     * Create the Mail Message for the given type using the view variables or model provided
     *
     * The method sets up any configured headers, recipients,
     * senders etc and returns the message for further manipulation
     *
     * @param  string $messageName Configured Message Name
     * @param  array  $options     Message options such as recipient, from, headers etc
     * @param  array  $viewParams  An array of view variables
     * @return Mail\Message
     */
    public function createMessage(string $messageName, array $options = [], ?array $viewParams = null) : Mail\Message
    {
        $html = $this->templateService->renderTemplate($messageName, $viewParams);
        $text = $this->templateService->renderTextTemplate($messageName, $viewParams);

        $charset = isset($options['charset']) ? $options['charset'] : 'utf-8';
        $mimeBody = $this->createMimeBody($html, $text, $charset);

        $options = array_merge($this->options->getMessageConfig($messageName), $options);
        $options['body'] = $mimeBody;

        $message = $this->prepareMessage($options);

        return $message;
    }

    public function send(string $messageName, array $options = [], ?array $viewParams = null) : Mail\Message
    {
        $message = $this->createMessage($messageName, $options, $viewParams);
        $eventParams = [
            'messageName' => $messageName,
        ];
        $this->sendMessage($message, $eventParams);
        return $message;
    }

    public function sendMessage(Mail\Message $message, array $eventParams = []) : void
    {
        $eventParams = array_merge(
            $eventParams,
            [
                'message' => $message,
            ]
        );
        $this->getEventManager()->trigger(__FUNCTION__, $this, $eventParams);
        $this->transport->send($message);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, $eventParams);
    }

    private function prepareMessageHeaders(array &$options) : void
    {
        // Convert headers as an array to header list object
        if (! isset($options['headers'])) {
            $options['headers'] = [];
        }
        $options['headers'] = array_merge($this->options->getDefaultHeaders(), $options['headers']);
        $headers = new Mail\Headers;
        $headers->addHeaders($options['headers']);
        $options['headers'] = $headers;
    }

    private function setDefaultSender(array &$options) : void
    {
        if (! isset($options['from']) || empty($options['from'])) {
            $from = $this->options->getDefaultSender();
            $fromName = $this->options->getDefaultSenderName();
            $options['from'] = [
                $from => $fromName,
            ];
        }
    }

    /**
     * Create and return a ready to send Mail\Message based on options
     *
     * @param  array $options
     * @return Mail\Message
     */
    private function prepareMessage(array $options = []) : Mail\Message
    {
        $this->prepareMessageHeaders($options);
        $this->setDefaultSender($options);

        /**
         * Set Recipients and senders that could be multiple addresses
         */
        $addressOptions = ['to', 'cc', 'bcc', 'from', 'replyTo'];
        foreach ($addressOptions as $type) {
            if (isset($options[$type])) {
                $who = $options[$type];
                if (empty($who)) {
                    unset($options[$type]);
                    continue;
                }
                $list = new Mail\AddressList;
                if (is_array($who)) {
                    $list->addMany($who);
                } elseif (is_string($who)) {
                    $list->addFromString($who);
                }
                $options[$type] = $list;
            }
        }
        /**
         * The options must be ordered so headers go first at the very least
         * otherwise a call to setHeaders could wipe out all other settings
         */
        $optionsFormat = [
            'headers',
            'subject',
            'body',
            'to',
            'cc',
            'bcc',
            'from',
            'sender',
            'encoding',
            'replyTo',
        ];
        $message = [];
        foreach ($optionsFormat as $name) {
            if (isset($options[$name])) {
                $message[$name] = $options[$name];
            }
        }

        $message = Mail\MessageFactory::getInstance($message);
        // Name of Sender will not be set as there is no way of doing this via the MessageFactory
        if ($message->getSender() && isset($options['senderName'])) {
            $message->setSender(new Mail\Address($message->getSender()->getEmail(), $options['senderName']));
        }

        $this->addAttachments($message, $options);

        return $message;
    }

    /**
     * Add any attachments
     *
     * @param  Mail\Message $message
     * @param  array        $options
     * @return void
     */
    private function addAttachments(Mail\Message $message, array $options)
    {
        if (isset($options['attachments'])) {
            foreach ($options['attachments'] as $name => $attachmentPath) {
                $fileContent = fopen($attachmentPath, 'r');
                $attachment = new Mime\Part($fileContent);
                $attachment->filename = $name;
                $attachment->type = $this->getMimeType($attachmentPath);
                $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding    = Mime\Mime::ENCODING_BASE64;
                $message->getBody()->addPart($attachment);
            }
        }
    }

    /**
     * Find mimetype of specified file
     *
     * @param  string $filePath
     * @return mixed
     */
    private function getMimeType($filePath)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $filePath);
    }

    /**
     * Create multipart mime message with strings
     *
     * @param string $html
     * @param string $text
     * @param string $charset
     * @return Mime\Message
     */
    private function createMimeBody(
        ?string $html = null,
        ?string $text = null,
        string $charset = 'utf-8'
    ) : Mime\Message {
        $mime = new Mime\Message;
        if ($text) {
            $mime->addPart($this->createTextPart($text, $charset));
        }
        if ($html) {
            $mime->addPart($this->createHtmlPart($html, $charset));
        }
        return $mime;
    }

    /**
     * Create html mime part with string
     *
     * @param string $markup
     * @param string $charset
     * @return Mime\Part
     */
    private function createHtmlPart(string $markup, string $charset = 'utf-8') : Mime\Part
    {
        $html = new Mime\Part($markup);
        $html->type = sprintf('text/html; charset="%s"', $charset);

        return $html;
    }

    /**
     * Create text mime part with string
     *
     * @param string $text
     * @param string $charset
     * @return Mime\Part
     */
    private function createTextPart(string $text, string $charset = 'utf-8') : Mime\Part
    {
        $part = new Mime\Part($text);
        $part->type = sprintf('text/plain; charset="%s"', $charset);

        return $part;
    }
}
