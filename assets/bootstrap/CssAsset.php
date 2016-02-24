<?php

namespace app\assets\bootstrap;

use yii\web\AssetBundle;

/**
 * @author Yunqi Ouyang
 */
class CssAsset extends AssetBundle
{
    public $sourcePath = '@app/static/bootstrap/dist';
    public $css = [
        'css/bootstrap.min.css',
        'css/bootstrap-theme.min.css',
    ];
    public $js = [
    ];
    public $depends = [
    ];
    public $publishOptions = [
        'only' => [
            'css/*.min.css',
            'fonts/*',
        ]
    ];
}
