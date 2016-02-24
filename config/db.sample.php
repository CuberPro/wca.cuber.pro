<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=wca_' . trim(@file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'wcaDb')),
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8',
];
