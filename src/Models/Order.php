<?php

namespace App\Models;

use App\Database;
use PDO;

class Order
{
    public static function create(int $productId, int $quantity, float $unitPrice, float $totalPrice): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO orders (product_id, quantity, unit_price, total_price)
             VALUES (:product_id, :quantity, :unit_price, :total_price)'
        );
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':unit_price', $unitPrice);
        $stmt->bindValue(':total_price', $totalPrice);
        $stmt->execute();

        return (int) Database::connection()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, product_id, quantity, unit_price, total_price, created_at
             FROM orders WHERE id = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch();
        return $order ?: null;
    }
}
