<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        //lets add the paypal components to allow us to do paypal sandbox simulations
        'paypal'=> [
            'class'        => 'kongoon\yii2\paypal\Paypal',
            'clientId'     => 'AQymVlI9wso_vCWTOUZXoqQUdg78w3_Mz8VmNWms7eSJYXf9B7UAh4kel-SggfCaZ6oORqXEjKaBGuBo',
            'clientSecret' => 'EIHkGryugLStmJgZP1uwEBKXb5q8nj6JAzjmyxlJdMqprZSbS-gTGTimh2VPwVb-KrmrqHSKaj_69ZLM',
            'isProduction' => false,
            // This is config file for the PayPal system
            'config'       => [
                'http.ConnectionTimeOut' => 30,
                'http.Retry'             => 1, //retry only once
                'mode'                   => \kongoon\yii2\paypal\Paypal::MODE_SANDBOX,    // sandbox | live
                'log.LogEnabled'         => YII_DEBUG ? 1 : 0, //based on our environment logs will b enabled or not
                'log.FileName'           => '@runtime/logs/paypal.log', //logs directory
                'log.LogLevel'           => \kongoon\yii2\paypal\Paypal::LOG_LEVEL_FINE,  // FINE | INFO | WARN | ERROR
            ]
        ],
    ],
];
