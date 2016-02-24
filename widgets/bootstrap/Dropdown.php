<?php

namespace app\widgets\bootstrap;

use app\assets\bootstrap\BootstrapAsset;

class Dropdown extends \yii\bootstrap\Dropdown {

    /**
     * Renders the widget.
     */
    public function run()
    {
        BootstrapAsset::register($this->getView());
        $this->registerClientEvents();
        return $this->renderItems($this->items, $this->options);
    }
}
