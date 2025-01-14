<?php

namespace App\Model;

use App\EnvLoader;

abstract class AbstractModel
{
  protected $pdo;

  public function __construct()
  {
    $env = new EnvLoader();
    $this->pdo = new \PDO(
      'mysql:host=' . $env->dbHost . ';dbname=' . $env->dbName,
      $env->dbUser,
      $env->dbPass,
    );
  }
}
