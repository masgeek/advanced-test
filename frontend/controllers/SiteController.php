<?php
namespace frontend\controllers;



use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;


use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\ItemList;
use PayPal\Api\RedirectUrls;

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
                'only' => ['logout', 'signup', 'purchase', 'paypal', 'result', 'download'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'purchase', 'paypal', 'result', 'download'],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays paypal purchase page.
     *
     * @return mixed
     */
    public function actionPurchase()
    {
        return $this->render('purchase');
    }

    public function actionPaypal()
    {
        //initalize the paypal extension so that we can get teh default parameters
        Yii::$app->paypal->init();
        $apiContext = Yii::$app->paypal->getApiContext();

        $payer = new Payer();
        $payer->setPaymentMethod("paypal"); //method is by pauypal account


        $item1 = new \PayPal\Api\Item(); //set item details
        $item1->setName('Software')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice(10);

        $itemList = new ItemList();
        $itemList->setItems(array($item1));
        $details = new Details();
        /*$details->setShipping(1.2) ///no need for shipping on thiss one its a digitl good
            ->setTax(1.3)
            ->setSubtotal(17.50);*/
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal(10)//set the amount
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());

        $baseUrl = 'http://www.tsobu.co.ke';//getBaseUrl(); //we will need to host it so that the redirect after payment works okay

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/advanced-test/frontend/web/index.php?r=site/result?status=true")
            ->setCancelUrl("$baseUrl/advanced-test/frontend/web/index.php?r=site/result?status=false");

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));
        $request = clone $payment;

        try {
            $payment->create($apiContext);
        } catch (Exception $ex) {
            ResultPrinter::printError("Created Payment Order Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            exit(1);
        }
        $approvalUrl = $payment->getApprovalLink();

        //now let us redirect to the approval URL to allow the client to pay
        $this->redirect($approvalUrl);

    }

    public function actionDownload()
    {
        return $this->render('download');
    }

    /**
     * @return \yii\web\Response
     *
     * process the result from the paypal payment action
     *
     * I will need to move to a live domain and cleanup the URL for better and roust evaluation
     */
    public function actionResult()
    {
        $status = isset($_GET['status']) ? $_GET['status'] : false;
        $token = isset($_GET['token']) ? $_GET['token'] : null;
//lest check the success status
        if ($status || $status == 'true') {
            Yii::$app->getSession()->setFlash('success', 'Item purchased successfully, please click on the link to download');
            return $this->redirect(['download']);
        }
        //go back to the main page and say it was cancelled
        Yii::$app->getSession()->setFlash('warning', 'You have cancelled the transaction');
        $this->redirect(['purchase']);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
