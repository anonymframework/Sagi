<?php

include "vendor/autoload.php";

if (!\Sagi\Database\Identitiy::isLogined()) {
    header('Location: login.php');
}

if (!isset($_GET['page'])) {
    $page = 'index';
} else {
    $page = $_GET['page'];
}

$file = 'pages' . DIRECTORY_SEPARATOR . $page . '.php';

$user = \Sagi\Database\Identitiy::user();

$configs = \Models\Configs::find()->one();

include "header.php";

if (file_exists($file)) {
    include $file;
} else {
    echo "<div class='alert alert-danger'> $page, adında bir sayfa bulunamadı";
}

include "footer.php";