<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

use app\widgets\bootstrap\Nav;
use app\widgets\bootstrap\NavBar;

use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <?= Url::toRoute(['']) ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::t('app', 'WCA Statistics'),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => Yii::t('app', 'Home'), 'active' => Url::to('') === '/' , 'url' => [Yii::$app->homeUrl]],
            ['label' => Yii::t('app', 'Persons'), 'active' => in_array(Url::to(''), ['/persons', '/person']), 'url' => ['/persons']],
            ['label' => Yii::t('app', 'Kinch'), 'active' => substr(Url::to(''), 0, 6) === '/kinch', 'items' => [
                ['label' => Yii::t('app', 'Persons'), 'url' => ['/kinch/persons']],
                ['label' => Yii::t('app', 'Countries'), 'url' => ['/kinch/countries']],
                ['label' => Yii::t('app', 'Continents'), 'url' => ['/kinch/continents']],
            ]],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
      <?= Html::beginForm(Url::to(['lang']), 'post', ['id' => 'formLang', 'class' => 'form form-inline']) ?>
      <select name="lang" id="selectLang">
        <?php $providingLanguages = require(Yii::getAlias('@app/config/lang.php')); ?>
        <?= Html::renderSelectOptions(Yii::$app->request->cookies->getValue('lang', Yii::$app->language), $providingLanguages) ?>
      </select>
      <?= Html::endForm() ?>
      <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <span class="pull-left copyright">&copy; Cuber.Pro <?= date('Y') ?></span>

        <span class="pull-right last-update"><?= Yii::t('app', 'Last Update: ') ?>
            <a href="https://www.worldcubeassociation.org/results/misc/export.html" target="_blank">
                <span><?= @file_get_contents(Yii::$app->getBasePath() . '/commands/shell/wca_db/last') ?></span>
            </a>
        </span>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
