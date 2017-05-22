<?php

return [

    'connections' => [

        'default' => [

            'username' => 'root',
            'password' => '',
            'dsn' => 'mysql:host=127.0.0.1;dbname=test',
            'driver' => 'mysql',

            'attr' => [
                PDO::ATTR_PERSISTENT => true,
            ],

        ],

    ],


    'cache' => [

        'driver' => 'memcache', // also supports redis

        'memcache' => [

            'default' => 'default_driver',

            'default_driver' => [
                'host' => '127.0.0.1',
                'port' => 11211
            ]
        ],

        'redis' => [

            'default' => 'default_driver',

            'default_driver' => [
                'scheme' => 'tcp',
                'host'   => '127.0.0.1',
                'port'   => 6379,
            ]

        ]
    ],

    'logging' => [
        'open' => true,
    ],

    'policies' => [
        /**
         *
         *  'Model::class' => Policy::class,
         *
         *  'messages' => [
         *      'table_name_update' => 'You don't have access this method',
         *      'table_name_save' => 'You can't save this'.
         *  ]
         *
         */
    ],

    'migrations' => [
        // put here your primary migrations
    ],

    'PRIVATE_KEY' => md5('YOUR KEY'),
];
