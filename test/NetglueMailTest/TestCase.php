<?php
declare(strict_types=1);

namespace NetglueMailTest;

use Psr\Container\ContainerInterface;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;
use Zend\Expressive\ZendView;
use Zend\ServiceManager\ServiceManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var ContainerInterface */
    protected $container;

    public function setUp()
    {
        parent::setUp();
        $config = $this->getModuleConfig();
        $dependencies = $config['dependencies'];
        $dependencies['services']['config'] = $config;
        $this->container = new ServiceManager($dependencies);
    }

    protected function getModuleConfig() : array
    {
        $aggregator = new ConfigAggregator([
            ZendView\ConfigProvider::class,
            \NetglueMail\ConfigProvider::class,
            new PhpFileProvider(realpath(__DIR__) . '/../config/config.global.php')
        ]);
        return $aggregator->getMergedConfig();
    }
}
