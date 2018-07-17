<?php
declare(strict_types=1);

namespace NetglueMail\Factory;

use NetglueMail\MailTemplateRendererInterface;
use NetglueMail\TemplateService;
use NetglueMail\ModuleOptions;
use Psr\Container\ContainerInterface;

class TemplateServiceFactory
{
    public function __invoke(ContainerInterface $container) : TemplateService
    {
        return new TemplateService(
            $container->get(ModuleOptions::class),
            $container->get(MailTemplateRendererInterface::class)
        );
    }
}
