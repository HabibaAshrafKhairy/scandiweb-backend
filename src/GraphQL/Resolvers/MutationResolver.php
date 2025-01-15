<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Model\Order;
use Exception;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MutationResolver
{
    public static function getType() : ObjectType
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
                        'display_value' => Type::nonNull(Type::string()),
                    ],
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
                        'items' => Type::nonNull(Type::listOf($orderItemInputType)),
                    ],
                    'resolve' => [self::class, 'resolveCreateOrder'],
                ],
            ],
        ]);
    }

    /**
     * Method to resolve the placeOrder mutation.
     *
     * @param mixed $root
     * @param mixed $args
     * @return null|array
     */
    public static function resolveCreateOrder($root, $args)
    {
        try {
            $orderModel = new Order();
            return $orderModel->create($args);
        } catch (Exception $e) {
            echo 'Error in resolveCreateOrder: ' . $e->getMessage();
            return null;
        }
    }
}
