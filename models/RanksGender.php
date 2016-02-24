<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "RanksGender".
 *
 * @property string $personId
 * @property string $eventId
 * @property string $gender
 * @property string $type
 * @property integer $best
 * @property integer $worldRank
 * @property integer $continentRank
 * @property integer $countryRank
 */
class RanksGender extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'RanksGender';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['personId', 'eventId', 'gender', 'type'], 'required'],
            [['best', 'worldRank', 'continentRank', 'countryRank'], 'integer'],
            [['personId'], 'string', 'max' => 10],
            [['eventId'], 'string', 'max' => 6],
            [['gender', 'type'], 'string', 'max' => 1]
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
            'gender' => 'Gender',
            'type' => 'Type',
            'best' => 'Best',
            'worldRank' => 'World Rank',
            'continentRank' => 'Continent Rank',
            'countryRank' => 'Country Rank',
        ];
    }
}
