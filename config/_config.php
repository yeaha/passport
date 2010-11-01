<?php
return array(
    'app' => array(
        'router' => array(
            'rewrite' => array(
                '#^/passport/auth/?#' => '\Controller\Passport\Auth',
                '#^/passport/(.+)#' => '\Controller\Passport',
            ),
        ),
    ),
    'storage' => array(
        'pool' => array(
            '__default__' => array(
                'class' => 'Lysine\Storage\DB\Adapter\Pgsql',
                'dsn' => 'pgsql:host=127.0.0.1 dbname=passport',
                'user' => 'dev',
                'pass' => 'abc',
            ),
        ),
    ),
    'allow_ip' => '*',  // array('192.168.1.100', '192.168.1.200'),
);
