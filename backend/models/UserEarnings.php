<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_earnings}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property double $value
 * @property int $status
 * @property int $created_at
 */
class UserEarnings extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_earnings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'value'], 'required'],
            [['user_id', 'status', 'created_at'], 'integer'],
            [['value'], 'number'],
            [['status'], 'default', 'value' => 10],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * properly calculate the earnings before saving
     * @return [type] [description]
     */
    public static function calcEarned()
    {
        $userAd = new UserAdvertisement;
        $totals = $userAd->getTotals();

        $withdrawal = UserWithdrawal::findOne(['user_id' => Yii::$app->user->id]);
        $withdrawalValue = $withdrawal !== null ? $withdrawal->value : 0;

        return $totals['income'] - $withdrawalValue;
    }

    /**
     * Add earnings to the proper tables
     * @param string  $type    Can be ad_clicks, binary, unilevel
     * @param integer $amount  The earned amount
     * @param [type]  $user_id User ID
     */
    public static function addEarnings($type = 'uncategorized', $amount = 0, $user_id = null)
    {
        $user_id = $user_id !== null ? $user_id : Yii::$app->user->id;
        $earnings = UserEarnings::findOne(['user_id' => $user_id, 'type' => $type]);

        if ($amount <= 0 || $earnings && $earnings->value <= $amount) {
            Yii::$app->session->setFlash(
                'danger',
                'Nothing has changed for your account.'
            );
            return false;
        }

        // Create a new earnings entry if this is the 1st time
        if ($earnings === null) {
            $earnings = new UserEarnings;
            $earnings->user_id = $user_id;
            $earnings->type = $type;
            $earnings->value = $amount;
        } else {
            $earnings->value = $amount;
        }

        if ($earnings->save()) {
            Yii::$app->session->setFlash(
                'success',
                'Your earnings total has been updated.'
            );
            return $earnings;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'value' => 'Value',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
