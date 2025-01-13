<?php

namespace App\Model;

use PDO;
use Throwable;

class Order extends AbstractModel
{
  public function create(array $args): ?array
  {
    try {
      // Calculate order Total
      $total = array_reduce($args['items'], function ($carry, $item) {
        return $carry + ($item['quantity'] * $item['price']);
      }, 0);

      // Create the order, insert Total, get the newly created order ID
      $stmt = $this->pdo->prepare('INSERT INTO orders (total) VALUES (:total)');
      $stmt->execute(['total' => $total]);
      $orderId = $this->pdo->lastInsertId();

      // Prepare inserting the order items
      $stmt = $this->pdo->prepare('
INSERT INTO order_items (order_id, product_id, quantity, price, selected_attributes)
VALUES (:order_id, :product_id, :quantity, :price, :selected_attributes)
');
      foreach ($args['items'] as $item) {
        $selectedAttributes = json_encode($item['selected_attribute_item_ids']);
        $stmt->execute([
          'order_id' => $orderId,
          'product_id' => $item['product_id'],
          'quantity' => $item['quantity'],
          'price' => $item['price'],
          'selected_attributes' => $selectedAttributes,
        ]);
      }

      // Step 4: Fetch and return the full order
      $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = :id');
      $stmt->execute(['id' => $orderId]);
      $order = $stmt->fetch();

      // Fetch items for the order
      $stmt = $this->pdo->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
      // $stmt = $this->pdo->prepare('SELECT *, a.* FROM order_items oi INNER JOIN attribute_items a ON JSON_CONTAINS (oi.selected_attributes, JSON_ARRAY (a.id)) WHERE order_id = :order_id');
      $stmt->execute(['order_id' => $orderId]);
      $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Resolve selected_attributes for each item
      foreach ($items as &$item) {
        $selectedAttributeIds = json_decode($item['selected_attributes'], true);

        // Log selected attribute IDs

        if (empty($selectedAttributeIds)) {
          $item['selected_attributes'] = [];
          // If there are no selected attribute IDs, break
          continue;
        }


        $stmt = $this->pdo->prepare('
          SELECT ai.value, a.name
          FROM attribute_items ai
          JOIN attributes a ON ai.attribute_id = a.id
          WHERE ai.id IN (' . implode(',', array_map('intval', $selectedAttributeIds)) . ')
      ');
        $stmt->execute();
        $item['selected_attributes'] = $stmt->fetchAll();
      }
      $order['items'] = $items;

      // print_r('in order model 2' . json_encode($order));

      // Log the final order response

      return $order;
    } catch (Throwable $e) {
      echo "Error in getById: " . $e->getMessage();
      return null;
    }
  }
}
