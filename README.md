# Sending template based emails in a Zend Expressive app

[![Build Status](https://travis-ci.org/netglue/Zend-Expressive-Mail-Template.svg?branch=master)](https://travis-ci.org/netglue/Zend-Expressive-Mail-Template)

This module/library/whatever is probably a bit naïve and is effectively a quick refactor of an older ZF2 module that does the same thing but more specifically with `Zend\View` - you can find that here: [netglue/ZF2-Mail-Template-Module](https://github.com/netglue/ZF2-Mail-Template-Module)…

The main use case for this library is where you’ll construct email message layouts and templates and store them on the local filesystem as you would for the frontend of your app/website. These templates will then be rendered using whatever templating engine you've already got setup in your zend expressive app.

Each type of message will have a name and associated defaults/configuration so that in your code, it'll be just a case of `$dispatcher->send(string 'myMessage', array $emailOptions, array $viewVariables);`

This lib is a bit thrown together to scratch a simple itch and should be considered a work in progress. Contributions are welcomed :)

## Install

    $ composer require netglue/zend-expressive-mail-template

## Configure

### Mail Transport

Configuration should be placed under the key `netglue_mail`. The first thing to do is make sure that you've got a factory setup to return a Mail Transport that `Zend\Mail` can use. So, let's say you've got a transport setup in your DI container with the name `Zend\Mail\Transport\TransportInterface`, you'll need to provide this name in config thus:

    // …
    'netglue_mail' => [
        'transport' => Zend\Mail\Transport\TransportInterface::class,
    ],
    // …

If no transport is provided, the lib will automatically construct an `InMemory` transport and all your mail will be delivered there, which is probably not what you want…

### Template Rendering

Next, you'll need to setup configuration for each type of message you want to send. At the very least, you'll need to provide a template for the "Message Type". This should either an HTML based template in the key `template` or a plain text template in the key `textTemplate`, or both for a multipart message. Clearly, you'll also need to setup your template resolver to know where to find these template names:
    
    'netglue_mail' => [
        // …
        'messages' => [
            'myMessage' => [
                'template'     => 'email::some-html',
                'textTemplate' => 'email::some-text',
            ],
        ],
        // …
    ],
    // Your template path config might look like:
    'templates' => [
        'map' => [
            'email::some-html' => __DIR__ . '/tmpl/mail/some-file.html',
            'email::some-text' => __DIR__ . '/tmpl/mail/some-text.txt',
        ],
    ],

The factory that puts together the Template Rendering 'Service' will look in the DI container for a rendering engine under the key `NetglueMail\MailTemplateRendererInterface::class` - this service name is aliased to `Zend\Expressive\Template\TemplateRendererInterface::class`, so, theoretically, as long as you are pulling in the Dependency Config from the `ConfigProvider` the template renderer will work out of the box _and_ if you want to provide a different renderer for mail than whatever your app is using, all you have to do is override this alias in your dependency config with something along the lines of this:
    
    'dependencies' => [
        'aliases' => [
            NetglueMail\MailTemplateRendererInterface::class => Some\Other\Renderer::class,
        ],
        'factories' => [
            Some\Other\Renderer::class => Some\Other\RendererFactory::class,
        ],  
    ],

There's a whole load of other stuff you can configure each individual message with along with global defaults. 

### Default Sender

Setup the default sender for all messages to go from a consistent address/name:
    
    'netglue_mail' => [
        'defaultSender' => 'me@example.com',
        'defaultSenderName' => 'Some Dude',
    ],

### Default Message Headers

Apply default headers to all outbound mail:
    
    'netglue_mail' => [
        'defaultHeaders' => [
            'X-Mailer' => 'Granny',
            'X-Foo' => 'Bar',
            'X-Blah' => 'Bing',
        ],
    ],

### Various defaults for individual messages

Set the recipient, sender, headers, subject etc…
    
    'netglue_mail' => [
        // …
        'messages' => [
            'myMessage' => [
                'template'     => 'email::some-html',
                'to'           => ['some@guy.com' => 'Some Guy', 'Someone Else' => 'other@elsewhere.com'],
                'subject'      => 'Re: Nappies',
                'headers' => [
                    'X-Foo' => 'Baz',
                ],
            ],
        ],
        // …
    ],
    

## Usage

Once everything is configured as you'd like, you can still override settings for Zend\Mail and provide view variables when sending something like this:
    
    $dispatcher = $container->get(\NetglueMail\Dispatcher::class);
    
    $options = [
        'to' => 'me@nowhere.com',
        'subject' => 'Scones',
        'headers' => [
            'X-Foo' => 'Bing Bong',
        ],
    ];
    $view = [
        'var1' => 'Foo',
        'var2' => 'Foo',
    ];
    
    $mailMessage = $dispatcher->send('myMessage', $options, $view);

You can also construct the message first from defaults and runtime options and then send it in 2 steps:

    $mailMessage = $disptacher->createMessage('myMessage', $options, $view);
    // Do stuff to $mailMessage
    // or add additional values to be sent with triggered events in $eventParams
    $dispatcher->sendMessage($mailMessage, $eventParams);
    
## Events

2 Events are triggered with `Zend\EventManager` on send and these are `sendMessage` and `sendMessage.post`, so you may like to somewhere:

    $dispatcher->getEventManager()->attach('sendMessage', function($e) {
        $message = $e->getParams()['message'];
        $dispatcher = $e->getTarget();
        // .. Do stuff before $message is passed to the transport
    });
    
## Tests

Test coverage is pretty good using Zend\View as the template engine, but I haven't had the time to test with Twig or Plates etc. YMMV.

## About

[Netglue makes web based stuff in Devon, England](https://netglue.uk). We hope this is useful to you and we’d appreciate feedback either way :)
