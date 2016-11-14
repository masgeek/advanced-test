<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;

//link to the file to be downloaded
$itemDownload = Yii::getAlias('@web') . '/uploads/file.zip';
?>

<!-- this will show teh flash messages-->
<div class="row"><?php
    foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
        echo '<div class="alert alert-' . $key . '">' . $message . '</div>';
    }
    ?>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h3>Click to download the file</h3>
    </div>
</div>
<div>
    <div class="col-md-6">
        Filename.zip
        Size 20MB

    </div>
    <div class="col-md-6">
        <!-- file download here -->
        <!-- use data from table to determine if its been paid for -->
        <?= Html::a('<span class="glyphicon glyphicon-list-alt"></span><br/>Download Item',
            $itemDownload, ['class' => 'btn btn-lg btn-success btn-block', 'role' => 'button']) ?>
    </div>
</div>