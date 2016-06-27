<?php

return [
    'linkAssets' => true,
    'converter' => [
        'class' => 'yii\web\AssetConverter',
        'commands' => [
            'less' => ['css', 'lessc {from} {to} --no-color --autoprefix --clean-css'],
        ],
    ],
];