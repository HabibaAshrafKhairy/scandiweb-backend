<?php

namespace App\GraphQL\Resolvers;

use App\Model\Category;
use App\Model\Product;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryResolver
{
  public static function getType(): ObjectType
  {
    return new ObjectType([
      'name' => 'Query',
      'fields' => [
        'categories' => [
          'type' => Type::listOf(Type::string()),
          'resolve' => [self::class, 'resolveCategories'],
        ],
        'products' => [
          'type' => Type::listOf(self::getProductType()),
          'args' => [
            'categoryName' => ['type' => Type::string()],
          ],
          'resolve' => [self::class, 'resolveProducts'],
        ],
      ],
    ]);
  }

  private static function getProductType(): ObjectType
  {
    return new ObjectType([
      'name' => 'Product',
      'fields' => [
        'id' => Type::nonNull(Type::string()),
        'name' => Type::nonNull(Type::string()),
        'price' => Type::nonNull(Type::float()),
        'in_stock' => Type::nonNull(Type::boolean()),
        'description' => Type::string(),
        'category' => [
          'type' => Type::nonNull(Type::string()),
          'resolve' => function ($product) {
            $categoryModel = new Category();
            $category = $categoryModel->getById($product['category_id']);
            return $category['name'] ?? null;
          },
        ],
        'gallery' => [
          'type' => Type::listOf(Type::string()),
          'resolve' => function ($product) {
            // Decode the gallery JSON from the database (if it exists)
            return $product['gallery'] ? json_decode($product['gallery']) : [];
          },
        ],
        'attributes' => [
          'type' => Type::listOf(self::getAttributeType()),
        ],
      ],
    ]);
  }

  private static function getAttributeType(): ObjectType
  {
    return new ObjectType([
      'name' => 'Attribute',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'name' => Type::nonNull(Type::string()),
        'type' => Type::string(),
        'items' => Type::listOf(new ObjectType([
          'name' => 'AttributeItem',
          'fields' => [
            'id' => Type::nonNull(Type::int()),
            'value' => Type::nonNull(Type::string()),
            'displayValue' => Type::string(),
            'swatch' => Type::string(),
          ],
        ])),
      ],
    ]);
  }

  public static function resolveCategories(): array
  {
    $categoryModel = new Category();
    return $categoryModel->getAll();
  }

  public static function resolveProducts($root, $args): array
  {
    $productModel = new Product();
    return $productModel->getByCategoryName($args['categoryName'] ?? null);
  }
}
