<?php

namespace NetglueMailTest;

use NetglueMail\ConfigProvider;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\PhpFileProvider;


class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected static $container;


    public static function setUpBeforeClass()
    {
        $configManager = new ConfigManager([
            ConfigProvider::class,
            new PhpFileProvider(__DIR__ . '/../config/{{,*.}global,{,*.}local}.php'),
        ]);
        $config = $configManager->getMergedConfig();
        $config['debug']                = true;
        $config['config_cache_enabled'] = false;

        // Build container
        $container = new ServiceManager();
        (new Config($config['dependencies']))->configureServiceManager($container);

        // Inject config
        $container->setService('config', $config);

        self::$container = $container;
    }

    public static function tearDownAfterClass()
    {
        // Clean up
        self::$container = null;
    }

}
