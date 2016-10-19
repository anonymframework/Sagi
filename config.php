<?php

return [
    'username' => 'root',
    'password' => '',
    'dsn' => 'mysql:host=localhost;dbname=test',
    'driver' => 'mysql',

    'cache' => [
        'host' => '127.0.0.1',
        'port' => 11211
    ],

    'logging' => [
        'open' => true,
        'mailing' => [
            'aa@bb.com'
        ]
    ],

    'policies' => [
        //
    ],

    'PRIVATE_KEY' => md5('YOUR KEY'),

    'authentication' => [
        'login' => ['username', 'password']
    ]
];