<?php 

namespace Auth;

class User
{
    public function __construct(private int $id, private string $username, private string $password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
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
        return $this->id . ' ' . $this->username . ' ' . $this->password . PHP_EOL;
    }
}