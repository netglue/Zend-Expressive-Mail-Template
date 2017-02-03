<?php
return [

    'dependencies' => [
        'factories' => [
            Zend\Expressive\Template\TemplateRendererInterface::class => Zend\Expressive\ZendView\ZendViewRendererFactory::class,
            Zend\View\HelperPluginManager::class => Zend\Expressive\ZendView\HelperPluginManagerFactory::class,
        ],
    ],

    'netglue_mail' => [

        'defaultSender' => 'me@example.com',
        'defaultSenderName' => 'Some Dude',

        /**
         * These headers will be set for every message sent
         */
        'defaultHeaders' => [
            'X-Mailer' => 'Netglue Mail Template Module',
            'X-Foo' => 'Bar',
            'X-Blah' => 'Bing',
        ],

        'messages' => [
            'contactUs' => [
                'template'     => 'tmpl::one',
                'textTemplate' => 'tmpl::text',
                'subject'      => 'An Interesting Subject Line',
                'to'           => ['bill@example.com', 'foo@example.com' => 'Foo'],
                'cc'           => ['cc@example.com', 'cc2@example.com' => 'Some Guy'],
                'bcc'          => [],
                'replyTo'      => ['you@example.com', 'reply@example.com' => 'Reply Here'],
                'from'         => 'Jane <jane@example.com>',
                'sender'       => 'sender@example.com',
                'senderName'   => 'I Sent This',
                'headers' => [
                    'X-Foo' => 'Baz',
                ],
            ],
            'nullTemplate' => [
                'subject' => 'Foo',
            ],
            'gotLayout' => [
                'template'     => 'tmpl::one',
                'layout'       => 'layout::one',
                'textTemplate' => 'tmpl::text',
                'textLayout'   => 'layout::text',
            ],

            'viewVariables' => [
                'textTemplate' => 'variables',
                'to' => 'me@example.com',
                'from' => 'you@example.com',
                'subject' => 'A Test',
            ],

            'noSender' => [
                'template'     => 'tmpl::one',
                'subject'      => 'foo!',
            ],
        ],
    ],

    'templates' => [
        'map' => [
            'tmpl::one'    => __DIR__ . '/../view/tmpl-one.phtml',
            'tmpl::text'   => __DIR__ . '/../view/tmpl-two.txt',
            'layout::one'  => __DIR__ . '/../view/layout-one.phtml',
            'layout::text' => __DIR__ . '/../view/text-layout.txt',
            'variables'    => __DIR__ . '/../view/variables.txt',
        ],
    ],
];
