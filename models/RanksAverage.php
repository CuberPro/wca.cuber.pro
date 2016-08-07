<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "RanksAverage".
 *
 * @property string $personId
 * @property string $eventId
 * @property integer $best
 * @property integer $worldRank
 * @property integer $continentRank
 * @property integer $countryRank
 */
class RanksAverage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'RanksAverage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['personId', 'eventId'], 'required'],
            [['best', 'worldRank', 'continentRank', 'countryRank'], 'integer'],
            [['personId'], 'string', 'max' => 10],
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
            'eventId' => 'Event ID',
            'best' => 'Best',
            'worldRank' => 'World Rank',
            'continentRank' => 'Continent Rank',
            'countryRank' => 'Country Rank',
        ];
    }
}
