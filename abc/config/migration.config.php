<?php
    return [
        'paths' => [
            'migrations' => [
                              '/home/tims02/abantecart_2.0/catalog/abc/migrations/',
                              '/home/tims02/abantecart_2.0/catalog/abc/extensions/licensing/migrations',
                              '/home/tims02/abantecart_2.0/catalog/abc/extensions/tims_catalog/migrations',
                            ]
        ],
        'environments' => [
            'default_migration_table' => 'abc_migration_log',
            'default_database' => 'prod',
            'prod' => [
                'adapter' => 'mysql',
                'host'    => 'prod-bf-tims.cvdoik6hbltp.eu-west-2.rds.amazonaws.com',
                'name'    => 'tims_catalog',
                'user'    => 'admin',
                'pass'    => 'JMhdQpnaAOIS0W6U5C1qdCdN51lCUNw4L7RvlZQ8!',
                'port'    => '',
                'table_prefix' => 'tims_',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
            ]
        ]
    ];