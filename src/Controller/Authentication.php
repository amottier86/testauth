<?php 

namespace Auth\Controller;

use PDO;
use DateTime;
use Throwable;
use Auth\Entity\User;
use Auth\Entity\Session;
use Auth\Repository\UserRepository;
use Symfony\Component\Mailer\Mailer;
use Auth\Repository\SessionRepository;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

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

    private function isUserExist(User $user): bool
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

    private function createUser(User $user): bool
    {
        $userRepo = new UserRepository($this->pdo);
        $user->password = $this->hashPassword($user->password);
        return $userRepo->create($user);
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    private function createSession(?User $user): bool
    {
        if(!$user) {
            return false;
        }

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

    public function signOut(User $user): bool
    {
        if($this->isUserExist($user)) {
            return false;
        }
        
        if(!$this->createUser($user)) {
            return false;
        }
        
        if(!$this->createSession($user)) {
            return false;
        }

        header("Location: index.php");

        return true;
    }

    public function login($username, $password): bool
    {
        $user = null;

        if(!$user = $this->isValidUser($username, $password)) {
            return false;
        }

        $_SESSION['user'] = serialize($user);

        if((bool)$user->choose2fa) {
            header("Location: page2fa.php");
            return true;
        }

        if(!$this->createSession($user)) {
            return false;
        }

        header("Location: index.php");

        return true;
    }

    public function login2FA(): bool
    {
        $user = unserialize($_SESSION['user']);

        if(!$this->createSession($user)) {
            return false;
        }

        header("Location: index.php");

        return true;
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

            setcookie("session", "", time() - 1);
            setcookie("signature", "", time() - 1);
        }

        header("Location: login.php");

        die();
    }

    private function isValidUser(string $username, string $password): ?User
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
        $sessionId = $_COOKIE['session'] ?? "";
        $signatureId = $_COOKIE['signature'] ?? "";

        $sessionIdHashed = hash_hmac("sha256", $sessionId, $_ENV['SIGNATURE_SECRET']);

        if(hash_equals($sessionIdHashed, $signatureId)) {
            $sessionRepo = new SessionRepository($this->pdo);
            $session = $sessionRepo->getSessionById($sessionId);
            if($session) {
                return true;
            }
        }

        return false;
    }

    public function initUser(): void
    {
        $sessionId = $_COOKIE['session'] ?? "";
        $sessionRepo = new SessionRepository($this->pdo);
        $session = $sessionRepo->getSessionById($sessionId);
        if($session) {
            $_SESSION['username'] = $session->user->username;
            $_SESSION['userid'] = $session->user->id;
        }
    }

    public function getSessionsOfUserId(int $id): array
    {
        $userRepo = new UserRepository($this->pdo);
        $user = $userRepo->getUserById($id);

        $sessionRepo = new SessionRepository($this->pdo);
        return $sessionRepo->getSessionsByUser($user);
    }

    public function deleteSession(string $id): void
    {
        $sessionRepo = new SessionRepository($this->pdo);
        $session = $sessionRepo->getSessionById($id);
        if($session) {
            $sessionRepo->delete($session);
        }
    }

    public function send2FACode($code, $emailToUse): void
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $mailer = new Mailer($transport);

        $emailApp = "app@example.com";

        $email = new Email();
        $email
            ->from($emailApp)
            ->to($emailToUse)
            ->replyTo($emailApp)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('2FA Code')
            ->html("<p>Voici votre code : {$code}</p>");

        $mailer->send($email);
    }
}