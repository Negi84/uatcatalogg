<?php
return [
        'APP_NAME' => 'AbanteCart',
        'MIN_PHP_VERSION' => '8.1.0',
        'DIR_ROOT' => '/var/www/html/uatcatalog/',
        'DIR_APP' => '/var/www/html/uatcatalog/abc/',
        'DIR_PUBLIC' => '/var/www/html/uatcatalog/public/',
        'SERVER_NAME' => '',
        'ADMIN_SECRET' => '201005',
        'UNIQUE_ID' => 'a454ff61f8a90bc05f873cf3b670cbc0',
        // SEO URL Keyword separator
        'SEO_URL_SEPARATOR' => '-',
        // EMAIL REGEXP PATTERN
        'EMAIL_REGEX_PATTERN' => '/^[A-Z0-9._\'%-]+@[A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,16}$/i',
        //postfixes for template override
        'POSTFIX_OVERRIDE' => '.override',
        'POSTFIX_PRE' => '.pre',
        'POSTFIX_POST' => '.post',
        'APP_CHARSET' => 'UTF-8',

        'DB_CURRENT_DRIVER' => 'mysql',
        'DATABASES' =>[
            'mysql' => [
                        'DB_DRIVER'    => 'mysql',
                        'DB_HOST'      => 'localhost',
                        'DB_PORT'      => '3306',
                        'DB_USER'      => 'root',
                        'DB_PASSWORD'  => '11111',
                        'DB_NAME'      => 'catalog',
                        'DB_PREFIX'    => 'tims_',
                        'DB_CHARSET'   => 'utf8',
                        'DB_COLLATION' => 'utf8_unicode_ci'
            ]
        ],

        'CACHE' => 
                    [
                        'driver' => 'file',
                        'stores' => [
                            'file' => [
                                //folder where we store cache files
                                'path'   => '/var/www/html/uatcatalog/abc/system/cache/',
                                //time-to-live in seconds
                                //also can be Datetime Object
                                'ttl'    => 86400
                            ],
                            /*'memcached' => [ 
                               'servers' => [
                                    [
                                        'host' => '127.0.0.1',
                                        'port' => 11211,
                                        'weight' => 100
                                    ]
                               ]
                            ],
                            //NOTE: if you plan to use phpredis client you should to install ext-redis (php-extension)
                            // on debian just type: sudo apt install php8.1-redis 
                            'redis' => [
                                'client' => 'phpredis',
                                'default' => [
                                    'host' => 'localhost',
                                    'password' => 'secret_redis',
                                    'port' => 6379,
                                    'database' => 'REDIS_DB',
                                ],
                            ]
                            */
                        ]
                    ],
        //enable debug info collection
        // 1 - output to debug-bar and logging, 2 - only logging (see log-directory)
        'DEBUG' => 1,
        /*
         * Level 0 - no logs , only exception errors
         * Level 1 - errors and warnings
         * Level 2 - #1 + mysql site load, php file execution time and page elements load time
         * Level 3 - #2 + basic logs and stack of execution
         * Level 4 - #3 + dump mysql statements
         * Level 5 - #4 + intermediate variable
         *
         * */
        'DEBUG_LEVEL' => 5,
        'ENCRYPTION_KEY' => '12345',
        // bootstrap 3 admin template
        // 'adminTemplate' => 'default_bs3',

        // cache settings for abac 3d-party factory
        
        'ABAC' =>
            [   
                'CONFIG_DIRECTORY' => [
                    '/var/www/html/uatcatalog/abc/config/abac'
                ],
                'CACHE_ENABLE' => true,
            //  'CACHE_FOLDER' => '/var/www/html/uatcatalog/abc/system/cache/abac',
            //  'CACHE_TTL'    => '3600',
            //  'CACHE_DRIVER' => 'text'
            ],
            
        'RABBIT_MQ' => [
            'HOST' => '',
            'PORT' => 5672,
            'USER' => '',
            'PASSWORD' => ''
           ]

];