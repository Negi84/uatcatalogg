<?php
//set default umask for file cache/ 664 for files 775 for directories
//Only for file-based cache! (to give access for tims02 user to cache files)
umask(002);
ini_set('date.timezone', 'Europe/London');

return [
    'APP_NAME'            => 'TIMS_CATALOG',
    'MIN_PHP_VERSION'     => '8.1.0',
    'DIR_ROOT'            => '/home/tims02/abantecart_2.0/catalog/',
    'DIR_APP'             => '/home/tims02/abantecart_2.0/catalog/abc/',
    'DIR_PUBLIC'          => '/home/tims02/abantecart_2.0/catalog/public/',
    'SERVER_NAME'         => 'uattimscatalogue.anysinfo.com',
    'ADMIN_SECRET'        => 'tims_admin',
    'UNIQUE_ID'           => '4a1455b6c2ffcb75a72af570d9ef7d51',
    // SEO URL Keyword separator
    'SEO_URL_SEPARATOR'   => '-',
    // EMAIL REGEXP PATTERN
    'EMAIL_REGEX_PATTERN' => '/^[A-Z0-9._%-]+@[A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,16}$/i',
    //postfixes for template override
    'POSTFIX_OVERRIDE'    => '.override',
    'POSTFIX_PRE'         => '.pre',
    'POSTFIX_POST'        => '.post',
    'APP_CHARSET'         => 'UTF-8',

    'DB_CURRENT_DRIVER' => 'mysql',
    'DATABASES'         => [
        'mysql' => [
            'DB_DRIVER'    => 'mysql',
            'DB_PORT'      => '',
            #'DB_HOST'      => 'localhost',
            #'DB_USER'      => 'tims',
            #'DB_PASSWORD'  => 'entertims',
            #'DB_NAME'      => 'tims',
            'DB_HOST'      => 'prod-bf-tims.cvdoik6hbltp.eu-west-2.rds.amazonaws.com',
            'DB_USER'      => 'admin',
            'DB_PASSWORD'  => 'JMhdQpnaAOIS0W6U5C1qdCdN51lCUNw4L7RvlZQ8!',
            'DB_NAME'      => 'tims_catalog',
            'DB_PREFIX'    => 'tims_',
            'DB_CHARSET'   => 'utf8',
            'DB_COLLATION' => 'utf8_unicode_ci',
            'strict'       => false,
            'options'      => array(PDO::MYSQL_ATTR_LOCAL_INFILE => true),
        ],
        'mysql2' => [   //local older DB
            'DB_DRIVER'    => 'mysql',
            'DB_HOST'      => 'localhost',
            'DB_PORT'      => '',
            'DB_USER'      => 'tims',
            'DB_PASSWORD'  => 'entertims',
            'DB_NAME'      => 'tims_catalog',
            'DB_PREFIX'    => 'tims_',
            'DB_CHARSET'   => 'utf8',
            'DB_COLLATION' => 'utf8_unicode_ci',
        ],
    ],

    'CACHE' =>
        [
            'driver' => 'file',
            'stores' => [
                'file' => [
                    //folder where we store cache files
                    'path' => '/home/tims02/abantecart_2.0/catalog/abc/system/cache',
                    //time-to-live in seconds
                    //also can be Datetime Object
                    'ttl'  => 86400
                ]
            ]
        ],
    //enable debug info collection
    // 1 - output to debug-bar and logging, 2 - only logging (see log-directory)
    'DEBUG'          => 0,
    /*
     * Level 0 - no logs , only exception errors
     * Level 1 - errors and warnings
     * Level 2 - #1 + mysql site load, php file execution time and page elements load time
     * Level 3 - #2 + basic logs and stack of execution
     * Level 4 - #3 + dump mysql statements
     * Level 5 - #4 + intermediate variable
     *
     * */
    'DEBUG_LEVEL'    => 5,
    'ENCRYPTION_KEY' => '12345',
];
