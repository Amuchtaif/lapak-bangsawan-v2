<?php

require __DIR__ . '/config/database.php';

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $host,
            'name' => $db_name,
            'user' => $user,
            'pass' => $pass,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'production' => [
            'adapter' => 'mysql',
            'host' => $host,
            'name' => $db_name,
            'user' => $user,
            'pass' => $pass,
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
