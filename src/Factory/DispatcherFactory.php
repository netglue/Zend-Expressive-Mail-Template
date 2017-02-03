<?php

namespace NetglueMail\Factory;

use Interop\Container\ContainerInterface;
use NetglueMail\Dispatcher;
use NetglueMail\TemplateService;
use NetglueMail\ModuleOptions;
use Zend\Mail\Transport\InMemory;

class DispatcherFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Dispatcher
    {
        $templateService = $container->get(TemplateService::class);
        $options         = $container->get(ModuleOptions::class);
        $transportName   = $options->getTransport();

        $transport = (null === $transportName) ?
            new InMemory :
            $container->get($transportName);

        return new Dispatcher($transport, $templateService);
    }
}
