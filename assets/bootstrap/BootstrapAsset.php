<?php

namespace app\assets\bootstrap;

use yii\web\AssetBundle;

/**
 * @author Yunqi Ouyang
 */
class BootstrapAsset extends AssetBundle
{
    public $depends = [
        'app\assets\bootstrap\JsAsset',
        'app\assets\bootstrap\CssAsset',
    ];
}
