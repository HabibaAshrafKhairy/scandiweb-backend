<?php

use App\EnvLoader;

$env = new EnvLoader();
// Database connection configuration
$host = $env->dbHost;
$db = $env->dbName;
$user = $env->dbUser;
$pass = $env->dbPass;
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);

  // Create the database if it doesn't exist
  $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
  $pdo->exec("USE $db");

  // Create categories table
  $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Create products table
  $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id VARCHAR(255) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        in_stock BOOLEAN NOT NULL,
        gallery JSON,
        category_id INT,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Create attributes table
  $pdo->exec("CREATE TABLE IF NOT EXISTS attributes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(50),
        product_id VARCHAR(255),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Create attribute_items table
  $pdo->exec("CREATE TABLE IF NOT EXISTS attribute_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        value VARCHAR(255) NOT NULL,
        display_value VARCHAR(255),
        swatch VARCHAR(255),
        attribute_id INT NOT NULL,
        FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Create orders table
  $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Create order_items table
  $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        selected_attributes JSON,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  echo "Database and tables created successfully.\n";
} catch (PDOException $e) {
  echo "Database setup failed: " . $e->getMessage() . "\n";
  exit;
}
