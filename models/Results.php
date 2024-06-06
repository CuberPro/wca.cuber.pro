<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "Results".
 *
 * @property string $competitionId
 * @property string $eventId
 * @property string $roundId
 * @property integer $pos
 * @property integer $best
 * @property integer $average
 * @property string $personName
 * @property string $personId
 * @property string $personCountryId
 * @property string $formatId
 * @property integer $value1
 * @property integer $value2
 * @property integer $value3
 * @property integer $value4
 * @property integer $value5
 * @property string $regionalSingleRecord
 * @property string $regionalAverageRecord
 */
class Results extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Results';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pos', 'best', 'average', 'value1', 'value2', 'value3', 'value4', 'value5'], 'integer'],
            [['competitionId'], 'string', 'max' => 32],
            [['eventId'], 'string', 'max' => 6],
            [['roundId', 'formatId'], 'string', 'max' => 1],
            [['personName'], 'string', 'max' => 80],
            [['personId'], 'string', 'max' => 10],
            [['personCountryId'], 'string', 'max' => 50],
            [['regionalSingleRecord', 'regionalAverageRecord'], 'string', 'max' => 3]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'competitionId' => 'Competition ID',
            'eventId' => 'Event ID',
            'roundId' => 'Round ID',
            'pos' => 'Pos',
            'best' => 'Best',
            'average' => 'Average',
            'personName' => 'Person Name',
            'personId' => 'Person ID',
            'personCountryId' => 'Person Country ID',
            'formatId' => 'Format ID',
            'value1' => 'Value1',
            'value2' => 'Value2',
            'value3' => 'Value3',
            'value4' => 'Value4',
            'value5' => 'Value5',
            'regionalSingleRecord' => 'Regional Single Record',
            'regionalAverageRecord' => 'Regional Average Record',
        ];
    }

    public static function formatTime($result, $eventId, $encode = true) {
        if ($result == -1) {
            return 'DNF';
        }
        if ($result == -2) {
            return 'DNS';
        }
        if ($result == 0) {
            return '';
        }
        if ($eventId === '333fm') {
            if ($result > 1000) {
                $time = sprintf('%.2f', $result / 100);
            } else {
                $time = $result;
            }
        } elseif ($eventId === '333mbf' || ($eventId === '333mbo' && strlen($result) == 9)) {
            $difference = 99 - substr($result, 0, 2);
            $missed = intval(substr($result, -2));
            $time = self::formatTimeByEvent(substr($result, 3, -2), '333mbf');
            $solved = $difference + $missed;
            $attempted = $solved + $missed;
            $time = $solved . '/' . $attempted . ' ' . $time;
        } elseif ($eventId === '333mbo') {
            $solved = 99 - substr($result, 1, 2);
            $attempted = intval(substr($result, 3, 2));
            $time = self::formatTimeByEvent(substr($result, -5), '333mbo');
            $time = $solved . '/' . $attempted . ' ' . $time;
        } else {
            $hsecond = substr($result, -2);
            $second = substr($result, 0, -2);
            $time = self::formatTimeByEvent(intval($second)) . '.' . $hsecond;
        }
        if ($encode) {
            $time = Html::encode($time);
        }
        return $time;
    }

    private static function formatTimeByEvent($time, $eventId = '') {
        $time = intval($time);
        if ($time === 99999 && substr($eventId, 0, -1) === '333mb') {
            return 'unknown';
        }
        if ($time == 0) {
            return '0';
        }

        $seconds = $time % 60;
        $minutes = intval($time / 60);
        if ($eventId === '333mbf') {
            return sprintf('%d:%02d', $minutes, $seconds);
        }
        $hours = intval($minutes / 60);
        $minutes = $minutes % 60;
        return ltrim(sprintf('%d:%02d:%02d', $hours, $minutes, $seconds), '0:');
    }
}
