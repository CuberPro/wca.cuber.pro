<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => false,
    'rules' => [
        'persons' => 'person/index',
        'person/<i:\d{4}[a-zA-z]{4}\d{2}>' => 'person/profile',
    ],
];