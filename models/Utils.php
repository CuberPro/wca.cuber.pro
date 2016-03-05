<?php

namespace app\models;

use Yii;

class Utils {

	private static $coll;

	private static function getCollator() {
		if (!isset(self::$coll)) {
			self::$coll = collator_create(Yii::$app->language);
		}
		return self::$coll;
	}

	public static function translateAndSort(array $data, string $property, string $translateCategory, bool $reverse = false) {
		array_walk($data, function(&$x, $k) use ($property, $translateCategory){
			$x[$property] = Yii::t($translateCategory, $x[$property]);
		});
		usort($data, function($a, $b) use ($property, $reverse) {
			$coll = self::getCollator();
			return $coll->compare($a[$property], $b[$property]) * ($reverse ? -1 : 1);
		});
		return $data;
	}
}