<?php

use Auth\Controller\Authentication;

require(__DIR__ . "/header.php");

$id = null;

$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if($id) {
    $auth = new Authentication();
    $auth->deleteSession($id);

    header("Location: index.php");
}