<?php

namespace App\GraphQL\Resolvers;

use App\Model\Order;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MutationResolver
{
  public static function getType(): ObjectType
  {
    return new ObjectType([
      'name' => 'Mutation',
      'fields' => [
        'placeOrder' => [
          'type' => Type::string(),
          'args' => [
            'items' => ['type' => Type::listOf(Type::string())],
          ],
          'resolve' => [self::class, 'resolvePlaceOrder'],
        ],
      ],
    ]);
  }

  public static function resolvePlaceOrder($root, $args): string
  {
    $orderModel = new Order();
    return $orderModel->create($args['items']);
  }
}
