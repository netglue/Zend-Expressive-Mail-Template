<?php
declare(strict_types=1);

namespace NetglueMailTest;

use NetglueMail\ConfigProvider;
use NetglueMail\Factory\ModuleOptionsFactory;
use NetglueMail\ModuleOptions;
use Psr\Container\ContainerInterface;

class ModuleOptionsTest extends TestCase
{

    public function testFactory()
    {
        $config = (new ConfigProvider())();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config);

        $factory = new ModuleOptionsFactory();
        $options = ($factory)($container->reveal());

        $this->assertInstanceOf(ModuleOptions::class, $options);
    }

    public function testBasic()
    {
        $config = [
            'default_sender_name' => 'DEFAULT SENDER NAME',
            'default_sender'      => 'DEFAULT SENDER',
            'default_headers'     => ['HEADER NAME' => 'HEADER VALUE'],
            'messages'            => ['someMessage' => ['OPTION' => 'VALUE']],
            'default_layout'      => 'LAYOUT',
            'text_layout'         => 'TEXT',
            'transport'           => 'TRANSPORT',
        ];
        $options = new ModuleOptions($config);
        $this->assertSame('DEFAULT SENDER NAME', $options->getDefaultSenderName());
        $this->assertSame('default sender', $options->getDefaultSender());
        $this->assertInternalType('array', $options->getMessages());
        $this->assertNull($options->getMessageConfig('wrong'));
        $this->assertInternalType('array', $options->getMessageConfig('someMessage'));
        $this->assertSame('VALUE', $options->getMessageOption('someMessage', 'OPTION'));
        $this->assertInternalType('array', $options->getDefaultHeaders());
        $this->assertSame('LAYOUT', $options->getDefaultLayout());
        $this->assertSame('TEXT', $options->getTextLayout());
        $this->assertSame('TRANSPORT', $options->getTransport());
    }

    public function testGetMessageOptionReturnsDefaultGivenWhenUnset()
    {
        $config = [
            'messages' => [
                'message' => [
                    'subject' => 'Foo',
                ],
            ],
        ];
        $options = new ModuleOptions($config);
        $this->assertSame('Foo', $options->getMessageOption('message', 'subject', 'Bar'));
        $this->assertSame('MyDefault', $options->getMessageOption('message', 'unknownOption', 'MyDefault'));
        $this->assertSame('MyDefault', $options->getMessageOption('unknown', 'unknownOption', 'MyDefault'));
    }

    /**
     * @expectedException \NetglueMail\Exception\InvalidArgumentException
     */
    public function testExceptionThrownForNonStringMessageConfigName()
    {
        new ModuleOptions(['messages' => [0 => []]]);
    }

    /**
     * @expectedException \NetglueMail\Exception\InvalidArgumentException
     */
    public function testExceptionThrownForNonArrayMessageConfig()
    {
        new ModuleOptions(['messages' => ['msg' => 'Foo']]);
    }
}
