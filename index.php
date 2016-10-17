<?php

include "vendor/autoload.php";


if (!isset($_GET['page'])) {
    $page = 'index';
} else {
    $page = $_GET['page'];
}

$file = 'pages' . DIRECTORY_SEPARATOR . $page . '.php';

$user = \Sagi\Database\Identitiy::user();

$logined = \Sagi\Database\Identitiy::isLogined();

include "header.php";

if (file_exists($file)) {
    include $file;
} else {
    echo "<div class='alert alert-danger'> $page, adında bir sayfa bulunamadı";
}

include "footer.php";