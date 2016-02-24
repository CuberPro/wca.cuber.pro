<?php

namespace app\assets\jquery;

use yii\web\AssetBundle;

/**
 * @author Yunqi Ouyang
 */
class JqueryAsset extends AssetBundle
{
    public $sourcePath = '@app/static/jquery';
    public $css = [
    ];
    public $js = [
        'jquery.min.js',
    ];
    public $depends = [
    ];
}
