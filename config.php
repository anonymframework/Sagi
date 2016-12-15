<?php

return [
    'username' => 'root',
    'password' => '',
    'dsn' => 'mysql:host=localhost;dbname=radar',
    'driver' => 'mysql',

    'fetch_mode' => PDO::FETCH_OBJ,

    'cache' => [
        'host' => '127.0.0.1',
        'port' => 11211
    ],

    'logging' => [
        'open' => true,
    ],

    'policies' => [
        //
    ],

    'PRIVATE_KEY' => md5('YOUR KEY'),

    'authentication' => [
        'login' => ['username', 'password'],
        'error_messages' => [
            'username' => 'Kullanıcı Adınızı Yanlış Girdiniz',
            'password' => 'Şifrenizi Yanlış Giridiniz'
        ]
    ],

];