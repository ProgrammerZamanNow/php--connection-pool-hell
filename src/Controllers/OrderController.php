<?php

namespace App\Controllers;

use App\Database;
use App\Http\JsonResponse;
use App\Models\Order;
use App\Models\Product;
use Throwable;

class OrderController
{
    public function store(array $payload): void
    {
        $productId = isset($payload['product_id']) ? (int) $payload['product_id'] : 0;
        $quantity  = isset($payload['quantity']) ? (int) $payload['quantity'] : 0;

        if ($productId <= 0 || $quantity <= 0) {
            JsonResponse::send(422, [
                'status'  => 'error',
                'message' => 'product_id dan quantity wajib diisi dan harus lebih besar dari 0',
            ]);
            return;
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $product = Product::find($productId);
            if ($product === null) {
                $pdo->rollBack();
                JsonResponse::send(404, [
                    'status'  => 'error',
                    'message' => "Product dengan id {$productId} tidak ditemukan",
                ]);
                return;
            }

            if ((int) $product['stock'] < $quantity) {
                $pdo->rollBack();
                JsonResponse::send(409, [
                    'status'  => 'error',
                    'message' => 'Stock produk tidak mencukupi',
                    'data'    => [
                        'available_stock'  => (int) $product['stock'],
                        'requested_quantity' => $quantity,
                    ],
                ]);
                return;
            }

            $unitPrice  = (float) $product['price'];
            $totalPrice = $unitPrice * $quantity;

            if (!Product::decreaseStock($productId, $quantity)) {
                $pdo->rollBack();
                JsonResponse::send(409, [
                    'status'  => 'error',
                    'message' => 'Gagal mengurangi stock, kemungkinan stock berubah saat transaksi',
                ]);
                return;
            }

            $orderId = Order::create($productId, $quantity, $unitPrice, $totalPrice);
            $pdo->commit();

            JsonResponse::send(201, [
                'status' => 'success',
                'data'   => [
                    'id'          => $orderId,
                    'product_id'  => $productId,
                    'product'     => $product['name'],
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'total_price' => $totalPrice,
                ],
            ]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            JsonResponse::send(500, [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function show(int $id): void
    {
        $order = Order::find($id);

        if ($order === null) {
            JsonResponse::send(404, [
                'status'  => 'error',
                'message' => "Order dengan id {$id} tidak ditemukan",
            ]);
            return;
        }

        JsonResponse::send(200, [
            'status' => 'success',
            'data'   => $order,
        ]);
    }
}
