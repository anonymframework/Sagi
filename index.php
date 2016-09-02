<?php
include "vendor/autoload.php"; ?>

<?php include "header.php";

$instagram = \Models\Instagram::find()->limit(5)->in('user_id', function (\Sagi\Database\Model $model) {
    return $model->setTable('user')->select('id')->where('gender', 0)->order('created_at', 'DESC');
});


var_dump($instagram->all());

?>


<?php include "footer.php"; ?>


