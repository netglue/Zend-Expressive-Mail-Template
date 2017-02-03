<?php

namespace NetglueMailTest;

use NetglueMail\TemplateService;
use NetglueMail\Dispatcher;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Zend\Mail\Transport\InMemory;
use Zend\Mail\AddressList;
use Zend\Mail\Address;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\EventManager\EventInterface;

class DispatcherTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function getTemplateService()
    {
        return self::$container->get(TemplateService::class);
    }

    public function testDispatcherCanBeRetrievedFromContainer()
    {
        $dispatcher = self::$container->get(Dispatcher::class);

        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
        $this->assertInstanceOf(TemplateService::class, $dispatcher->getTemplateService());
        $this->assertInstanceOf(InMemory::class, $dispatcher->getTransport());

        return $dispatcher;
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testTransportCanBeOverridden(Dispatcher $dispatcher)
    {
        $old = $dispatcher->getTransport();
        $new = new InMemory;
        $dispatcher->setTransport($new);
        $this->assertSame($new, $dispatcher->getTransport());
        return $dispatcher;
    }

    public function testAddressList()
    {
        $list = array(
            'fred@example.com' => 'Fred',
            'jane@example.com',
        );

        $addressList = new AddressList;
        $addressList->addMany($list);
        $this->assertTrue($addressList->has('fred@example.com'));
        $this->assertTrue($addressList->has('jane@example.com'));

        $email = 'Bill <bill@example.com>';
        $addressList->addFromString($email);
        $this->assertTrue($addressList->has('bill@example.com'));

        $email = 'jim@example.com';
        $addressList->addFromString($email);
        $this->assertTrue($addressList->has('jim@example.com'));
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testCreateMessageReturnsExpectedMessage(Dispatcher $dispatcher)
    {
        $msg = $dispatcher->createMessage('contactUs');
        $this->assertInstanceOf('Zend\Mail\Message', $msg);

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

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testSendMessage(Dispatcher $dispatcher)
    {
        $transport = $dispatcher->getTransport();
        $msg = $dispatcher->createMessage('contactUs');
        $dispatcher->sendMessage($msg);
        $this->assertSame($msg, $transport->getLastMessage());
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testViewVariablesInMessages(Dispatcher $dispatcher)
    {
        $transport = $dispatcher->getTransport();
        $dispatcher->send('viewVariables', [], ['test' => 'This is a test']);
        $message = $transport->getLastMessage();
        $body = $message->getBody();
        $this->assertCount(1, $body->getParts());
        $text = current($body->getParts());
        $this->assertContains('This is a test', $text->getContent());
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testAttachments(Dispatcher $dispatcher)
    {
        $transport = $dispatcher->getTransport();
        $dispatcher->send('viewVariables', [
            'attachments' => array(
                'TextFile.txt' => __DIR__ . '/../view/attachment.txt',
            )], [
                'test' => 'This is a test',
            ]);
        $message = $transport->getLastMessage();
        $body = $message->getBody();
        $parts = $body->getParts();
        $this->assertCount(2, $parts);
        $file = end($parts);
        $this->assertContains('Attachment Content', base64_decode($file->getContent()));
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testSendEventsAreTriggered(Dispatcher $dispatcher)
    {

        $events = $dispatcher->getEventManager();
        $events->attach('sendMessage', [$this, 'ensurePreSendEvent']);
        $events->attach('sendMessage.post', [$this, 'ensurePostSendEvent']);
        $dispatcher->send('viewVariables');
    }

    public function ensurePreSendEvent(EventInterface $event)
    {
        $this->assertInstanceOf(Dispatcher::class, $event->getTarget());
        $this->assertSame('sendMessage', $event->getName());
        $params = $event->getParams();
        $this->assertInstanceOf('Zend\Mail\Message', $params['message']);
        $this->assertSame('viewVariables', $params['messageName']);
    }

    public function ensurePostSendEvent(EventInterface $event)
    {
        $this->assertSame('sendMessage.post', $event->getName());
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testViewModelIsAcceptable(Dispatcher $dispatcher)
    {
        $id = uniqid('expect-this-');
        $viewModel = [
            'test' => $id,
        ];
        $message = $dispatcher->send('viewVariables', [], $viewModel);
        $body = $message->getBody();
        $text = current($body->getParts());
        $this->assertContains($id, $text->getContent());
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testDefaultSenderIsSet(Dispatcher $dispatcher)
    {
        $msg = $dispatcher->createMessage('noSender');
        $from = $msg->getFrom();
        $this->assertCount(1, $from);
        $this->assertTrue($from->has('me@example.com'));
    }

    /**
     * @depends testDispatcherCanBeRetrievedFromContainer
     */
    public function testDefaultSenderDoesNotOverrideFrom(Dispatcher $dispatcher)
    {
        $msg = $dispatcher->createMessage('contactUs');
        $from = $msg->getFrom();
        $this->assertCount(1, $from);
        $this->assertFalse($from->has('me@example.com'));
        $this->assertTrue($from->has('jane@example.com'));
    }
}
