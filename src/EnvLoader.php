<?php

namespace App;

class EnvLoader
{
  public readonly string $dbHost;
  public readonly string $dbName;
  public readonly string $dbUser;
  public readonly string $dbPass;

  public function __construct()
  {
    $this->dbHost = $_ENV['DB_HOST'] ?? 'db';
    $this->dbName = $_ENV['DB_NAME'] ?? 'scandiweb';
    $this->dbUser = $_ENV['DB_USER'] ?? 'root';
    $this->dbPass = $_ENV['DB_PASS'] ?? 'yourpassword';
  }
}
