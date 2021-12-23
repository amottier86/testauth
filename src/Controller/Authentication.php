<?php 

namespace Auth\Controller;

use Auth\Entity\Session;
use Auth\Entity\User;
use Auth\Repository\SessionRepository;
use Auth\Repository\UserRepository;
use DateTime;
use PDO;
use Throwable;

class Authentication
{
    private $pdo;

    public function __construct() {
        $this->initDB();
    }

    private function initDB(): void
    {
        try {
            $this->pdo = new PDO('pgsql:host='.$_ENV['SERVER'].';dbname='.$_ENV['DATABASE'], $_ENV['USER'], $_ENV['PASSWORD'], [ 
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
            ]);
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function isUserExist(User $user): bool
    {
        $username = $user->username;
        $email = $user->email;

        $userRepo = new UserRepository($this->pdo);

        if(
            $userRepo->getUserByUsername($username) || 
            $userRepo->getUserByEmail($email)
        ) {
            return true;
        }

        return false;
    }

    public function createUser(User $user): bool
    {
        $userRepo = new UserRepository($this->pdo);
        $user->password = $this->hashPassword($user->password);
        return $userRepo->create($user);
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public function createSession(User $user): bool
    {
        // Session
        $idSession = bin2hex(random_bytes(32));
        $session = new Session($idSession, $user, new DateTime());

        // Signature
        $signatureId = hash_hmac("sha256", $idSession, $_ENV['SIGNATURE_SECRET']);

        $sessionRepo = new SessionRepository($this->pdo);
        if($sessionRepo->create($session)) {
            setcookie("session", $idSession, time() + 60 * 60 * 24 * 2, "", "", false, true);
            setcookie("signature", $signatureId, time() + 60 * 60 * 24 * 2, "", "", false, true);
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        // On détruit les cookies
        $sessionId = $_COOKIE['session'] ?? null;
        $signatureId = $_COOKIE['signature'] ?? null;

        // On détruit la session
        if($sessionId && $signatureId) {
            $sessionRepo = new SessionRepository($this->pdo);
            $session = $sessionRepo->getSessionById($sessionId);
            if($session) {
                $sessionRepo->delete($session);
                unset($_SESSION);
                session_destroy();
            }

            setcookie("session", null, time() - 1);
            setcookie("signature", null, time() - 1);
        }

        

        header("Location: login.php");
    }

    public function isValidUser(string $username, string $password): ?User
    {
        $userRepo = new UserRepository($this->pdo);
        $user = $userRepo->getUserByUsername($username);
        if($user) {
            $passwordUser = $user->password;
            if(password_verify($password, $passwordUser)) {
                return $user;
            } 
        }

        return null;
    }

    public function isLoggedIn(): bool
    {
        $sessionId = $_COOKIE['session'];
        $signatureId = $_COOKIE['signature'];

        $sessionIdHashed = hash_hmac("sha256", $sessionId, $_ENV['SIGNATURE_SECRET']);

        if(hash_equals($sessionIdHashed, $signatureId)) {
            return true;
        } else {
            $this->logout();
        }

        return false;
    }

    public function initUser(): void
    {
        $sessionRepo = new SessionRepository($this->pdo);
        $session = $sessionRepo->getSessionById($_COOKIE['session']);
        if($session) {
            $_SESSION['username'] = $session->user->username;
        }
    }
}