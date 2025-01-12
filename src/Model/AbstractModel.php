<?php

namespace App\Model;

abstract class AbstractModel
{
  protected $pdo;

  public function __construct()
  {
    $this->pdo = new \PDO(
      'mysql:host=db;dbname=scandiweb',
      'root',
      'yourpassword'
    );
  }
}
