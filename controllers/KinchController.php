<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use app\controllers\base\BaseController;
use app\models\KinchScores;
use app\models\Events;

class KinchController extends BaseController
{
    public function actionCountries()
    {
        $r = Yii::$app->request;
    	$regionId = $r->get('regionId', KinchScores::REGION_WORLD);
    	$gender = $r->get('gender', KinchScores::GENDER_ALL);
    	$eventList = Events::getEventIds();
    	$countryList = KinchScores::getCountryRanks($regionId, $gender);
        return $this->render('regions', [
        	'type' => 'country',
        	'regionList' => $countryList,
        	'eventList' => $eventList]);
    }

    public function actionIndex()
    {
        $this->redirect(Url::to(['/kinch/countries']));
    }

    public function actionContinents()
    {
        $r = Yii::$app->request;
    	$gender = $r->get('gender', KinchScores::GENDER_ALL);
    	$eventList = Events::getEventIds();
    	$continentList = KinchScores::getContinentRanks($gender);
        return $this->render('regions', [
        	'type' => 'continent',
        	'regionList' => $continentList,
        	'eventList' => $eventList]);
    }

}
