<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ecom', 'root', '');
$col = $pdo->query("SHOW COLUMNS FROM orders LIKE 'store_id'")->fetch();
echo 'orders.store_id: ' . ($col ? 'OK' : 'MISSING') . PHP_EOL;
$idx = $pdo->query("SHOW INDEX FROM orders WHERE Key_name LIKE '%store%'")->fetchAll();
echo 'index rows: ' . count($idx) . PHP_EOL;
