<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Yunqi Ouyang
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@app/static/app';
    public $css = [
        'less/site.less',
    ];
    public $js = [
        'js/main.js',
    ];
    public $depends = [
        'app\assets\bootstrap\BootstrapAsset',
        'app\assets\jquery\JqueryAsset',
    ];
    public $publishOptions = [
        'only' => [
            '*.js',
            '*.less',
        ],
    ];
}
