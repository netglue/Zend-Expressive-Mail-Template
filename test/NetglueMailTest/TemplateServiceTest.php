<?php
declare(strict_types=1);

namespace NetglueMailTest;

use NetglueMail\Factory\TemplateServiceFactory;
use NetglueMail\MailTemplateRendererInterface;
use NetglueMail\ModuleOptions;
use NetglueMail\TemplateService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateServiceTest extends TestCase
{
    /** @var TemplateService */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->container->get(TemplateService::class);
    }

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

    public function testGetTemplateByName()
    {
        $this->assertSame('tmpl::one', $this->service->getTemplateByName('contactUs'));
        $this->assertNull($this->service->getTemplateByName('unknown-message-type'), 'Template should be null for unknown messages');
        $this->assertNull($this->service->getTemplateByName('nullTemplate'), 'Template should be null when one has not been set');
    }

    public function testGetTextTemplateByName()
    {
        $this->assertSame('tmpl::text', $this->service->getTextTemplateByName('contactUs'));
        $this->assertNull($this->service->getTextTemplateByName('unknown-message-type'), 'Template should be null for unknown messages');
        $this->assertNull($this->service->getTextTemplateByName('nullTemplate'), 'Template should be null when one has not been set');
    }

    public function testRenderTemplate()
    {
        $html = $this->service->renderTemplate('contactUs');
        $this->assertInternalType('string', $html);
        $this->assertContains('&amp;', $html);
    }

    public function testRenderTextTemplate()
    {
        $text = $this->service->renderTextTemplate('contactUs');
        $this->assertInternalType('string', $text);
        $this->assertContains('I’m a Text Template', $text);
    }

    public function testRenderTemplateReturnsNullForNullTemplate()
    {
        $this->assertNull($this->service->renderTemplate('nullTemplate'));
    }

    public function testRenderTextTemplateReturnsNullForNullTemplate()
    {
        $this->assertNull($this->service->renderTextTemplate('nullTemplate'));
    }

    public function testRenderLayout()
    {
        $html = $this->service->renderTemplate('gotLayout');
        $this->assertInternalType('string', $html);
        $this->assertContains('[layoutStart]', $html);
        $this->assertContains('&amp;', $html);
    }

    public function testRenderTextLayout()
    {
        $text = $this->service->renderTextTemplate('gotLayout');
        $this->assertInternalType('string', $text);
        $this->assertContains('[Text Layout Start]', $text);
        $this->assertContains('I’m a Text Template', $text);
    }
}
