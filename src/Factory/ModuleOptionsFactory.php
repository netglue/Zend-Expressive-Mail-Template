<?php

namespace NetglueMail\Factory;
use Interop\Container\ContainerInterface;
use NetglueMail\ModuleOptions;

class ModuleOptionsFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ModuleOptions
    {
        $config = $container->get('config');
        $config = isset($config['netglue_mail']) ? $config['netglue_mail'] : [];
        return new ModuleOptions($config);
    }
}
