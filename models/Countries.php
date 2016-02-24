<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Countries".
 *
 * @property string $id
 * @property string $name
 * @property string $continentId
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $zoom
 * @property string $iso2
 */
class Countries extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Countries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['latitude', 'longitude', 'zoom'], 'integer'],
            [['id', 'name', 'continentId'], 'string', 'max' => 50],
            [['iso2'], 'string', 'max' => 2]
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
            'continentId' => 'Continent ID',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'zoom' => 'Zoom',
            'iso2' => 'Iso2',
        ];
    }
}
