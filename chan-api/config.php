<?php

return [
    'db' => [
        'database' => $_ENV['MYSQL_DATABASE'],
        'hostname' => $_ENV['MYSQL_HOSTNAME'],
        'username' => $_ENV['MYSQL_USERNAME'],
        'password' => $_ENV['MYSQL_PASSWORD']
    ],
    'maintenance_key'      => $_ENV['ADMINISTRATOR_KEY'],
    'default_name'         => $_ENV['DEFAULT_NAME'],
    'exclude_tags'         => $_ENV['EXCLUDE_TAGS'],
];
