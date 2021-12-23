<?php

namespace Auth\Repository;

use Auth\Entity\User;
use PDO;
use Throwable;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserById(int $id): ?User
    {
        try {
            $statment = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $statment->bindValue("id", $id);
            $statment->execute();
            if($user = $statment->fetch(PDO::FETCH_ASSOC)) {
                return new User($user['id'], $user['username'], $user['password'], $user['email']);
            }
            return null;
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function getUserByEmail(string $email): ?User
    {
        try {
            $statment = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $statment->bindValue("email", $email);
            $statment->execute();
            if($user = $statment->fetch(PDO::FETCH_ASSOC)) {
                return new User($user['id'], $user['username'], $user['password'], $user['email']);
            }
            return null;
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function getUserByUsername(string $username): ?User
    {
        try {
            $statment = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
            $statment->bindValue("username", $username);
            $statment->execute();
            if($user = $statment->fetch(PDO::FETCH_ASSOC)) {
                return new User($user['id'], $user['username'], $user['password'], $user['email']);
            }
            return null;
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function create(User $user): bool
    {
        $id = $user->id;
        $username = $user->username;
        $password = $user->password;
        $email = $user->email;

        try {
            $statment = $this->pdo->prepare("INSERT INTO users VALUES (:id, :username, :password, :email)");
            $statment->bindValue("id", $id);
            $statment->bindValue("username", $username);
            $statment->bindValue("password", $password);
            $statment->bindValue("email", $email);
            return $statment->execute();
        } catch (Throwable $th) {
            dd($th);
        }
    }
}