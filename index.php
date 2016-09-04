<?php
include "vendor/autoload.php";
$links = false;
include "header.php";
?>

<div class="loading" style="display: none">

</div>

<div class="messages" style="display: none;"></div>

<div id="content" class="col-lg-12 col-md-12 col-xs-12">

</div>

<div id="foot col-lg-12 col-md-12 col-xs-12">
    <div id="more"></div>
</div>


<?php include "footer.php"; ?>

<?php if (isset($_GET['call'])): ?>
    <script>
        <?php echo $_GET['call']; ?>(15);
    </script>

<?php else: ?>

    <script>
        premium();
        rand(15);
    </script>

<?php endif; ?>

