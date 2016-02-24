<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "KinchScores".
 *
 * @property string $personId
 * @property string $countryId
 * @property string $continentId
 * @property string $gender
 * @property string $eventId
 * @property string $worldSame
 * @property string $worldAll
 * @property string $continentSame
 * @property string $continentAll
 * @property string $countrySame
 * @property string $countryAll
 */
class KinchScores extends \yii\db\ActiveRecord
{
    const REGION_WORLD = 'world';
    const REGION_CONTINENT = 'continent';
    const REGION_COUNTRY = 'country';

    const GENDER_ALL = 'a';
    const GENDER_MALE = 'm';
    const GENDER_FEMALE = 'f';
    const GENDER_UNKNOWN = '';

    const COUNTRY_RANK_CACHE_KEY_PREFIX = 'kinch_country_';
    const CONTINENT_RANK_CACHE_KEY_PREFIX = 'kinch_continent_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'KinchScores';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['personId', 'countryId', 'continentId', 'gender', 'eventId'], 'required'],
            [['worldSame', 'worldAll', 'continentSame', 'continentAll', 'countrySame', 'countryAll'], 'number'],
            [['personId'], 'string', 'max' => 10],
            [['countryId', 'continentId'], 'string', 'max' => 50],
            [['gender'], 'string', 'max' => 1],
            [['eventId'], 'string', 'max' => 6]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'personId' => 'Person ID',
            'countryId' => 'Country ID',
            'continentId' => 'Continent ID',
            'gender' => 'Gender',
            'eventId' => 'Event ID',
            'worldSame' => 'World Same',
            'worldAll' => 'World All',
            'continentSame' => 'Continent Same',
            'continentAll' => 'Continent All',
            'countrySame' => 'Country Same',
            'countryAll' => 'Country All',
        ];
    }

    public static function getCountryRanks($regionId = self::REGION_WORLD, $gender = self::GENDER_ALL) {
        $regionType = Continents::contains($regionId) ? self::REGION_CONTINENT : self::REGION_WORLD;
        switch ($gender) {
            case self::GENDER_FEMALE:
            case self::GENDER_MALE:
            case self::GENDER_UNKNOWN:
                break;
            default:
                $gender = self::GENDER_ALL;
        }
        $cacheKey = self::COUNTRY_RANK_CACHE_KEY_PREFIX . ($regionType == self::REGION_WORLD ? self::REGION_WORLD : $regionId) . '_of_gender_' . $gender;
        $c = Yii::$app->cache;
        if (($countryScores = $c->get($cacheKey)) !== false) {
            return $countryScores;
        }
        $genderType = $gender === self::GENDER_ALL ? 'All' : 'Same';
        $scoreColumn = $regionType . $genderType;
        $query = (new Query())
            ->select([
                'k.countryId',
                'cy.name',
                'k.eventId',
                'score' => 'MAX(`k`.`' . $scoreColumn . '`)'])
            ->from(['k' => 'KinchScores'])
            ->leftJoin(['cy' => 'Countries'], '`k`.`countryId`=`cy`.`id`')
            ->groupBy(['k.countryId', 'k.eventId']);
        $nextWhere = 'where';
        if ($regionType == self::REGION_CONTINENT) {
            $query->$nextWhere(['k.continentId' => $regionId]);
            $nextWhere = 'andWhere';
        }
        if ($gender != self::GENDER_ALL) {
            $query->$nextWhere(['k.gender' => $gender]);
            $nextWhere = 'andWhere';
        }
        $countryData = $query->all();
        $eventList = Events::getEventIds();
        $eventList = array_fill_keys(array_values($eventList), 0.0);
        $eventCount = count($eventList);
        $countryScores = [];
        foreach ($countryData as $country) {
            if (!isset($countryScores[$country['countryId']])) {
                $countryScores[$country['countryId']] = [
                    'name' => $country['name'],
                    'scores' => $eventList,
                ];
            }
            $countryScores[$country['countryId']]['scores'][$country['eventId']] = floatval($country['score']);
        }
        foreach ($countryScores as &$country) {
            $country['scores']['overall'] = round(array_sum($country['scores']) / $eventCount, 2);
        }
        unset($country);
        uasort($countryScores, function($a, $b) {$ret = $b['scores']['overall'] - $a['scores']['overall'];return $ret > 0 ? 1 : ($ret == 0 ? 0 : -1);});
        $c->set($cacheKey, $countryScores);
        return $countryScores;
    }

    public static function getContinentRanks($gender = self::GENDER_ALL) {
        switch ($gender) {
            case self::GENDER_FEMALE:
            case self::GENDER_MALE:
            case self::GENDER_UNKNOWN:
                break;
            default:
                $gender = self::GENDER_ALL;
        }
        $cacheKey = self::CONTINENT_RANK_CACHE_KEY_PREFIX . 'of_gender_' . $gender;
        $c = Yii::$app->cache;
        if (($continentScores = $c->get($cacheKey)) !== false) {
            return $continentScores;
        }
        $genderType = $gender === self::GENDER_ALL ? 'All' : 'Same';
        $scoreColumn = self::REGION_WORLD . $genderType;
        $query = (new Query())
            ->select([
                'k.continentId',
                'ct.name',
                'k.eventId',
                'score' => 'MAX(`k`.`' . $scoreColumn . '`)'])
            ->from(['k' => 'KinchScores'])
            ->leftJoin(['ct' => 'Continents'], '`k`.`continentId`=`ct`.`id`')
            ->groupBy(['k.continentId', 'k.eventId']);
        if ($gender != self::GENDER_ALL) {
            $query->where(['k.gender' => $gender]);
        }
        $continentData = $query->all();
        $eventList = Events::getEventIds();
        $eventList = array_fill_keys(array_values($eventList), 0.0);
        $eventCount = count($eventList);
        $continentScores = [];
        foreach ($continentData as $continent) {
            if (!isset($continentScores[$continent['continentId']])) {
                $continentScores[$continent['continentId']] = [
                    'name' => $continent['name'],
                    'scores' => $eventList,
                ];
            }
            $continentScores[$continent['continentId']]['scores'][$continent['eventId']] = floatval($continent['score']);
        }
        foreach ($continentScores as &$continent) {
            $continent['scores']['overall'] = round(array_sum($continent['scores']) / $eventCount, 2);
        }
        unset($continent);
        uasort($continentScores, function($a, $b) {$ret = $b['scores']['overall'] - $a['scores']['overall'];return $ret > 0 ? 1 : ($ret == 0 ? 0 : -1);});
        $c->set($cacheKey, $continentScores);
        return $continentScores;
    }
}
