<?php 

namespace Auth\Repository;

use Auth\Entity\Session;
use Auth\Entity\User;
use DateTime;
use PDO;
use Throwable;

class SessionRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSessionById(string $id): ?Session
    {
        try {
            $userRepo = new UserRepository($this->pdo);

            $statment = $this->pdo->prepare("SELECT * FROM sessions WHERE id = :id");
            $statment->bindValue("id", $id);
            $statment->execute();
            if($session = $statment->fetch(PDO::FETCH_ASSOC)) {
                $userId = $session['user_id'];
                $user = $userRepo->getUserById($userId);
                if($user) {
                    $createdAt = DateTime::createFromFormat("Y-m-d H:i:s.u", $session['created_at']);
                    return new Session($session['id'], $user, $createdAt);
                }
            }
            return null;
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function getSessionsByUser(User $user): array
    {
        try {
            $liste = [];
            $userId = $user->id;
            $statment = $this->pdo->prepare("SELECT * FROM sessions WHERE user_id = :userid ORDER BY created_at DESC");
            $statment->bindValue("userid", $userId);
            $statment->execute();
            if($results = $statment->fetchAll(PDO::FETCH_ASSOC)) {
                foreach($results as $session) {
                    $createdAt = DateTime::createFromFormat("Y-m-d H:i:s.u", $session['created_at']);
                    $liste[] = new Session($session['id'], $user, $createdAt);
                }
            }
            return $liste;
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function create(Session $session): bool
    {
        try {
            $sessionId = $session->id;
            $userId = $session->user->id;
            $createdAt = $session->createdAt->format("d/m/Y H:i:s.u");
            $statment = $this->pdo->prepare("INSERT INTO sessions VALUES (:id, :user_id, :created_at)");
            $statment->bindValue("id", $sessionId);
            $statment->bindValue("user_id", $userId);
            $statment->bindValue("created_at", $createdAt);
            return $statment->execute();
        } catch (Throwable $th) {
            dd($th);
        }
    }

    public function delete(Session $session): bool
    {
        try {
            $id = $session->id;
            $statment = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
            $statment->bindValue("id", $id);
            return $statment->execute();
        } catch (Throwable $th) {
            dd($th);
        }
    }
}