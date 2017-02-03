<?php

namespace NetglueMailTest;

use NetglueMail\ModuleOptions;

class ModuleOptionsTest extends TestCase
{


    public function testModuleOptionsCanBeRetrievedContainer()
    {
        $options = self::$container->get(ModuleOptions::class);

        $this->assertInstanceOf(ModuleOptions::class, $options);

        return $options;
    }

    /**
     * @expectedException NetglueMail\Exception\InvalidArgumentException
     * @depends testModuleOptionsCanBeRetrievedContainer
     */
    public function testGetMessageOptionThrowsExceptionForNonString(ModuleOptions $options)
    {
        $options->getMessageOption('foo', 123);
    }

    /**
     * @depends testModuleOptionsCanBeRetrievedContainer
     */
    public function testGetMessageOptionReturnsDefaultGivenWhenUnset(ModuleOptions $options)
    {
        $this->assertSame('Foo', $options->getMessageOption('nullTemplate', 'subject', 'Bar'));
        $this->assertSame('MyDefault', $options->getMessageOption('nullTemplate', 'unknownOption', 'MyDefault'));
    }

}
