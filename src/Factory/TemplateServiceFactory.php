<?php

namespace NetglueMail\Factory;

use NetglueMail\TemplateService;
use NetglueMail\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateServiceFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : TemplateService
    {

        $options  = $container->get(ModuleOptions::class);

        /**
         * @var Zend\Expressive\ZendView\ZendViewRenderer
         */
        $renderer = $container->get(TemplateRendererInterface::class);

        return new TemplateService($options, $renderer);
    }
}
