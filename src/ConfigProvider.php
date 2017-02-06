<?php

namespace NetglueMail;
use Zend\Expressive\Template\TemplateRendererInterface;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig()
    {
        return [
            'factories' => [
                Dispatcher::class      => Factory\DispatcherFactory::class,
                ModuleOptions::class   => Factory\ModuleOptionsFactory::class,
                TemplateService::class => Factory\TemplateServiceFactory::class,
            ],
            'aliases' => [
                'NetglueMail\TemplateRendererInterface' => TemplateRendererInterface::class,
            ],
        ];
    }
}
