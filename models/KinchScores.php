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
class KinchScores extends \yii\db\ActiveRecord {
    const REGION_WORLD = 'world';
    const REGION_CONTINENT = 'continent';
    const REGION_COUNTRY = 'country';

    const GENDER_ALL = 'a';
    const GENDER_MALE = 'm';
    const GENDER_FEMALE = 'f';
    const GENDER_UNKNOWN = '';

    const PERSON_RANK_CACHE_KEY_PREFIX = 'kinch_person_';
    const COUNTRY_RANK_CACHE_KEY_PREFIX = 'kinch_country_';
    const CONTINENT_RANK_CACHE_KEY_PREFIX = 'kinch_continent_';

    public static function getRankTypes() {
        return [
            '100 Persons' => [
                'label' => Yii::t('app', '100 Persons'),
                'type' => 'rank',
                'limit' => 100,
            ],
            '1000 Persons' => [
                'label' => Yii::t('app', '1000 Persons'),
                'type' => 'rank',
                'limit' => 1000,
            ],
            'All Persons' => [
                'label' => Yii::t('app', 'All Persons'),
                'type' => 'count',
                'limit' => 100,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'KinchScores';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
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
    public function attributeLabels() {
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
    public static function getPersonRanks($regionId = self::REGION_WORLD, $gender = self::GENDER_ALL, $type = 0, $page = 1) {
        $regionType = Continents::contains($regionId) ? self::REGION_CONTINENT : (Countries::contains($regionId) ? self::REGION_COUNTRY : self::REGION_WORLD);
        self::normalizeGender($gender);
        $genderType = $gender == self::GENDER_ALL ? 'All' : 'Same';
        $scoreColumn = $regionType . $genderType;

        $rankTypes = self::getRankTypes();
        if (!isset($rankTypes[$type])) {
            $type = array_keys($rankTypes)[0];
        }
        $rankType = $rankTypes[$type];

        $cacheKey = self::PERSON_RANK_CACHE_KEY_PREFIX . $regionId . '_gender_' . $gender . '_' . $type;
        if ($rankType['type'] == 'count') {
            $cacheKey .= '_page_' . $page;
        }
        $c = Yii::$app->cache;
        if (($result = $c->get($cacheKey)) !== false) {
            return $result;
        }

        $basicQuery = (new Query())
            ->from(['k' => 'KinchScores']);
        switch ($regionType) {
            case self::REGION_CONTINENT:
            $basicQuery->andWhere(['k.continentId' => $regionId]);
            break;
            case self::REGION_COUNTRY:
            $basicQuery->andWhere(['k.countryId' => $regionId]);
            break;
            case self::REGION_WORLD:
            default:
                break;
        }
        if ($gender != self::GENDER_ALL) {
            $basicQuery->andWhere(['k.gender' => $gender]);
        }

        $query = clone $basicQuery;
        $query->select(['count' => 'COUNT(DISTINCT(`personId`))']);
        $totalPerson = intval($query->one()['count']);

        $persons = [];
        $rankOffset = 0;
        $countOffset = 0;
        $totalPage = 1;

        if ($rankType['type'] == 'rank') {
            $query = clone $basicQuery;
            if ($totalPerson > $rankType['limit']) {
                $query
                    ->select([
                        'overall' => 'ROUND(SUM(`k`.`' . $scoreColumn . '`)/18, 2)',
                    ])
                    ->groupBy(['k.personId'])
                    ->orderBy([
                        'overall' => SORT_DESC,
                    ])
                    ->limit(1)
                    ->offset($rankType['limit'] - 1);
                $threshold = $query->one()['overall'];
            }
        } else {
            $page = max(--$page, 0);
            $pageSize = $rankType['limit'];
            $offset = $page * $pageSize;
            if ($offset >= $totalPerson) {
                $page = 0;
                $offset = 0;
            }
            $page++;
            $countOffset = $offset;
            $totalPage = max(1, intval(ceil($totalPerson / $pageSize)));
        }
        $query = clone $basicQuery;
        $query
            ->select([
                'k.personId',
                'p.name',
                'countryId' => 'cy.id',
                'countryName' => 'cy.name',
                'countryCode' => 'cy.iso2',
                'overall' => 'ROUND(SUM(`k`.`' . $scoreColumn . '`)/18, 2)',
            ])
            ->leftJoin(['p' => 'Persons'], '`k`.`personId`=`p`.`id` AND `p`.`subid`=1')
            ->leftJoin(['cy' => 'Countries'], '`p`.`countryId`=`cy`.`id`')
            ->groupBy(['k.personId'])
            ->orderBy([
                'overall' => SORT_DESC,
            ]);
        if (isset($offset)) {
            $query->offset($offset);
        }
        if (isset($pageSize)) {
            $query->limit($pageSize);
        }
        if (isset($threshold)) {
            $query->having(['>=', 'overall', $threshold]);
        }
        $persons = $query->all();

        if (count($persons) > 0) {
            if ($rankType['type'] == 'count') {
                $firstScore = $persons[0]['overall'];
                $query = clone $basicQuery;
                $query
                    ->select([
                        'overall' => 'ROUND(SUM(`k`.`' . $scoreColumn . '`)/18, 2)',
                    ])
                    ->groupBy(['k.personId'])
                    ->having(['>', 'overall', $firstScore])
                    ->orderBy([
                        'overall' => SORT_DESC,
                    ]);
                $rankOffset = count($query->all());
            }
            $personIds = array_column($persons, 'personId');
            $persons = array_combine($personIds, $persons);
            array_walk($persons, function(&$onePerson){$onePerson['overall'] = floatval($onePerson['overall']);});
            unset($onePerson);
            $query = (new Query())
                ->select([
                    'personId',
                    'eventId',
                    $scoreColumn,
                ])
                ->from('KinchScores')
                ->where(['IN', 'personId', $personIds]);
            $details = $query->all();
            foreach ($details as $detail) {
                $persons[$detail['personId']][$detail['eventId']] = floatval($detail[$scoreColumn]);
            }
        }
        $result = [$persons, $rankOffset, $countOffset, $page, $totalPage];
        $c->set($cacheKey, $result);
        return $result;
    }

    private static function normalizeGender(&$gender) {
        switch ($gender) {
            case self::GENDER_FEMALE:
            case self::GENDER_MALE:
            case self::GENDER_UNKNOWN:
                break;
            default:
                $gender = self::GENDER_ALL;
        }
    }

    public static function getCountryRanks($regionId = self::REGION_WORLD, $gender = self::GENDER_ALL) {
        $regionType = Continents::contains($regionId) ? self::REGION_CONTINENT : self::REGION_WORLD;
        self::normalizeGender($gender);
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
                'code' => 'cy.iso2',
                'k.eventId',
                'score' => 'MAX(`k`.`' . $scoreColumn . '`)',
            ])
            ->from(['k' => 'KinchScores'])
            ->leftJoin(['cy' => 'Countries'], '`k`.`countryId`=`cy`.`id`')
            ->groupBy(['k.countryId', 'k.eventId']);
        if ($regionType == self::REGION_CONTINENT) {
            $query->andWhere(['k.continentId' => $regionId]);
        }
        if ($gender != self::GENDER_ALL) {
            $query->andWhere(['k.gender' => $gender]);
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
                    'code' => $country['code'],
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
        self::normalizeGender($gender);
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
