<?php

class Database
{
    private $host;
    private $user;
    private $password;
    private $name;

    public function __construct(
        string $host,
        string $user,
        string $password,
        string $name
    ){

        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->name = $name;

    }

public function getConnection(): PDO
{
    $dns = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
    return new PDO($dns, $this->user, $this->password);
}
}