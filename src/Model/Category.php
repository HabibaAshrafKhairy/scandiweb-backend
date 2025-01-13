<?php

namespace App\Model;

class Category extends AbstractModel
{
  public function getAll(): array
  {
    $stmt = $this->pdo->query('SELECT * FROM categories');
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getById(int $id): ?array
  {
    $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
  }
}
