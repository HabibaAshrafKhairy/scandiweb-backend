<?php

namespace App\GraphQL\Resolvers;

use App\Model\Attribute as ModelAttribute;
use App\Model\Order;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;


class MutationResolver
{
  public static function getType(): ObjectType
  {
    $orderItemInputType = new InputObjectType([
      'name' => 'OrderItemInput',
      'fields' => [
        'product_id' => Type::nonNull(Type::string()),
        'quantity' => Type::nonNull(Type::int()),
        'price' => Type::nonNull(Type::float()),
        'selected_attribute_item_ids' => Type::nonNull(Type::listOf(Type::int())),
      ],
    ]);

    $orderItemType = new ObjectType([
      'id' => Type::nonNull(Type::int()),
      'name' => 'OrderItem',
      'fields' => [
        'product_id' => Type::nonNull(Type::string()),
        'quantity' => Type::nonNull(Type::int()),
        'price' => Type::nonNull(Type::float()),
        'selected_attributes' => Type::listOf(new ObjectType([
          'name' => 'SelectedAttribute',
          'fields' => [
            'name' => Type::nonNull(Type::string()),
            'value' => Type::nonNull(Type::string()),
          ],
          'args' => [
            'orderItem' => ['type' => $orderItemInputType],
          ],
          'resolve' => [self::class, 'resolveSelectedAttributes'],
        ])),
      ],
    ]);

    $orderType = new ObjectType([
      'name' => 'Order',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'total' => Type::nonNull(Type::float()),
        'created_at' => Type::string(),
        'items' => Type::listOf($orderItemType),
      ],
    ]);


    // Define Mutation type with placeOrder
    return new ObjectType([
      'name' => 'Mutation',
      'fields' => [
        'placeOrder' => [
          'type' => Type::nonNull($orderType),
          'args' => [
            'items' => Type::nonNull(Type::listOf($orderItemInputType))
          ],
          'resolve' => function ($root, $args) {
            $orderModel = new Order();
            return $orderModel->create($args);
          },
        ],
      ],
    ]);
  }



  public static function resolveSelectedAttributes($root, $args)
  {
    try {
      $attributeModel = new ModelAttribute();
      $res = $attributeModel->getByOrderItem($args['orderItem']['selected_attribute_item_ids']);
      return $res;
    } catch (\Exception $e) {
      echo "Error in resolveSelectedAttributes: " . $e->getMessage();
      return null;
    }
  }
}
