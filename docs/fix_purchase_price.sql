-- Script untuk Update Purchase Price dari Batch Data
-- Jalankan ini jika banyak COGS = 0

-- 1. Cek berapa products yang tidak punya purchase_price
SELECT 
    COUNT(*) as total_products_tanpa_harga,
    (SELECT COUNT(*) FROM products) as total_products
FROM products 
WHERE purchase_price IS NULL OR purchase_price = 0;

-- 2. Update purchase_price dari rata-rata batch
UPDATE products 
SET purchase_price = COALESCE(
    (SELECT AVG(purchase_price) 
     FROM product_batches 
     WHERE product_id = products.id 
     AND purchase_price > 0),
    purchase_price
)
WHERE (purchase_price IS NULL OR purchase_price = 0)
AND EXISTS (SELECT 1 FROM product_batches WHERE product_id = products.id);

-- 3. Verifikasi hasil
SELECT 
    COUNT(*) as products_updated,
    AVG(purchase_price) as avg_purchase_price
FROM products 
WHERE purchase_price > 0;

-- 4. Lihat products yang masih NULL (tidak punya batch data)
SELECT 
    id, 
    name, 
    purchase_price,
    (SELECT COUNT(*) FROM product_batches WHERE product_id = products.id) as batch_count
FROM products 
WHERE (purchase_price IS NULL OR purchase_price = 0)
LIMIT 20;
