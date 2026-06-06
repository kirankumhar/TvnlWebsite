<?php

class Database
{
    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private PDO $pdo;

    public function __construct()
    {
        // Detect environment
        $isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

        if ($isLocal) {
            // LOCAL
            $this->host     = 'localhost';
            $this->dbname   = 'cdgps_db';
            $this->username = 'root';
            $this->password = '';
        } else {
            // PRODUCTION
            $this->host     = 'localhost';
            $this->dbname   = 'domains_cgst';
            $this->username = 'domains_cgst';
            $this->password = '24!JXcN^ny8yjodd';
        }

        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

            $this->pdo = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

        } catch (PDOException $e) {
            error_log($e->getMessage());
            die('Database connection failed.');
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
