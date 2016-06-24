<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                'persons' => 'person/index',
                'person/<i:\d{4}[a-zA-z]{4}\d{2}>' => 'person/profile',
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => require(is_file(__DIR__ . '/cookie.local.php') ? __DIR__ . '/cookie.local.php' : __DIR__ . '/cookie.php'),
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-Raw',
                    'fileMap' => [
                        'data' => 'data.php',
                    ],
                ],
            ],
        ],
        'errorHandler' => [
            // 'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(is_file(__DIR__ . '/db.local.php') ? __DIR__ . '/db.local.php' : __DIR__ . '/db.php'),
    ],
    'language' => array_keys(require(__DIR__ . '/lang.php'))[0],
    'params' => $params,
    'defaultRoute' => 'kinch/countries',
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
