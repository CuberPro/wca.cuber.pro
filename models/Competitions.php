<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Competitions".
 *
 * @property string $id
 * @property string $name
 * @property string $cityName
 * @property string $countryId
 * @property string $information
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property integer $endMonth
 * @property integer $endDay
 * @property string $eventSpecs
 * @property string $wcaDelegate
 * @property string $organiser
 * @property string $venue
 * @property string $venueAddress
 * @property string $venueDetails
 * @property string $external_website
 * @property string $cellName
 * @property integer $latitude
 * @property integer $longitude
 */
class Competitions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Competitions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'eventSpecs'], 'required'],
            [['information', 'eventSpecs', 'wcaDelegate', 'organiser'], 'string'],
            [['year', 'month', 'day', 'endMonth', 'endDay', 'latitude', 'longitude'], 'integer'],
            [['id'], 'string', 'max' => 32],
            [['name', 'cityName', 'countryId'], 'string', 'max' => 50],
            [['venue'], 'string', 'max' => 240],
            [['venueAddress', 'venueDetails'], 'string', 'max' => 120],
            [['external_website'], 'string', 'max' => 200],
            [['cellName'], 'string', 'max' => 45]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'cityName' => 'City Name',
            'countryId' => 'Country ID',
            'information' => 'Information',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'endMonth' => 'End Month',
            'endDay' => 'End Day',
            'eventSpecs' => 'Event Specs',
            'wcaDelegate' => 'Wca Delegate',
            'organiser' => 'Organiser',
            'venue' => 'Venue',
            'venueAddress' => 'Venue Address',
            'venueDetails' => 'Venue Details',
            'external_website' => 'External Website',
            'cellName' => 'Cell Name',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ];
    }

    public static function dateDiff($comp1, $comp2, $abs = true) {
        $time1 = strtotime(join('-', [$comp1['year'], $comp1['month'], $comp1['day']]));
        $time2 = strtotime(join('-', [$comp2['year'], $comp2['month'], $comp2['day']]));
        $negative = 1;
        if ($time1 > $time2 && !$abs) {
            $negative = -1;
        }
        return intval(abs($time2 - $time1) / 86400) * $negative;
    }
}
