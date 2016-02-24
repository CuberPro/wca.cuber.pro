<?php

namespace app\controllers;

use Yii;
use app\controllers\base\BaseController;
use app\models\Persons;
use app\models\Results;
use app\models\Events;
use app\models\Competitions;

class PersonController extends BaseController
{

    public function actionIndex()
    {
        $r = Yii::$app->request;
        $query = trim($r->get('query', ''));
        $persons = Persons::queryByIDOrName($query);
        if (count($persons) == 1) {
            $this->redirect(['/person/' . $persons[0]['id']]);
        }
        return $this->render('index', [
            'persons' => $persons,
            'query' => $query,
        ]);
    }

    public function actionProfile() {
        $r = Yii::$app->request;
        $personId = $r->get('i');
        $person = Persons::getPerson($personId, true);
        $singles = [];
        $pbs = [];
        if (!empty($person)) {
            $pbs = Persons::getOldestStandingPersonalRecords($personId);
            foreach ($pbs as &$pb) {
                $pb['best'] = Results::formatTime($pb['best'], $pb['eventId']);
                $pb['average'] = Results::formatTime($pb['average'], $pb['eventId']);
                $pb['eventName'] = Events::getCellName($pb['eventId']);
                $pb['days'] = Competitions::dateDiff($pb, ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')]);
            }
            unset($pb);
        }
        return $this->render('profile', ['person' => $person, 'pbs' => $pbs]);
    }
}
