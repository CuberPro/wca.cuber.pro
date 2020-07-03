<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Continents;
use app\models\Persons;
use app\models\Utils;

$this->title = Yii::t('app', 'Kinch Rank - Continents');
?>
<?= Html::beginForm(Url::to(['']), 'get', ['class' => 'form form-inline', 'id' => 'kinchRegionForm']) ?>
<?php if ($type == 'country'): 
  $this->title = Yii::t('app', 'Kinch Rank - Countries');
?>
<div class="form-group">
  <label for="kinchRegionId"><?= Yii::t('app', 'Region') ?></label>
  <select name="regionId" id="kinchRegionId" class="form-control">
    <option value="world"><?= Yii::t('app', 'World') ?></option>
    <optgroup label="<?= Yii::t('app', 'Continents') ?>">
      <?php
      $selected = Yii::$app->request->get('regionId');
      $continents = Continents::getContinents();
      $continents = Utils::translateAndSort($continents, 'name', 'region');
      foreach ($continents as $continent): ?>
      <option value="<?= $continent['id'] ?>" <?= $continent['id'] == $selected ? 'selected' : '' ?>><?= $continent['name'] ?></option>
      <?php endforeach; ?>
    </optgroup>
  </select>
</div>
<?php endif; ?>
<div class="form-group">
  <label for="kinchGender"><?= Yii::t('app', 'Gender') ?></label>
  <select id="kinchGender" name="gender" class="form-control">
    <?= Html::renderSelectOptions(Yii::$app->request->get('gender'), [
      'a' => Persons::getGenderName('a'),
      'm' => Persons::getGenderName('m'),
      'f' => Persons::getGenderName('f'),
      'o' => Persons::getGenderName('o'),
      '' => Persons::getGenderName(''),
    ]) ?>
  </select>
</div>
<?php if ($type== 'country'): ?><?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?><?php endif; ?>
<?= Html::endForm() ?>
<div class="table-responsive">
  <table class="table table-condensed table-bordered vertical-center">
    <thead>
      <tr class="info">
        <th class="text-center"><?= Yii::t('app', 'Rank') ?></th>
        <th class="text-center"><?= Yii::t('data', ucfirst($type)) ?></th>
        <th class="text-center"><?= Yii::t('app', 'Overall') ?></th>
        <?php foreach ($eventList as $event): ?>
        <th class="text-center"><?= $event ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php $rank = ''; $count = 0; $lastScore = -1.00; ?>
      <?php foreach ($regionList as $id => $region): 
        ++$count;
        $rank = $region['scores']['overall'] == $lastScore ? '' : $count;
        $lastScore = $region['scores']['overall']; ?>
      <tr>

        <td class="text-right rank">
          <a href="#" class="region-anchor" name="<?= $id ?>"></a>
          <?= $rank ?>
        </td>
        <th class="text-left region-name">
          <?php if ($type == 'country'): ?>
          <i class="flag flag-<?= strtolower($region['code']) ?>"></i>
          <?php endif; ?>
          <span><?= Yii::t('region', $region['name']) ?></span>
        </th>
        <td class="text-center level-<?= $region['scores']['overall'] == 0.0 ? 'none' : intval($region['scores']['overall'] / 10) ?>">
          <strong><?= sprintf('%.2f', $region['scores']['overall']) ?></strong>
        </td>
        <?php foreach ($eventList as $event): ?>
        <td class="text-center vertical-center level-<?= $region['scores'][$event] == 0.0 ? 'none' : intval($region['scores'][$event] / 10) ?>"><small><?= sprintf('%.2f', $region['scores'][$event]) ?></small></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
