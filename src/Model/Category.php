<?php

namespace App\Model;

class Category extends AbstractModel
{
  public function getAll(): array
  {
    $stmt = $this->pdo->query('SELECT * FROM categories');
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
}
