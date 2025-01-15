<?php

declare(strict_types=1);

namespace App\Model;

use PDO;
use Throwable;

use function array_fill;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function json_decode;
use function json_encode;

class Order extends AbstractModel
{
    public function create(array $args) : ?array
    {
        try {
            // Step 1: Calculate order total
            $total = array_reduce($args['items'], static function ($carry, $item) {
                return $carry + ($item['quantity'] * $item['price']);
            }, 0);

            // Step 2: Insert the order and get the order ID
            $stmt = $this->pdo->prepare('INSERT INTO orders (total) VALUES (:total)');
            $stmt->execute(['total' => $total]);
            $orderId = $this->pdo->lastInsertId();

            // Step 3: Prepare batch insert for order items
            $attributeIds = [];
            $values = [];
            $placeholders = [];
            foreach ($args['items'] as $item) {
                // If there are no attributes, set selected_attributes as empty JSON
                $selectedAttributes = empty($item['selected_attribute_item_ids'])
                    ? '[]'
                    : json_encode($item['selected_attribute_item_ids']);

                // If the item has attributes, merge them into the attributeIds array
                if (! empty($item['selected_attribute_item_ids'])) {
                    $attributeIds = array_merge($attributeIds, $item['selected_attribute_item_ids']);
                }

                // Add the item to the batch insert values
                $values = array_merge($values, [
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $selectedAttributes,
                ]);
                $placeholders[] = '(?, ?, ?, ?, ?)';
            }

            $sql = '
            INSERT INTO order_items (order_id, product_id, quantity, price, selected_attributes)
            VALUES ' . implode(',', $placeholders);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            // Step 4: Fetch all required attributes in one query if there are any
            $attributesMap = [];
            if (! empty($attributeIds)) {
                $attributeIds = array_values(array_unique($attributeIds));
                $placeholders = implode(',', array_fill(0, count($attributeIds), '?'));
                $stmt = $this->pdo->prepare('
              SELECT ai.id, ai.display_value AS display_value, a.name, ai.value
              FROM attribute_items ai
              JOIN attributes a ON ai.attribute_id = a.id
              WHERE ai.id IN (' . $placeholders . ')
          ');
                $stmt->execute($attributeIds);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $attribute) {
                    $attributesMap[$attribute['id']] = [
                        'value' => $attribute['value'],
                        'display_value' => $attribute['display_value'],
                        'name' => $attribute['name'],
                    ];
                }
            }

            // Step 5: Fetch the order and items
            $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = :id');
            $stmt->execute(['id' => $orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
            $stmt->execute(['order_id' => $orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Step 6: Resolve attributes for each item
            foreach ($items as &$item) {
                $selectedAttributeIds = json_decode($item['selected_attributes'], true);

                // If there are attributes, resolve them
                if (! empty($selectedAttributeIds)) {
                    $item['selected_attributes'] = array_map(static function ($id) use ($attributesMap) {
                        return $attributesMap[$id] ?? ['display_value' => 'Unknown', 'name' => 'Unknown'];
                    }, $selectedAttributeIds);
                } else {
                    // If no attributes, set it to empty array or some default value
                    $item['selected_attributes'] = [];
                }
            }
            $order['items'] = $items;

            return $order;
        } catch (Throwable $e) {
            echo 'Error in create: ' . $e->getMessage();
            echo 'Error in create: ' . $e->getTraceAsString();
            return null;
        }
    }
}
