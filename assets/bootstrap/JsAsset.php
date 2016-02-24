<?php

namespace app\assets\bootstrap;

use yii\web\AssetBundle;

/**
 * @author Yunqi Ouyang
 */
class JsAsset extends AssetBundle
{
    public $sourcePath = '@app/static/bootstrap/dist/js';
    public $css = [
    ];
    public $js = [
        'bootstrap.min.js',
    ];
    public $depends = [
        'app\assets\jquery\JqueryAsset',
    ];
    public $publishOptions = [
        'only' => [
            '*.min.js',
        ]
    ];
}
