<?php
declare(strict_types=1);

namespace NetglueMail\Factory;

use NetglueMail\Dispatcher;
use NetglueMail\ModuleOptions;
use NetglueMail\TemplateService;
use Psr\Container\ContainerInterface;
use Zend\Mail\Transport\InMemory;

class DispatcherFactory
{
    public function __invoke(ContainerInterface $container) : Dispatcher
    {
        /**
         *
 * @var ModuleOptions $options
*/
        $options         = $container->get(ModuleOptions::class);
        $transportName   = $options->getTransport();

        $transport = (null === $transportName) ?
            new InMemory :
            $container->get($transportName);

        return new Dispatcher(
            $transport,
            $container->get(TemplateService::class),
            $options
        );
    }
}
