<?php

namespace App\Model;

use App\Model\Attribute;
use Throwable;

class Product extends AbstractModel
{
  public function getByCategoryName(?string $categoryName): array
  {
    try {
      $query = 'SELECT p.* FROM products p JOIN categories c ON p.category_id = c.id';
      $params = [];

      if ($categoryName) {
        $query .= ' WHERE c.name = :categoryName';
        $params['categoryName'] = $categoryName;
      }

      $stmt = $this->pdo->prepare($query);
      $stmt->execute($params);
      $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Fetch attributes for each product
      $attributeModel = new Attribute();
      foreach ($products as &$product) {
        $product['attributes'] = $attributeModel->getByProductId($product['id']);
      }

      return $products;
    } catch (Throwable $e) {
      echo "Error in getByCategoryName: " . $e->getMessage();
      return [];
    }
  }

  public function getById(string $id): array
  {
    try {
      $query = 'SELECT p.* FROM products p WHERE p.id = :id';
      $stmt = $this->pdo->prepare($query);
      $stmt->execute(['id' => $id]);
      $product = $stmt->fetch(\PDO::FETCH_ASSOC);

      if ($product) {
        // Fetch attributes for the product
        $attributeModel = new Attribute();
        $product['attributes'] = $attributeModel->getByProductId($product['id']);
      }

      return $product;
    } catch (Throwable $e) {
      echo "Error in getById: " . $e->getMessage();
      return [];
    }
  }
}
