<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Countries".
 *
 * @property string $id
 * @property string $name
 * @property string $continentId
 * @property string $iso2
 */
class Countries extends \yii\db\ActiveRecord {

    const COUNTRIES_LIST_CACHE_KEY = 'wca_countries_list';

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'Countries';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id'], 'required'],
            [['id', 'name', 'continentId'], 'string', 'max' => 50],
            [['iso2'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'continentId' => 'Continent ID',
            'iso2' => 'Iso2',
        ];
    }

    public static function getCountries($includeMultipleCountries = false) {
        $c = Yii::$app->cache;
        $countryList = $c->get(self::COUNTRIES_LIST_CACHE_KEY);
        if ($countryList === false) {
            $countryList = self::find()->orderBy('name')->all();
            $c->set(self::COUNTRIES_LIST_CACHE_KEY, $countryList);
        }
        if (!$includeMultipleCountries) {
            $countryList = array_filter($countryList, function($country) {
                return !in_array($country['id'], ['XA', 'XE', 'XS']);
            });
        }
        return $countryList;
    }

    public static function contains($id) {
        $countryList = self::getCountries();
        foreach ($countryList as $country) {
            if ($country->id === $id) {
                return true;
            }
        }
        return false;
    }
}
