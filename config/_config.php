<?php
return array(
    'app' => array(
        'router' => array(
            'map' => array(
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
);
