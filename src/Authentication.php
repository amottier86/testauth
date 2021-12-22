<?php 

namespace Auth;

use PDO;
use Throwable;
use Auth\User;

class Authentication
{
    const HASH = PASSWORD_ARGON2I;
    const SECRET = "i9u49fuzhvn4u9h9c4zhc94zhcn94izhfc94izfd83utgsb763rf5t3dvx";
    private $pdo;

    public function __construct() {
        $this->initDB();
    }

    public function initDB(): void
    {
        try {
            $this->pdo = new PDO('pgsql:host='.$_ENV['SERVER'].';dbname='.$_ENV['DATABASE'], $_ENV['USER'], $_ENV['PASSWORD'], [ 
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
            ]);
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function createUser(User $user): void
    {
        try {    
            $id = $user->id;
            $username = $user->username;
            $password = password_hash($user->password, self::HASH);

            $statment = $this->pdo->prepare("INSERT INTO users VALUES(:id, :username, :pass)");
            $statment->bindValue("id", $id);
            $statment->bindValue("username", $username);
            $statment->bindValue("pass", $password);
            $statment->execute();
        } catch(Throwable $th) {
            dd($th);
        }
    }

    public function get(string $username): bool
    {
        try {
            $statment = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $statment->bindValue("username", $username);
            $statment->execute();
            $user = $statment->fetch(PDO::FETCH_ASSOC);
            $userExist = $user ? true : false;
            return $userExist;
        } catch(Throwable $th) {
            dd($th);
        }
    }

    public function isUserAlreadyExist(string $username): bool
    {
        try {
            $statment = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $statment->bindValue("username", $username);
            $statment->execute();
            $user = $statment->fetch(PDO::FETCH_ASSOC);
            $userExist = $user ? true : false;
            return $userExist;
        } catch(Throwable $th) {
            dd($th);
        }
    }

    public function isValidUser(string $username, string $password): ?User
    {
        try {
            $statment = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $statment->bindValue("username", $username);
            $statment->execute();
            $datas = $statment->fetch(PDO::FETCH_ASSOC);

            if($datas) {
                $user = new User($datas['id'], $datas['username'], $datas['password']);

                $passwordUser = $datas['password'];
                if(password_verify($password, $passwordUser)) {
                    return $user;
                } 
            }
            return null;
        } catch(Throwable $th) {
            dd($th);
        }
    }

    public function createSession(User $user): bool
    {
        try {
            $id = bin2hex(random_bytes(32));
            $user_id = $user->id;
            $statment = $this->pdo->prepare("INSERT INTO sessions VALUES(:id, :userid)");
            $statment->bindValue("id", $id);
            $statment->bindValue("userid", $user_id);
            $statment->execute();

            $signature = hash_hmac("sha256", $id, self::SECRET);

            setcookie("signature", $signature, time() + 60 * 60 * 24 * 2, "", "", false, true);
            setcookie("session", $id, time() + 60 * 60 * 24 * 2, "", "", false, true);

            return true;
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function deleteSession(string $id): bool
    {
        try {
            $statment = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
            $statment->bindValue("id", $id);
            $statment->execute();
            return true;
        } catch(Throwable $th) {
            dd($th);
        }
    }
}