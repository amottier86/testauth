<?php

use Auth\Authentication;

require(__DIR__ . "/header.php");

// On détruit le cookie
$sessionId = $_COOKIE['session'] ?? null;
$signatureId = $_COOKIE['signature'] ?? null;
if($sessionId && $signatureId) {
    setcookie("session", null, time() - 1);
    setcookie("signature", null, time() - 1);
    unset($_SESSION);
    session_destroy();

    $auth = new Authentication();
    $auth->deleteSession($sessionId);

    header("Location: login.php");
}

die();