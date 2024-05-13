<?php

use yii\helpers\Html;
use yii\helpers\Url;

use app\models\Persons;

$this->title = Yii::t('app', 'Persons');

?>
<div class="col-md-offset-2 col-md-8">
  <?= Html::beginForm(Url::to(['']), 'get', ['class' => 'form form-inline']) ?>
    <div class="form-group">
      <label for="query"><?= Yii::t('app', 'Name parts or WCA ID'); ?></label>
      <input id="query" class="form-control required" type="text" name="query">
    </div>
    <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
  <?= Html::endForm() ?>
  <?php if (strlen($query) != 0): ?><h2><?= Yii::t('app', '{count,plural,=1{# person} other{# persons}} matching: "{query}"', ['count' => count($persons), 'query' => $query]) ?></h2><?php endif;?>
  <?php if (!empty($persons)): ?>
    <table class="table">
      <thead>
        <tr>
          <th><?= Yii::t('app', 'Name'); ?></th>
          <th><?= Yii::t('app', 'WCA ID') ?></th>
          <th><?= Yii::t('app', 'Country') ?></th>
          <th><?= Yii::t('app', 'Gender') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($persons as $person): ?>
          <tr>
            <td><a href="/person/<?= $person['id'] ?>"><?= $person['name'] ?></a></td>
            <td class="nowrap">
              <a target="_blank" href="https://www.worldcubeassociation.org/persons/<?= $person['id'] ?>">
                <img src="/img/wca_logo.png" class="wca-logo" title="WCA" alt="WCA"><?= $person['id'] ?>
              </a>
            </td>
            <td class="nowrap">
              <i class="flag flag-<?= strtolower($person['countryCode']) ?>"></i>
              <span><?= Yii::t('region', $person['country']) ?></span>
            </td>
            <td><?= Persons::getGenderName($person['gender']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
