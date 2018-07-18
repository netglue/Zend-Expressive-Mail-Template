<?php
declare(strict_types=1);

namespace NetglueMail;

use Zend\Expressive\Template\TemplateRendererInterface;

class ConfigProvider
{

    public const EMPTY_LAYOUT_TEMPLATE = 'layout::emailLayoutNone';

    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'netglue_mail' => $this->getModuleOptions(),
            'templates'    => $this->getTemplateConfig(),
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
            'emptyLayoutTemplate' => self::EMPTY_LAYOUT_TEMPLATE,
            'defaultHeaders' => [],
            'messages' => [],
        ];
    }

    public function getTemplateConfig() : array
    {
        return [
            'map' => [
                self::EMPTY_LAYOUT_TEMPLATE => __DIR__ . '/../templates/empty-layout.phtml',
            ],
        ];
    }
}
