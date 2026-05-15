# PHP RESTful API - Orders

API sederhana untuk membuat order menggunakan PHP murni + PDO + MySQL.

## Struktur Proyek

```
.
├── config/
│   └── database.php           # Konfigurasi koneksi MySQL
├── public/
│   └── index.php              # Entry point / router
├── sql/
│   └── schema.sql             # Schema DB + sample data
└── src/
    ├── autoload.php           # PSR-4 style autoloader sederhana
    ├── Database.php           # Singleton PDO connection
    ├── Controllers/
    │   └── OrderController.php
    ├── Http/
    │   └── JsonResponse.php
    └── Models/
        ├── Order.php
        └── Product.php
```

## Setup

1. Buat database & isi sample data:

   ```bash
   mysql -u root -p < sql/schema.sql
   ```

2. (Opsional) Set environment variable untuk koneksi DB:

   ```bash
   export DB_HOST=127.0.0.1
   export DB_PORT=3306
   export DB_NAME=shop_db
   export DB_USER=root
   export DB_PASS=secret
   ```

3. Jalankan PHP built-in web server dari root project:

   ```bash
   php -S localhost:8000 -t public
   ```

## Endpoints

### POST `/api/orders`

Membuat order baru. Harga total dihitung otomatis dari `products.price`.

**Request body (JSON):**

```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Response sukses (201):**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "product_id": 1,
    "product": "Laptop Asus",
    "quantity": 2,
    "unit_price": 12500000,
    "total_price": 25000000
  }
}
```

**Response error:**

- `422` — `product_id` / `quantity` tidak valid
- `404` — Produk tidak ditemukan
- `409` — Stock tidak mencukupi

### GET `/api/orders/{id}`

Mengambil detail order berdasarkan id.

## Contoh `curl`

```bash
curl -X POST http://localhost:8000/api/orders \
  -H 'Content-Type: application/json' \
  -d '{"product_id": 1, "quantity": 2}'

curl http://localhost:8000/api/orders/1
```
