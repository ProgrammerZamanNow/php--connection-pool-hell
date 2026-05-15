<?php

namespace App\Models;

use App\Database;
use PDO;

class Product
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, name, price, stock FROM products WHERE id = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch();
        return $product ?: null;
    }

    public static function decreaseStock(int $id, int $quantity): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE products SET stock = stock - :qty_sub WHERE id = :id AND stock >= :qty_check'
        );
        $stmt->bindValue(':qty_sub', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':qty_check', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
