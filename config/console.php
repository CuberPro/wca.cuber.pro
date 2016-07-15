<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');

$COMPONENT_DIRS = [
    __DIR__ . '/common',
    __DIR__ . '/console',
];

$components = require(__DIR__ . '/components.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => $components,
    'params' => $params,
];
