<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Rounds".
 *
 * @property string $id
 * @property integer $rank
 * @property string $name
 * @property string $cellName
 */
class Rounds extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Rounds';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rank'], 'integer'],
            [['id'], 'string', 'max' => 1],
            [['name'], 'string', 'max' => 50],
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
            'rank' => 'Rank',
            'name' => 'Name',
            'cellName' => 'Cell Name',
        ];
    }
}
