<?php
declare(strict_types=1);

namespace NetglueMail;

use NetglueMail\Exception\InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     *
     * @var string|null
     */
    private $defaultSenderName;

    /**
     *
     * @var string|null
     */
    private $defaultSender;

    /**
     *
     * @var array
     */
    private $defaultHeaders = [];

    /**
     *
     * @var array
     */
    private $messages = [];

    /**
     *
     * @var string|null
     */
    private $defaultLayout;

    /**
     *
     * @var string|null
     */
    private $textLayout;

    /**
     *
     * @var string|null
     */
    private $transport;

    /** @var string */
    private $emptyLayoutTemplate = ConfigProvider::EMPTY_LAYOUT_TEMPLATE;

    public function setDefaultSenderName(string $name) : void
    {
        $name = empty($name) ? null : $name;
        $this->defaultSenderName = $name;
    }

    public function getDefaultSenderName() :? string
    {
        return $this->defaultSenderName;
    }

    public function setDefaultSender(string $email) : void
    {
        $email = trim(strtolower($email));
        $this->defaultSender = empty($email) ? null : $email;
    }

    public function getDefaultSender() :? string
    {
        return $this->defaultSender;
    }

    public function setDefaultHeaders(array $headers) : void
    {
        $this->defaultHeaders = $headers;
    }

    public function getDefaultHeaders() : array
    {
        return $this->defaultHeaders;
    }

    public function setMessages(array $messages) : void
    {
        $this->messages = [];
        foreach ($messages as $name => $config) {
            $this->addMessageConfig($name, $config);
        }
    }

    public function getMessages() : array
    {
        return $this->messages;
    }

    private function addMessageConfig($name, $config) : void
    {
        if (! \is_string($name)) {
            throw new InvalidArgumentException('Message configuration should be keyed with a string');
        }
        if (! is_array($config)) {
            throw new InvalidArgumentException('Message configuration should be an array');
        }
        $this->messages[$name] = $config;
    }

    /**
     * Return config for a specific message
     *
     * @param  string $name
     * @return array|null
     */
    public function getMessageConfig(string $name) :? array
    {
        return isset($this->messages[$name]) ? $this->messages[$name] : null;
    }

    /**
     * Return the value of a specific message option
     *
     * @param  string $messageName
     * @param  string $option
     * @param  mixed  $default
     * @return mixed
     */
    public function getMessageOption(string $messageName, string $option, $default = null)
    {
        $config = $this->getMessageConfig($messageName);
        if ($config) {
            return (isset($config[$option])) ? $config[$option] : $default;
        }
        return $default;
    }

    public function setDefaultLayout(string $layout) : void
    {
        $layout = empty($layout) ? null : $layout;
        $this->defaultLayout = $layout;
    }

    public function getDefaultLayout() :? string
    {
        return $this->defaultLayout;
    }

    public function setTextLayout(string $layout) : void
    {
        $layout = empty($layout) ? null : $layout;
        $this->textLayout = $layout;
    }

    public function getTextLayout() :? string
    {
        return $this->textLayout;
    }

    public function setTransport(string $transport) : void
    {
        $transport = empty($transport) ? null : $transport;
        $this->transport = $transport;
    }

    /**
     * Return transport name
     *
     * @return string|null
     */
    public function getTransport() :? string
    {
        return $this->transport;
    }

    public function setEmptyLayoutTemplate(string $templateName) : void
    {
        $this->emptyLayoutTemplate = $templateName;
    }

    public function getEmptyLayoutTemplate() : string
    {
        return $this->emptyLayoutTemplate;
    }
}
