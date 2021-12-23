<?php 

namespace Auth\Entity;

class User
{
    private int $id;
    private string $username;
    private string $password;
    private string $email;
    private bool $choose2fa;

    public function __construct(int $id, string $username, string $password, string $email, $choose2fa) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->choose2fa = $choose2fa;
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
        return  "user : {$this->id} {$this->username} {$this->password} {$this->email} {$this->choose2fa}" . PHP_EOL;
    }
}