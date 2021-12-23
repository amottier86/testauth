<?php

use Auth\Controller\Authentication;

require(__DIR__ . "/header.php"); 

$error = null;

$auth = new Authentication();

if($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(isset($_SESSION['user']) ) {
        $user = unserialize($_SESSION['user']);
        $_SESSION['code2FA'] = random_int(100, 999);
        $auth->send2FACode($_SESSION['code2FA'], $user->email);
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_SESSION['code2FA'];

    $codeInput = filter_input(INPUT_POST, "code", FILTER_SANITIZE_NUMBER_INT);

    if((int)$codeInput === $code) {
        if(!$auth->login2FA()) {
            header("Location: index.php");
            die();
        }
    } else {
        $error = "Error code, retry !";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2fa</title>
</head>
<body>
    <h1>Code to approve</h1>
    <form action="?" method="post">
        <div>
            <label for="code">Code :</label>
            <input type="text" name="code" value="">
        </div>
        <?php if($error): ?>
            <span style="color:red;">
                <?= $error; ?>
            </span>
        <?php endif; ?>
        <div>
            <button type="submit">Valider</button>
        </div>
    </form>
</body>
</html>