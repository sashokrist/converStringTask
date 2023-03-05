<?php

namespace app\controllers;

use app\models\InputForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionHello()
    {
        $input = '1234';
        return $this->render('hello', array('input' => $input));
    }

    public function actionInput()
    {
        $model = new InputForm();
        $output = array(); // initialize the output array

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $output = array_map(function($value) use ($model) {
                // remove the opening and closing brackets from the input string
                $model->input = trim($value, "[]");
                // remove any whitespace around the value
                $value = trim($value);
                // check if the value is an integer
                if (is_numeric($value) && str_contains($value, '.') === false) {
                    return (int)$value; // cast the value to integer and return it
                }
                // check if the value is a float
                if (is_numeric($value) && str_contains($value, '.') !== false) {
                    return (float)$value; // cast the value to float and return it
                }
                return trim($value, "'\"");
            }, explode(",", $model->input));
            return $this->render('entry-confirm', ['output' => $output]);
        }
        // either the page is initially displayed or there is some validation error
        return $this->render('inputForm', ['model' => $model]);
    }
}
