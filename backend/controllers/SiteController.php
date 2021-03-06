<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'click-ads'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $id = Yii::$app->user->id;
        $userAds = new \app\models\UserAdvertisement;
        $totals = $userAds->getTotals();
        // $binaryStatementEarned = \app\models\UserEarnings::calcBinaryEarned();
        $directReferrals = \app\models\UserEarnings::calcDirectReferrals();
        $directReferralsCount = \app\models\UserNetwork::countUnilevelMembers();
        $network = \app\models\UserNetwork::getBinary($id, 5);
        $earned = \app\models\UserEarnings::getEarnings();
        // return var_dump($earned['ad_clicks']);

        return $this->render('index', [
            'clickCount'  => $totals['count'],
            'directReferrals' => $directReferrals,
            'directReferralsCount' => $directReferralsCount,
            'network' => $network,
            'earned' => $earned,
        ]);
    }

    public function actionClickAds()
    {
        $userAdvertisement = new \app\models\UserAdvertisement;
        $ads = \app\models\Advertisement::getToday();
        $userAds = \app\models\UserAdvertisement::getAllDone();
        $totals = $userAdvertisement->getTotals();

        // return var_dump($ads);
        return $this->render('clickads', [
            'ads' => $ads,
            'userAds' => $userAds,
            'totalIncome' => $totals['income'],
            'clickCount'  => $totals['count']
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
