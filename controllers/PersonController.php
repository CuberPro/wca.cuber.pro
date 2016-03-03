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
        $wcaids = array_unique(array_column($persons, 'id'));
        if (count($wcaids) == 1) {
            $this->redirect(['/person/' . $wcaids[0]]);
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
        if (!empty($person)) {
            $pbs = Persons::getPersonalRecords($personId);
            foreach ($pbs as &$event) {
                $event['s']['days'] = Competitions::dateDiff($event['s'], ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')]);
                if (isset($event['a'])) {
                    $event['a']['days'] = Competitions::dateDiff($event['a'], ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')]);
                }
            }
            unset($event);
        }
        return $this->render('profile', [
            'person' => $person,
            'pbs' => $pbs,
        ]);
    }
}
