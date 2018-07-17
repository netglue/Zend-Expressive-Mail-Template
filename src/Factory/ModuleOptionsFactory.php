<?php
declare(strict_types=1);

namespace NetglueMail\Factory;

use NetglueMail\ModuleOptions;
use Psr\Container\ContainerInterface;

class ModuleOptionsFactory
{
    public function __invoke(ContainerInterface $container) : ModuleOptions
    {
        return new ModuleOptions($container->get('config')['netglue_mail']);
    }
}
