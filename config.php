<?php

return [
    'username' => 'root',
    'password' => 'aa',
    'dsn' => 'mysql:host=localhost;dbname=aa',
    'driver' => 'mysql',

    'cache' => [
        'host' => '127.0.0.1',
        'port' => 11211
    ],

    'policies' => [
        //
    ],

    'PRIVATE_KEY' => md5('YOUR KEY'),

    'authentication' => [
        'login' => ['username', 'password']
    ]
];