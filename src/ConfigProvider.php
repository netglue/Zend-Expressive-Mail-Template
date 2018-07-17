<?php
declare(strict_types=1);

namespace NetglueMail;

use Zend\Expressive\Template\TemplateRendererInterface;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'netglue_mail' => $this->getModuleOptions(),
        ];
    }

    public function getDependencyConfig() : array
    {
        return [
            'factories' => [
                Dispatcher::class      => Factory\DispatcherFactory::class,
                ModuleOptions::class   => Factory\ModuleOptionsFactory::class,
                TemplateService::class => Factory\TemplateServiceFactory::class,
            ],
            'aliases' => [
                MailTemplateRendererInterface::class => TemplateRendererInterface::class,
            ],
        ];
    }

    public function getModuleOptions() : array
    {
        return [
            'defaultHeaders' => [],
            'messages' => [],
        ];
    }
}
