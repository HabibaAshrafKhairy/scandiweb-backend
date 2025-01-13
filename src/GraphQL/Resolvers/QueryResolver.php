<?php

namespace App\GraphQL\Resolvers;

use App\Model\Category;
use App\Model\Product;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryResolver
{
  private static $productType = null;

  public static function getType(): ObjectType
  {
    try {
      return new ObjectType([
        'name' => 'Query',
        'fields' => [
          'categories' => [
            'type' => Type::listOf(new ObjectType([
              'name' => 'Category',
              'fields' => [
                'id' => Type::nonNull(Type::int()),
                'name' => Type::nonNull(Type::string()),
              ],
            ])),
            'resolve' => function () {
              try {
                $categoryModel = new \App\Model\Category();
                return $categoryModel->getAll();
              } catch (\Exception $e) {
                echo "Error in categories resolve: " . $e->getMessage();
                return [];
              }
            },
          ],

          'products' => [
            'type' => Type::listOf(self::getProductType()),
            'args' => [
              'categoryName' => ['type' => Type::string()],
            ],
            'resolve' => [self::class, 'resolveProducts'],
          ],

          'product' => [
            'type' => self::getProductType(),
            'args' => [
              'id' => ['type' => Type::nonNull(Type::string())],
            ],
            'resolve' => [self::class, 'resolveProductById'],
          ]
        ],
      ]);
    } catch (\Exception $e) {
      echo "Error in getType: " . $e->getMessage();
      return new ObjectType([
        'name' => 'Query',
        'fields' => [],
      ]);
    }
  }

  private static function getProductType(): ObjectType
  {
    if (self::$productType === null) {
      try {
        self::$productType = new ObjectType([
          'name' => 'Product',
          'fields' => [
            'id' => Type::nonNull(Type::string()),
            'name' => Type::nonNull(Type::string()),
            'price' => Type::nonNull(Type::float()),
            'in_stock' => Type::nonNull(Type::boolean()),
            'description' => Type::string(),
            'category' => [
              'type' => new ObjectType([
                'name' => 'CategoryDetails',
                'fields' => [
                  'id' => Type::nonNull(Type::int()),
                  'name' => Type::nonNull(Type::string()),
                ],
              ]),
              'resolve' => function ($product) {
                try {
                  $categoryModel = new Category();
                  $category = $categoryModel->getById($product['category_id']);
                  return [
                    'id' => $category['id'],
                    'name' => $category['name'],
                  ];
                } catch (\Exception $e) {
                  echo "Error in category resolve: " . $e->getMessage();
                  return [];
                }
              },
            ],
            'gallery' => [
              'type' => Type::listOf(Type::string()),
              'resolve' => function ($product) {
                try {
                  // Decode the gallery JSON from the database (if it exists)
                  return $product['gallery'] ? json_decode($product['gallery']) : [];
                } catch (\Exception $e) {
                  echo "Error in gallery resolve: " . $e->getMessage();
                  return [];
                }
              },
            ],
            'attributes' => [
              'type' => Type::listOf(self::getAttributeType()),
            ],
          ],
        ]);
      } catch (\Exception $e) {
        echo "Error in getProductType: " . $e->getMessage();
        return new ObjectType([
          'name' => 'Product',
          'fields' => [],
        ]);
      }
    }
    return self::$productType;
  }

  private static function getAttributeType(): ObjectType
  {
    try {
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
    } catch (\Exception $e) {
      echo "Error in getAttributeType: " . $e->getMessage();
      return new ObjectType([
        'name' => 'Attribute',
        'fields' => [],
      ]);
    }
  }

  public static function resolveCategories(): array
  {
    try {
      $categoryModel = new Category();
      return $categoryModel->getAll();
    } catch (\Exception $e) {
      echo "Error in resolveCategories: " . $e->getMessage();
      return [];
    }
  }

  public static function resolveProducts($root, $args): array
  {
    try {
      $productModel = new Product();
      return $productModel->getByCategoryName($args['categoryName'] ?? null);
    } catch (\Exception $e) {
      echo "Error in resolveProducts: " . $e->getMessage();
      return [];
    }
  }

  public static function resolveProductById($root, $args)
  {
    try {
      $productModel = new Product();
      $res = $productModel->getById($args['id']);
      return $res;
    } catch (\Exception $e) {
      echo "Error in resolveProductById: " . $e->getMessage();
      return null;
    }
  }
}
