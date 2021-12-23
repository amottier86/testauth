<?php

require(__DIR__ . "/header.php"); 

use Auth\Controller\Authentication;

$username = null;
$password = null;

$auth = new Authentication();
if(!$auth->isLoggedIn()) {
    $auth->logout();
}

$auth->initUser();

$username = $_SESSION['username'];

$openSessions = $auth->getSessionsOfUserId($_SESSION['userid']);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
</head>
<body>
    <?php require(__DIR__ . "/navbar.php"); ?>
    <h1>Bienvenue dans votre espace <span style="color: blue;"><?= $username ?></span> !</h1>
    <p>Mes sessions ouvertes :</p>
    <ul>
        <?php foreach($openSessions as $session): ?>
            <li>
                Connecté le <?= $session->createdAt->format("d/m/Y H:i"); ?>
                <a href="removeSession.php?id=<?= $session->id; ?>">Supprimer</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="logout.php">Déconnexion</a>
</body>
</html>