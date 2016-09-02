<?php
include "vendor/autoload.php"; ?>

<?php include "header.php";

$instagram = \Models\Instagram::find()->limit(5)->in('user_id', function(\Sagi\Database\Model $model){
    return $model->where('gender', 0);
});

$instagram->all();

?>




<?php include "footer.php"; ?>


