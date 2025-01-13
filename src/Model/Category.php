<?php

namespace App\Model;

use Throwable;

class Category extends AbstractModel
{
  public function getAll(): array
  {
    try {
      $stmt = $this->pdo->query('SELECT * FROM categories');
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      echo "Error in getAll: " . $e->getMessage();
      return [];
    }
  }

  public function getById(int $id): ?array
  {
    try {
      $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
      $stmt->execute(['id' => $id]);
      return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
      echo "Error in getById: " . $e->getMessage();
      return null;
    }
  }
}
