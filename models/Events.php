<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Events".
 *
 * @property string $id
 * @property string $name
 * @property integer $rank
 * @property string $format
 * @property string $cellName
 */
class Events extends \yii\db\ActiveRecord
{

    const EVENTS_LIST_CACHE_KEY = 'wca_events_list';
    const RANK_LIMIT = 990;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Events';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['rank'], 'integer'],
            [['id'], 'string', 'max' => 6],
            [['name'], 'string', 'max' => 54],
            [['format'], 'string', 'max' => 10],
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
            'rank' => 'Rank',
            'format' => 'Format',
            'cellName' => 'Cell Name',
        ];
    }

    private static function getEvents($getAll = false) {
        $c = Yii::$app->cache;
        $cacheKey = self::EVENTS_LIST_CACHE_KEY . ($getAll ? '_all' : '');
        $eventInfo = $c->get($cacheKey);
        if ($eventInfo === false) {
            $eventInfo = self::find()
                ->where(['<', 'rank', $getAll ? 10000 : self::RANK_LIMIT])
                ->orderBy('rank')
            ->all();
            $c->set($cacheKey, $eventInfo);
        }
        return $eventInfo;
    }

    public static function getEventIds() {
        $eventInfo = self::getEvents();
        $ids = [];
        foreach ($eventInfo as $event) {
            $ids[] = $event->id;
        }
        return $ids;
    }

    public static function getCellName($eventId) {
        $eventInfo = self::getEvents(true);
        foreach ($eventInfo as $event) {
            if ($event->id === $eventId) {
                return $event->cellName;
            }
        }
        return $eventId;
    }

    public static function getName($eventId) {
        $eventInfo = self::getEvents(true);
        foreach ($eventInfo as $event) {
            if ($event->id === $eventId) {
                return $event->name;
            }
        }
        return $eventId;
    }
}
