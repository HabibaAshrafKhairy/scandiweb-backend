<?php

require_once '../EnvLoader.php';

use App\EnvLoader;

$env = new EnvLoader();
// Database connection configuration
$host = $env->dbHost;
$db = $env->dbName;
$user = $env->dbUser;
$pass = $env->dbPass;
$charset = 'utf8mb4';


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Prevent re-running if data already inserted
if (file_exists('data_inserted.txt')) {
    die('Data has already been inserted. Remove "data_inserted.txt" to run again.');
}

// Load the JSON file
$jsonData = file_get_contents('data.json');
$data = json_decode($jsonData, true);

echo "Inserting categories...\n";

// Insert categories
$categories = $data['data']['categories'];
foreach ($categories as $category) {
    // Check if the category exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
    $stmt->execute(['name' => $category['name']]);
    if ($stmt->fetchColumn()) {
        echo "Skipping duplicate category: {$category['name']}\n";
        continue;
    }

    // Insert category
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->execute(['name' => $category['name']]);
}

// Map categories to their IDs for product insertion
$categoryMap = [];
$stmt = $pdo->query("SELECT id, name FROM categories");
while ($row = $stmt->fetch()) {
    $categoryMap[$row['name']] = $row['id'];
}

echo "Inserting products...\n";

// Insert products
$products = $data['data']['products'];
foreach ($products as $product) {
    // Check if the product already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id = :id");
    $stmt->execute(['id' => $product['id']]);
    if ($stmt->fetchColumn()) {
        echo "Skipping duplicate product: {$product['id']}\n";
        continue;
    }

    // Insert product
    $stmt = $pdo->prepare("
        INSERT INTO products (id, name, in_stock, description, category_id, price, gallery)
        VALUES (:id, :name, :in_stock, :description, :category_id, :price, :gallery)
    ");
    $galleryJson = json_encode($product['gallery']);
    $price = $product['prices'][0]['amount'];
    $stmt->execute([
        'id' => $product['id'],
        'name' => $product['name'],
        'in_stock' => isset($product['inStock']) && $product['inStock'] ? 1 : 0,
        'description' => $product['description'],
        'category_id' => $categoryMap[$product['category']],
        'price' => $price,
        'gallery' => $galleryJson,
    ]);

    echo "Inserted product: {$product['name']}\n";

    // Insert attributes
    foreach ($product['attributes'] as $attribute) {
        // Insert attribute
        $stmt = $pdo->prepare("
            INSERT INTO attributes (product_id, name, type)
            VALUES (:product_id, :name, :type)
        ");
        $stmt->execute([
            'product_id' => $product['id'],
            'name' => $attribute['name'],
            'type' => $attribute['type'],
        ]);

        $attributeId = $pdo->lastInsertId();

        // Insert attribute items
        foreach ($attribute['items'] as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO attribute_items (attribute_id, display_value, value)
                VALUES (:attribute_id, :display_value, :value)
            ");
            $stmt->execute([
                'attribute_id' => $attributeId,
                'display_value' => $item['displayValue'],
                'value' => $item['value'],
            ]);
        }
    }
}

// Create a flag file to mark successful insertion
file_put_contents('data_inserted.txt', 'Data inserted successfully.');

echo "Data insertion complete.\n";
