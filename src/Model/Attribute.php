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

  public function getByOrderItem(array $orderItem): array
  {
    error_log('Resolving selected_attributes for order item: ' . json_encode($orderItem));  // Log this to check if it's triggered


    // Assuming $selectedAttributeIds is the string "[23,25,27]"
    $selectedAttributeIds = json_decode($orderItem['selected_attributes'], true);

    // Check the decoded result
    error_log('Decoded selected attribute IDs: ' . json_encode($selectedAttributeIds));


    // Check if the array is not empty before proceeding
    if (empty($selectedAttributeIds)) {
      error_log('No selected attributes found for order item: ' . json_encode($orderItem));
      return [];
    }



    // Fetch attribute items based on their IDs
    $sql = '
SELECT ai.value, a.name
FROM attribute_items ai
JOIN attributes a ON ai.attribute_id = a.id
WHERE ai.id IN (' . implode(',', array_map('intval', $selectedAttributeIds)) . ')
';
    error_log('Executing query: ' . $sql);

    $stmt = $$this->pdo->prepare($sql);
    if (!$stmt->execute()) {
      error_log('Failed to execute query: ' . implode(' ', $stmt->errorInfo()));
    }

    $stmt->execute();
    $result = $stmt->fetchAll();
    error_log('Fetched selected attributes: ' . json_encode($result)); // Log query result
    if (empty($result)) {
      error_log('No selected attributes found for IDs: ' . json_encode($selectedAttributeIds));
    }


    return json_encode($result);
  }
}
