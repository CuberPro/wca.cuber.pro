<?php

namespace app\controllers;

use Yii;
use app\controllers\base\BaseController;
use app\models\Persons;
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
        $pbs = [];
        $oprs = [];
        if (!empty($person)) {
            $pbs = Persons::getPersonalRecords($personId);
            foreach ($pbs as &$event) {
                $event['s']['days'] = Competitions::dateDiff($event['s'], ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')]);
                if (isset($event['a'])) {
                    $event['a']['days'] = Competitions::dateDiff($event['a'], ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')]);
                }
            }
            unset($event);
            $oprs = Persons::getOldestStandingPersonalRecords($personId);
            foreach ($oprs as &$opr) {
                $opr['days'] = Competitions::dateDiff($opr, ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')]);
            }
            unset($opr);
        }
        return $this->render('profile', [
            'person' => $person,
            'oprs' => $oprs,
            'pbs' => $pbs,
        ]);
    }
}
