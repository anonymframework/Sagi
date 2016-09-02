<?php
include "vendor/autoload.php"; ?>

<?php include "header.php";

use Models\Premiums;


$selected = Premiums::find()->order('created_at', 'DESC')->limit(10);



$last = \Models\User::find()->order('id', 'DESC')->limit(5);
$random = \Models\User::find()->order('RAND()', 'DESC')->limit(5);

?>

<div class="row">

    <?php  echo $selected->exists() ? $selected->all()->display('premium') : '' ?>
</div>

<div class="row">
    <?php echo $last->exists() ? $last->all()->display('items'): '' ?>
</div>

<div class="row">
    <?php echo $random->exists() ? $random->all()->display('items') : ''; ?>
</div>




<?php include "footer.php"; ?>


