<?php

namespace App\Model;

class Order extends AbstractModel
{
  public function create(array $items): string
  {
    $total = array_reduce($items, fn($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0);

    $stmt = $this->pdo->prepare('INSERT INTO orders (total) VALUES (:total)');
    $stmt->execute(['total' => $total]);
    $orderId = $this->pdo->lastInsertId();

    foreach ($items as $item) {
      $stmt = $this->pdo->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)
            ');
      $stmt->execute([
        'order_id' => $orderId,
        'product_id' => $item['product_id'],
        'quantity' => $item['quantity'],
        'price' => $item['price']
      ]);
    }

    return "Order ID: $orderId created successfully.";
  }
}
