<?php

use app\models\Persons;
use app\models\Events;
use app\models\Results;

if ($person == null):
  $this->title = Yii::t('app', 'WCA Profile - Person Not Found');
?>
<h1 class="text-center">404</h1>
<?php
else:
  $this->title = Yii::t('app', 'WCA Profile - ') . $person[0]['name'];
?>
<h1 class="text-center"><?= $person[0]['name']; ?></h1>
<section class="clearfix">
  <div class="col-md-8 col-md-offset-2 table-responsive">
    <table class="table table-condensed" id="personalDetails">
      <thead>
        <tr class="info">
          <th><?= Yii::t('app', 'WCA ID') ?></th>
          <th><?= Yii::t('app', 'Name') ?></th>
          <th><?= Yii::t('app', 'Country') ?></th>
          <th><?= Yii::t('app', 'Gender') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php for ($i = 0, $count = count($person); $i < $count; $i++): ?>
        <tr <?= $i > 0 ? 'class="text-muted"' : '' ?>>
          <td><?php if ($i == 0): ?>
            <a target="_blank" href="https://www.worldcubeassociation.org/results/p.php?i=<?= $person[$i]['id'] ?>">
              <img src="/img/wca_logo.png" class="wca-logo" title="WCA" alt="WCA"><?= $person[$i]['id'] ?>
            </a>
          <?php else: ?>
            <em><?=Yii::t('app', '(was)') ?></em>
          <?php endif; ?></td>
          <td><?= $person[$i]['name'] ?></td>
          <td>
            <a href="/kinch/countries#<?= $person[$i]['countryId'] ?>">
              <i class="flag flag-<?= strtolower($person[$i]['countryCode']) ?>"></i>
              <span><?= Yii::t('region', $person[$i]['country']) ?></span>
            </a>
          </td>
          <td><?= Yii::t('app', Persons::getGenderName($person[$i]['gender'])) ?></td>
        </tr>
      <?php endfor; ?>
      </tbody>
    </table>
  </div>
</section>
<section class="clearfix">
  <div class="col-md-10 col-md-offset-1 table-responsive">
    <table class="table table-condensed vertical-center">
      <thead>
        <tr class="info">
          <th class="text-center"><?= Yii::t('app', 'Event') ?></th>
          <th class="text-right">NR</th>
          <th class="text-right">CR</th>
          <th class="text-right">WR</th>
          <th class="text-center"><?= Yii::t('app', 'Competition') ?></th>
          <th class="text-right"><?= Yii::t('app', 'Best') ?></th>
          <th class="text-right"><?= Yii::t('app', 'Average') ?></th>
          <th class="text-center"><?= Yii::t('app', 'Competition') ?></th>
          <th class="text-right">WR</th>
          <th class="text-right">CR</th>
          <th class="text-right">NR</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pbs as $event): ?>
        <tr>
          <th class="text-center"><?= Yii::t('event', Events::getCellName($event['s']['eventId'])) ?></th>
          <td class="text-right">
            <span class="ranking <?= $event['s']['countryRank'] <= 10 ? 'top10' : '' ?>"><?= $event['s']['countryRank'] ?></span>
          </td>
          <td class="text-right">
            <span class="ranking <?= $event['s']['continentRank'] <= 10 ? 'top10' : '' ?>"><?= $event['s']['continentRank'] ?></span>
          </td>
          <td class="text-right">
            <span class="ranking <?= $event['s']['worldRank'] <= 10 ? 'top10' : '' ?>"><?= $event['s']['worldRank'] ?></span>
          </td>
          <td class="text-center"><?= $event['s']['competitionName'] ?></td>
          <td class="text-right">
            <span class="personal-record <?= $event['s']['days'] > 1000 ? 'very-long' : '' ?>" data-toggle="tooltip" data-placement="left" title="<?= Yii::t('app', '{days,plural,=1{# day ago} =0{Today} other{# days ago}}', ['days' => $event['s']['days']]) ?>">
              <?= Results::formatTime($event['s']['best'], $event['s']['eventId']) ?>
            </span>
          </td>
          <td class="text-right">
            <span class="personal-record <?= isset($event['a']) && $event['a']['days'] > 1000 ? 'very-long' : '' ?>" data-toggle="tooltip" data-placement="right" title="<?= isset($event['a']) ? Yii::t('app', '{days,plural,=1{# day ago} =0{Today} other{# days ago}}', ['days' => $event['a']['days']]) : '' ?>">
              <?= isset($event['a']) ? Results::formatTime($event['a']['best'], $event['a']['eventId']) : '' ?>
            </span>
          </td>
          <td class="text-center"><?= isset($event['a']) ? $event['a']['competitionName'] : '' ?></td>
          <td class="text-right">
            <span class="ranking <?= isset($event['a']) && $event['a']['worldRank'] <= 10 ? 'top10' : '' ?>"><?= isset($event['a']) ? $event['a']['worldRank'] : '' ?></span>
          </td>
          <td class="text-right">
            <span class="ranking <?= isset($event['a']) && $event['a']['continentRank'] <= 10 ? 'top10' : '' ?>"><?= isset($event['a']) ? $event['a']['continentRank'] : '' ?></span>
          </td>
          <td class="text-right">
            <span class="ranking <?= isset($event['a']) && $event['a']['countryRank'] <= 10 ? 'top10' : '' ?>"><?= isset($event['a']) ? $event['a']['countryRank'] : '' ?></span>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php
endif;
?>
