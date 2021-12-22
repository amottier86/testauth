<?php

use Auth\Authentication;

require(__DIR__ . "/header.php"); 

$username = null;
$password = null;
$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = filter_input_array(INPUT_POST, [
        "username" => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        "password" => FILTER_DEFAULT
    ]);

    $username = $input['username'];
    $password = $input['password'];

    if(!$username || !$password) {
        $error = "Veuillez tout compléter";
    } else {
        $auth = new Authentication();
        if($user = $auth->isValidUser($username, $password)) {
            if($auth->createSession($user)) {
                header("Location: index.php");
            }
        } else {
            $error = "Utilisateur inconnu !";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
</head>
<body>
    <?php require(__DIR__ . "/navbar.php"); ?>
    <h1>Connectez-vous</h1>
    <form action="?" method="post">
        <div>
            <label for="username">Username :</label>
            <input type="text" name="username" value="<?= $username; ?>">
        </div>
        <div>
            <label for="password">Password :</label>
            <input type="password" name="password" value="<?= $password; ?>">
        </div>
        <?php if($error): ?>
            <span style="color:red;">
                <?= $error; ?>
            </span>
        <?php endif; ?>
        <div>
            <button type="submit">Connexion</button>
        </div>
    </form>
</body>
</html>