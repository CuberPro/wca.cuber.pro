<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Continents".
 *
 * @property string $id
 * @property string $name
 * @property string $recordName
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $zoom
 */
class Continents extends \yii\db\ActiveRecord {

    const CONTINENTS_LIST_CACHE_KEY = 'wca_continents_list';
    
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'Continents';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['latitude', 'longitude', 'zoom'], 'integer'],
            [['id', 'name'], 'string', 'max' => 50],
            [['recordName'], 'string', 'max' => 3]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'recordName' => 'Record Name',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'zoom' => 'Zoom',
        ];
    }

    public static function getContinents() {
        $c = Yii::$app->cache;
        $continentList = $c->get(self::CONTINENTS_LIST_CACHE_KEY);
        if ($continentList === false) {
            $continentList = self::find()->orderBy('name')->all();
            $c->set(self::CONTINENTS_LIST_CACHE_KEY, $continentList);
        }
        return $continentList;
    }

    public static function contains($id) {
        $continentList = self::getContinents();
        foreach ($continentList as $continent) {
            if ($continent->id === $id) {
                return true;
            }
        }
        return false;
    }
}
