<?php
declare(strict_types=1);

namespace NetglueMailTest;

use NetglueMail\Factory\DispatcherFactory;
use NetglueMail\ModuleOptions;
use NetglueMail\TemplateService;
use NetglueMail\Dispatcher;
use Psr\Container\ContainerInterface;
use Zend\EventManager\Event;
use Zend\Mime\Message;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Zend\Mail;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\EventManager\EventInterface;

class DispatcherTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /** @var Dispatcher */
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = $this->container->get(Dispatcher::class);
    }

    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $templates = $this->prophesize(TemplateService::class);
        $container->get(ModuleOptions::class)->willReturn(new ModuleOptions([]));
        $container->get(TemplateService::class)->willReturn($templates->reveal());

        $factory = new DispatcherFactory();
        $dispatcher = ($factory)($container->reveal());
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        return $dispatcher;
    }

    public function testCreateMessageReturnsExpectedMessage()
    {
        $msg = $this->dispatcher->createMessage('contactUs');
        $this->assertInstanceOf(Mail\Message::class, $msg);

        $to = $msg->getTo();
        $this->assertTrue($to->has('bill@example.com'));
        $this->assertTrue($to->has('foo@example.com'));
        $address = $to->get('foo@example.com');
        $this->assertSame('Foo', $address->getName());

        $cc = $msg->getCc();
        $this->assertTrue($cc->has('cc@example.com'));
        $this->assertTrue($cc->has('cc2@example.com'));
        $address = $cc->get('cc2@example.com');
        $this->assertSame('Some Guy', $address->getName());

        $reply = $msg->getReplyTo();
        $this->assertTrue($reply->has('you@example.com'));
        $this->assertTrue($reply->has('reply@example.com'));
        $address = $reply->get('reply@example.com');
        $this->assertSame('Reply Here', $address->getName());

        $this->assertSame('An Interesting Subject Line', $msg->getSubject());

        $headers = $msg->getHeaders();
        $header = $headers->get('X-Foo');
        $this->assertSame('Baz', $header->getFieldValue());

        $header = $headers->get('X-Mailer');
        $this->assertSame('Netglue Mail Template Module', $header->getFieldValue());

        $address = $msg->getSender();
        $this->assertSame('sender@example.com', $address->getEmail());
        $this->assertSame('I Sent This', $address->getName());
    }

    public function testSendMessage()
    {
        /** @var Mail\Transport\InMemory $transport */
        $transport = $this->dispatcher->getTransport();
        $this->assertInstanceOf(Mail\Transport\InMemory::class, $transport);
        $msg = $this->dispatcher->createMessage('contactUs');
        $this->dispatcher->sendMessage($msg);
        $this->assertSame($msg, $transport->getLastMessage());
    }

    public function testViewVariablesInMessages()
    {
        /** @var Mail\Transport\InMemory $transport */
        $transport = $this->dispatcher->getTransport();
        $this->dispatcher->send('viewVariables', [], ['test' => 'This is a test']);
        $message = $transport->getLastMessage();
        $body = $message->getBody();
        $this->assertInstanceOf(Message::class, $body);
        $this->assertCount(1, $body->getParts());
        $text = current($body->getParts());
        $this->assertContains('This is a test', $text->getContent());
    }

    public function testAttachments()
    {
        /** @var Mail\Transport\InMemory $transport */
        $transport = $this->dispatcher->getTransport();
        $this->dispatcher->send(
            'viewVariables',
            [
            'attachments' => [
                'TextFile.txt' => __DIR__ . '/../view/attachment.txt',
            ]],
            [
                'test' => 'This is a test',
            ]
        );
        $message = $transport->getLastMessage();
        $body = $message->getBody();
        $parts = $body->getParts();
        $this->assertCount(2, $parts);
        $file = end($parts);
        $this->assertContains('Attachment Content', base64_decode($file->getContent()));
    }

    public function testSendEventsAreTriggered()
    {
        $sendFired = $postFired = false;
        $events = $this->dispatcher->getEventManager();
        $events->attach('sendMessage', function (Event $event) use (&$sendFired) {
            $sendFired = true;
            $this->assertSame('sendMessage', $event->getName());
            $this->assertSame($this->dispatcher, $event->getTarget());
            $this->assertInstanceOf(Mail\Message::class, $event->getParam('message'));
        });
        $events->attach('sendMessage.post', function (Event $event) use (&$postFired) {
            $postFired = true;
            $this->assertSame('sendMessage.post', $event->getName());
            $this->assertSame($this->dispatcher, $event->getTarget());
            $this->assertInstanceOf(Mail\Message::class, $event->getParam('message'));
        });
        $this->dispatcher->send('viewVariables');
        $this->assertTrue($sendFired);
        $this->assertTrue($postFired);
    }

    public function testViewModelIsAcceptable()
    {
        $id = uniqid('expect-this-');
        $viewModel = [
            'test' => $id,
        ];
        $message = $this->dispatcher->send('viewVariables', [], $viewModel);
        $body = $message->getBody();
        $text = current($body->getParts());
        $this->assertContains($id, $text->getContent());
    }

    public function testDefaultSenderIsSet()
    {
        $msg = $this->dispatcher->createMessage('noSender');
        $from = $msg->getFrom();
        $this->assertCount(1, $from);
        $this->assertTrue($from->has('me@example.com'));
    }

    public function testDefaultSenderDoesNotOverrideFrom()
    {
        $msg = $this->dispatcher->createMessage('contactUs');
        $from = $msg->getFrom();
        $this->assertCount(1, $from);
        $this->assertFalse($from->has('me@example.com'));
        $this->assertTrue($from->has('jane@example.com'));
    }
}
