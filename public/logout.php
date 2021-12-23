<?php

use Auth\Controller\Authentication;

require(__DIR__ . "/header.php");

$auth = new Authentication();
$auth->logout();