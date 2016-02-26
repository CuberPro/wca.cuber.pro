<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "Persons".
 *
 * @property string $id
 * @property integer $subid
 * @property string $name
 * @property string $countryId
 * @property string $gender
 */
class Persons extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'Persons';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['subid'], 'integer'],
            [['id'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 80],
            [['countryId'], 'string', 'max' => 50],
            [['gender'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'subid' => 'Subid',
            'name' => 'Name',
            'countryId' => 'Country ID',
            'gender' => 'Gender',
        ];
    }

    public static function getPerson($id, $all = false) {
        $query = (new Query())
            ->select([
                'p.name',
                'p.id',
                'p.gender',
                'countryId' => 'p.countryId',
                'country' => 'c.name',
                'p.subid'])
            ->from(['p' => 'Persons'])
            ->leftJoin(['c' => 'Countries'], '`p`.`countryId`=`c`.`id`')
            ->where(['p.id' => $id])
            ->orderBy('p.subid');
        return $all ? $query->all() : $query->one();
    }

    public static function queryByIDOrName($query) {
        $query = trim($query);
        if (strlen($query) == 0) {
            return [];
        }
        $queries = array_filter(preg_split('/\s+/', $query), function ($x) {return strlen($x) > 0;});
        $persons = (new Query())
            ->select([
                'p.name',
                'p.id',
                'p.gender',
                'country' => 'c.name'])
            ->from(['p' => 'Persons'])
            ->leftJoin(['c' => 'Countries'], '`p`.`countryId`=`c`.`id`')
            ->where(['like', 'p.name', $queries])
            ->orWhere(['like', 'p.id', $queries])
            ->orderBy('p.name')
        ->all();
        return $persons;
    }

    public static function getPersonalRecords($personId) {
        $singles = (new Query())
            ->select([
                'rk.eventId',
                'rk.best',
                'rk.worldRank',
                'rk.continentRank',
                'rk.countryRank',
                'competitionId' => 'c.id',
                'competitionName' => 'c.cellName',
                'c.year',
                'c.month',
                'c.day',
            ])
            ->from(['rk' => 'RanksSingle'])
            ->leftJoin(['r' => 'Results'], [
                'AND',
                '`rk`.`eventId`=`r`.`eventId`',
                '`rk`.`personId`=`r`.`personId`',
                '`rk`.`best`=`r`.`best`',
            ])
            ->leftJoin(['c' => 'Competitions'], '`r`.`competitionId`=`c`.`id`')
            ->leftJoin(['e' => 'Events'], '`rk`.`eventId`=`e`.`id`')
            ->where(['rk.personId' => $personId])
            ->orderBy(['e.rank' => SORT_ASC])
        ->all();
        $averages = (new Query())
            ->select([
                'rk.eventId',
                'rk.best',
                'rk.worldRank',
                'rk.continentRank',
                'rk.countryRank',
                'competitionId' => 'c.id',
                'competitionName' => 'c.cellName',
                'c.year',
                'c.month',
                'c.day',
            ])
            ->from(['rk' => 'RanksAverage'])
            ->leftJoin(['r' => 'Results'], [
                'AND',
                '`rk`.`eventId`=`r`.`eventId`',
                '`rk`.`personId`=`r`.`personId`',
                '`rk`.`best`=`r`.`average`',
            ])
            ->leftJoin(['c' => 'Competitions'], '`r`.`competitionId`=`c`.`id`')
            ->where(['rk.personId' => $personId])
        ->all();
        $results = [];
        foreach ($singles as $single) {
            if (isset($results[$single['eventId']]['s'])) {
                if (Competitions::dateDiff($single, $results[$single['eventId']]['s'], false) > 0) {
                    continue;
                }
            }
            $results[$single['eventId']]['s'] = $single;
        }
        foreach ($averages as $average) {
            if (isset($results[$average['eventId']]['a'])) {
                if (Competitions::dateDiff($average, $results[$average['eventId']]['a'], false) > 0) {
                    continue;
                }
            }
            $results[$average['eventId']]['a'] = $average;
        }
        return $results;
    }

    public static function getOldestStandingPersonalRecords($personId) {
        $singles = (new Query())
            ->select([
                'personId',
                'eventId',
                'best',
                '0 AS `average`'])
            ->from('RanksSingle')
            ->where(['personId' => $personId]);
        $averages = (new Query())
            ->select([
                'personId',
                'eventId',
                '0 AS `best`',
                'average' => 'best'])
            ->from('RanksAverage')
            ->where(['personId' => $personId]);
        $results = (new Query())
            ->select([
                'b.*',
                'competitionId' => 'c.id',
                'competitionName' => 'c.cellName',
                'c.year',
                'c.month',
                'c.day'
            ])
            ->from(['b' => $singles->union($averages)])
            ->leftJoin(['r' => 'Results'], [
                'AND',
                    '`b`.`eventId`=`r`.`eventId`',
                    '`b`.`personId`=`r`.`personId`',
                    ['OR',
                        ['AND',
                            ['!=', 'b.best', 0],
                            '`b`.`best`=`r`.`best`'],
                        ['AND',
                            ['!=', 'b.average', 0],
                            '`b`.`average`=`r`.`average`']]])
            ->leftJoin(['c' => 'Competitions'], '`r`.`competitionId`=`c`.`id`')
            ->leftJoin(['e' => 'Events'], '`b`.`eventId`=`e`.`id`')
            ->groupBy(['eventId', 'best', 'average'])
            ->orderBy([
                'c.year' => SORT_ASC,
                'c.month' => SORT_ASC,
                'c.day' => SORT_ASC,
                'e.rank' => SORT_ASC,
                'b.best' => SORT_DESC])
        ->all();
        return $results;
    }

    public static function getGenderName($gender) {
        switch ($gender) {
            case 'm':
                return Yii::t('app', 'Male');
            case 'f':
                return Yii::t('app', 'Female');
            default:
                return Yii::t('app', 'Unknown');
        }
    }

}
