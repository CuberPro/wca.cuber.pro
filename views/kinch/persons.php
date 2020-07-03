<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Continents;
use app\models\Countries;
use app\models\KinchScores;
use app\models\Persons;
use app\models\Utils;

$this->title = Yii::t('app', 'Kinch Rank - Persons');
?>
<?= Html::beginForm(Url::to(['']), 'get', ['class' => 'form form-inline', 'id' => 'kinchPersonForm']) ?>
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
    <optgroup label="<?= Yii::t('app', 'Countries') ?>">
      <?php
      $countries = Countries::getCountries();
      $countries = Utils::translateAndSort($countries, 'name', 'region');
      foreach ($countries as $country): ?>
      <option value="<?= $country['id'] ?>" <?= $country['id'] == $selected ? 'selected' : '' ?>><?= $country['name'] ?></option>
      <?php endforeach; ?>
    </optgroup>
  </select>
</div>
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
<div class="form-group">
  <label for="kinchShow"><?= Yii::t('app', 'Show') ?></label>
  <select id="kinchShow" name="show" class="form-control">
    <?php
    $selected = Yii::$app->request->get('show');
    $rankTypes = KinchScores::getRankTypes();
    foreach ($rankTypes as $id => $type): ?>
    <option value="<?= $id ?>" <?= $id == $selected ? 'selected' : '' ?>><?= $type['label'] ?></option>
    <?php endforeach; ?>
  </select>
</div>
<?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
<?= Html::endForm() ?>
<?php if ($totalPage > 1): ?>
  <nav class="text-center">
    <ul class="pagination pagination-sm">
      <li class="<?= $page == 1 ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => 1]) ?>">&lt;&lt; <?= Yii::t('app', 'First') ?></a></li>
      <li class="<?= $page == 1 ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => max(1, $page - 1)]) ?>">&lt; <?= Yii::t('app', 'Previous') ?></a></li>
      <?php
        $firstPage = min(max(1, $page - 5), max(1, $totalPage - 9));
        for ($i = $firstPage; $i < $firstPage + 10 && $i <= $totalPage; $i++): ?>
          <li class="<?= $page == $i ? 'active' : '' ?>"><a href="<?= Url::current(['page' => $i]) ?>"><?= $i ?></a></li>
      <?php endfor; ?>
      <li class="<?= $page == $totalPage ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => min($totalPage, $page + 1)]) ?>"><?= Yii::t('app', 'Next') ?> &gt;</a></li>
      <li class="<?= $page == $totalPage ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => $totalPage]) ?>"><?= Yii::t('app', 'Last') ?> &gt;&gt;</a></li>
    </ul>
  </nav>
<?php endif; ?>
<div class="table-responsive">
  <table class="table table-condensed table-bordered vertical-center">
    <thead>
      <tr class="info">
        <th class="text-center"><?= Yii::t('app', 'Rank') ?></th>
        <th class="text-center"><?= Yii::t('app', 'Person') ?></th>
        <th class="text-center"><?= Yii::t('app', 'Overall') ?></th>
        <?php foreach ($eventList as $event): ?>
        <th class="text-center"><?= $event ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php $rank = $rankOffset + 1; $count = $countOffset; $lastScore = -1.00; $firstOfPage = true; ?>
      <?php foreach ($personList as $id => $person): 
        ++$count;
        $rank = $firstOfPage ? $rank : ($person['overall'] == $lastScore ? '' : $count);
        $firstOfPage = false;
        $lastScore = $person['overall']; ?>
      <tr>

        <td class="text-right rank"><?= $rank ?></td>
        <th class="text-left person-name">
          <i class="flag flag-<?= strtolower($person['countryCode']) ?>" title="<?= Yii::t('region', $person['countryName']) ?>" data-toggle="tooltip" data-placement="top"></i>
          <span><a href="/person/<?= $person['personId'] ?>"><?= $person['name'] ?></a></span>
        </th>
        <td class="text-center level-<?= $person['overall'] == 0.0 ? 'none' : intval($person['overall'] / 10) ?>">
          <strong><?= sprintf('%.2f', $person['overall']) ?></strong>
        </td>
        <?php foreach ($eventList as $event):
          $score = isset($person[$event]) ? $person[$event] : 0.00;
        ?>
        <td class="text-center vertical-center level-<?= $score == 0.0 ? 'none' : intval($score / 10) ?>"><small><?= sprintf('%.2f', $score) ?></small></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if ($totalPage > 1): ?>
  <nav class="text-center">
    <ul class="pagination">
      <li class="<?= $page == 1 ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => 1]) ?>">&lt;&lt; <?= Yii::t('app', 'First') ?></a></li>
      <li class="<?= $page == 1 ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => max(1, $page - 1)]) ?>">&lt; <?= Yii::t('app', 'Previous') ?></a></li>
      <?php
        $firstPage = min(max(1, $page - 5), max(1, $totalPage - 9));
        for ($i = $firstPage; $i < $firstPage + 10 && $i <= $totalPage; $i++): ?>
          <li class="<?= $page == $i ? 'active' : '' ?>"><a href="<?= Url::current(['page' => $i]) ?>"><?= $i ?></a></li>
      <?php endfor; ?>
      <li class="<?= $page == $totalPage ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => min($totalPage, $page + 1)]) ?>"><?= Yii::t('app', 'Next') ?> &gt;</a></li>
      <li class="<?= $page == $totalPage ? 'disabled' : '' ?>"><a href="<?= Url::current(['page' => $totalPage]) ?>"><?= Yii::t('app', 'Last') ?> &gt;&gt;</a></li>
    </ul>
  </nav>
<?php endif; ?>
