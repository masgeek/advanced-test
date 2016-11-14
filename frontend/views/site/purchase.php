<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;

//action for tehy paypal pay click
$paypalAction = Url::toRoute(['/site/paypal']);
?>

<!-- this will show the flash messages-->
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h2>Kindly purchase to download the file</h2>
    </div>
</div>
<div>
    <div class="col-md-6">
        <!-- paypal button here -->
    </div>
    <div>
        <!-- file download here -->
        <!-- use data from table to determine if its been paid for -->
        <?= Html::a('<span class="glyphicon glyphicon-list-alt"></span><br/>Purchase Item',
            $paypalAction,['class'=>'btn btn-lg btn-danger btn-block', 'role'=>'button']) ?>
    </div>
</div>
