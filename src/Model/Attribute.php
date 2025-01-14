<?php

namespace App\Model;

class Attribute extends AbstractModel
{
  public function getByProductId(string $productId): array
  {
    $stmt = $this->pdo->prepare('
            SELECT a.id AS attribute_id, a.name AS attribute_name, a.type AS attribute_type, ai.id AS item_id, ai.value AS item_value, ai.display_value AS item_display_value, ai.swatch AS item_swatch
            FROM attributes a
            JOIN attribute_items ai ON a.id = ai.attribute_id
            WHERE a.product_id = :productId
        ');
    $stmt->execute(['productId' => $productId]);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Organize attributes and their items into a structured array
    $attributes = [];
    foreach ($rows as $row) {
      $attributeId = $row['attribute_id'];
      if (!isset($attributes[$attributeId])) {
        $attributes[$attributeId] = [
          'id' => $row['attribute_id'],
          'name' => $row['attribute_name'],
          'type' => $row['attribute_type'],
          'items' => [],
        ];
      }

      $attributes[$attributeId]['items'][] = [
        'id' => $row['item_id'],
        'value' => $row['item_value'],
        'displayValue' => $row['item_display_value'],
        'swatch' => $row['item_swatch'],
      ];
    }

    return array_values($attributes);
  }
}
