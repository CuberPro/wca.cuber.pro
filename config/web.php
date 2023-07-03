<?php

$params = require(__DIR__ . '/params.php');

$COMPONENT_DIRS = [
    __DIR__ . '/common',
    __DIR__ . '/web',
];

$components = require(__DIR__ . '/components.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => $components,
    'language' => array_keys(require(__DIR__ . '/lang.php'))[0],
    'params' => $params,
    'defaultRoute' => 'kinch/countries',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', gethostbyname($_SERVER['HTTP_HOST'])]
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', gethostbyname($_SERVER['HTTP_HOST'])]
    ];
}

return $config;
