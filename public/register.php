<?php

use Auth\Controller\Authentication;
use Auth\Entity\User;

require(__DIR__ . "/header.php"); 

$username = null;
$password = null;
$email = null;
$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = filter_input_array(INPUT_POST, [
        "username" => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        "password" => FILTER_DEFAULT,
        "email" => FILTER_SANITIZE_EMAIL
    ]);

    $username = $input['username'];
    $password = $input['password'];
    $email = $input['email'];
    $id = time();

    if(!$username || !$password || !$email) {
        $error = "Veuillez tout compléter";
    } else {
        $user = new User($id, $username, $password, $email, false);
        $auth = new Authentication();
        if(!$auth->signOut($user)) {
            $error = "Registration failed !";
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
    <title>Register page</title>
</head>
<body>
    <?php require(__DIR__ . "/navbar.php"); ?>
    <h1>Enregistrez-vous</h1>
    <form action="?" method="post">
        <div>
            <label for="username">Username :</label>
            <input type="text" name="username" value="<?= $username; ?>">
        </div>
        <div>
            <label for="password">Password :</label>
            <input type="password" name="password" value="<?= $password; ?>">
        </div>
        <div>
            <label for="password">E-mail :</label>
            <input type="email" name="email" value="<?= $email; ?>">
        </div>
        <?php if($error): ?>
            <span style="color:red;">
                <?= $error; ?>
            </span>
        <?php endif; ?>
        <div>
            <button type="submit">Enregistrer</button>
        </div>
    </form>
</body>
</html>