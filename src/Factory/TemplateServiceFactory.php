<?php

namespace NetglueMail\Factory;

use NetglueMail\TemplateService;
use NetglueMail\ModuleOptions;
use Interop\Container\ContainerInterface;

class TemplateServiceFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : TemplateService
    {

        $options  = $container->get(ModuleOptions::class);

        /**
         * Use an alias to get the template renderer interface so that it's easier to
         * swap out if you want to use a different engine for mail vs web.
         */
        $renderer = $container->get('NetglueMail\TemplateRendererInterface');

        return new TemplateService($options, $renderer);
    }
}
