<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Scrambles".
 *
 * @property integer $scrambleId
 * @property string $competitionId
 * @property string $eventId
 * @property string $roundId
 * @property string $groupId
 * @property integer $isExtra
 * @property integer $scrambleNum
 * @property string $scramble
 */
class Scrambles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Scrambles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scrambleId', 'competitionId', 'eventId', 'roundId', 'groupId', 'isExtra', 'scrambleNum', 'scramble'], 'required'],
            [['scrambleId', 'isExtra', 'scrambleNum'], 'integer'],
            [['competitionId'], 'string', 'max' => 32],
            [['eventId'], 'string', 'max' => 6],
            [['roundId'], 'string', 'max' => 1],
            [['groupId'], 'string', 'max' => 3],
            [['scramble'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scrambleId' => 'Scramble ID',
            'competitionId' => 'Competition ID',
            'eventId' => 'Event ID',
            'roundId' => 'Round ID',
            'groupId' => 'Group ID',
            'isExtra' => 'Is Extra',
            'scrambleNum' => 'Scramble Num',
            'scramble' => 'Scramble',
        ];
    }
}
