<?php
return [
    'paths' => [
        'migrations' => [
            '/home/tims01/abantecart_2.0/abantecart/abc/migrations/',
            '/home/tims01/abantecart_2.0/abantecart/abc/extensions/tims/migrations',
        ]
    ],
    'environments' => [
        'default_migration_table' => 'abc_migration_log',
        'default_database' => 'dev',
        'dev' => [
            'adapter' => 'mysql',
            'host'    => 'localhost',
            'name'    => 'tims01',
            'user'    => 'tims',
            'pass'    => 'entertims',
            'port'    => '',
            'table_prefix' => 'tims_',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]
    ]
];