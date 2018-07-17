<?php

namespace NetglueMailTest;

use NetglueMail\Factory\TemplateServiceFactory;
use NetglueMail\MailTemplateRendererInterface;
use NetglueMail\TemplateService;
use NetglueMail\ModuleOptions;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\ZendView\ZendViewRenderer;

class TemplateServiceTest extends TestCase
{

    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ModuleOptions::class)->willReturn(new ModuleOptions());
        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $container->get(MailTemplateRendererInterface::class)->willReturn($renderer->reveal());

        $factory = new TemplateServiceFactory();
        $service = ($factory)($container->reveal());

        $this->assertInstanceOf(TemplateService::class, $service);
    }

    /**
     *
     * @depends testTemplateServiceCanBeRetrievedFromContainer
     */
    public function testGetTemplateByName(TemplateService $service)
    {
        $this->assertSame('tmpl::one', $service->getTemplateByName('contactUs'));
        $this->assertNull($service->getTemplateByName('unknown-message-type'), 'Template should be null for unknown messages');
        $this->assertNull($service->getTemplateByName('nullTemplate'), 'Template should be null when one has not been set');

        return $service;
    }

    /**
     *
     * @depends testTemplateServiceCanBeRetrievedFromContainer
     */
    public function testGetTextTemplateByName(TemplateService $service)
    {
        $this->assertSame('tmpl::text', $service->getTextTemplateByName('contactUs'));
        $this->assertNull($service->getTextTemplateByName('unknown-message-type'), 'Template should be null for unknown messages');
        $this->assertNull($service->getTextTemplateByName('nullTemplate'), 'Template should be null when one has not been set');

        return $service;
    }

    /**
     *
     * @depends testGetTemplateByName
     */
    public function testRenderTemplate(TemplateService $service)
    {
        $html = $service->renderTemplate('contactUs');
        $this->assertInternalType('string', $html);
        $this->assertContains('&amp;', $html);
    }

    /**
     *
     * @depends testGetTextTemplateByName
     */
    public function testRenderTextTemplate(TemplateService $service)
    {
        $text = $service->renderTextTemplate('contactUs');
        $this->assertInternalType('string', $text);
        $this->assertContains('I’m a Text Template', $text);
    }

    /**
     *
     * @depends testGetTemplateByName
     */
    public function testRenderTemplateReturnsNullForNullTemplate(TemplateService $service)
    {
        $this->assertNull($service->renderTemplate('nullTemplate'));
    }

    /**
     *
     * @depends testGetTemplateByName
     */
    public function testRenderTextTemplateReturnsNullForNullTemplate(TemplateService $service)
    {
        $this->assertNull($service->renderTextTemplate('nullTemplate'));
    }

    /**
     *
     * @depends testGetTemplateByName
     */
    public function testRenderLayout(TemplateService $service)
    {
        $html = $service->renderTemplate('gotLayout');
        $this->assertInternalType('string', $html);
        $this->assertContains('[layoutStart]', $html);
        $this->assertContains('&amp;', $html);
    }

    /**
     *
     * @depends testGetTextTemplateByName
     */
    public function testRenderTextLayout(TemplateService $service)
    {
        $text = $service->renderTextTemplate('gotLayout');
        $this->assertInternalType('string', $text);
        $this->assertContains('[Text Layout Start]', $text);
        $this->assertContains('I’m a Text Template', $text);
    }
}
