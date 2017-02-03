<?php

namespace NetglueMail;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    /**
     * @var string Default Sender Name
     */
    private $defaultSenderName;

    /**
     * @var string Default Sender Email address
     */
    private $defaultSender;

    /**
     * Header set for every outbound message
     * @var array
     */
    private $defaultHeaders = [];

    /**
     * Message configuration
     * @var array
     */
    private $messages = [];

    /**
     * Default Layout Template
     * @var string|null
     */
    private $defaultLayout;

    /**
     * Default Plain Text Layout Template
     * @var string|null
     */
    private $textLayout;

    /**
     * Transport Name
     * @var string|null
     */
    private $transport;

    /**
     * @param string $name
     * @return void
     */
    public function setDefaultSenderName($name)
    {
        $name = empty($name) ? null : (string) $name;
        $this->defaultSenderName = $name;
    }

    /**
     * @return string|null
     */
    public function getDefaultSenderName()
    {
        return $this->defaultSenderName;
    }

    /**
     * @param string $email
     * @return void
     */
    public function setDefaultSender($email)
    {
        $email = trim(strtolower($email));
        $this->defaultSender = empty($email) ? null : $email;
    }

    /**
     * @return string|null
     */
    public function getDefaultSender()
    {
        return $this->defaultSender;
    }

    /**
     * @param array|Traversable $headers
     * @return void
     */
    public function setDefaultHeaders($headers)
    {
        $this->defaultHeaders = $headers;
    }

    /**
     * @return array
     */
    public function getDefaultHeaders()
    {
        return $this->defaultHeaders;
    }

    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Return config for a specific message
     * @param string $name
     * @return array|null
     */
    public function getMessageConfig($name)
    {
        return isset($this->messages[$name]) ? $this->messages[$name] : null;
    }

    /**
     * Return the value of a specific message option
     * @param string $messageName
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    public function getMessageOption($messageName, $option, $default = null)
    {
        if(!is_string($option)) {
            throw new Exception\InvalidArgumentException('Option name must be a string');
        }
        if ($config = $this->getMessageConfig($messageName)) {
            if (isset($config[$option])) {
                return $config[$option];
            }
        }

        return $default;
    }


    /**
     * Layout
     * @param string $layout
     * @return void
     */
    public function setDefaultLayout($layout)
    {
        $layout = empty($layout) ? null : (string) $layout;
        $this->defaultLayout = $layout;
    }

    /**
     * @return string|null
     */
    public function getDefaultLayout()
    {
        return $this->defaultLayout;
    }

    /**
     * Layout
     * @param string $layout
     * @return void
     */
    public function setTextLayout($layout)
    {
        $layout = empty($layout) ? null : (string) $layout;
        $this->textLayout = $layout;
    }

    /**
     * @return string|null
     */
    public function getTextLayout()
    {
        return $this->textLayout;
    }

    /**
     * Set transport name
     * @param string $transport
     * @return void
     */
    public function setTransport($transport)
    {
        $transport = empty($transport) ? null : (string) $transport;
        $this->transport = $transport;
    }

    /**
     * Return transport name
     * @return string|null
     */
    public function getTransport()
    {
        return $this->transport;
    }
}
