<?php 

namespace Auth\Entity;

use Auth\Entity\User;
use DateTime;

class Session
{
    private string $id;
    private User $user;
    private DateTime $createdAt;

    public function __construct(string $id, User $user, DateTime $createdAt)
    {
        $this->id = $id;
        $this->user = $user;
        $this->createdAt = $createdAt;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __toString(): string
    {
        return "session : {$this->id} - user : {$this->user->id}" . PHP_EOL;
    }
}