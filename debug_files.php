<?php
$reports_file = 'Modules/Reports/app/Providers/ReportsServiceProvider.php';
$cart_file = 'Modules/Cart/app/Providers/CartServiceProvider.php';

$reports = file_get_contents($reports_file);
$cart = file_get_contents($cart_file);

echo "=== Reports ===\n";
echo $reports;
echo "\n\n=== Cart ===\n";
echo $cart;
echo "\n\n=== Hex Comparison ===\n";
echo "Reports first 100 bytes (hex):\n";
echo bin2hex(substr($reports, 0, 100)) . "\n\n";
echo "Cart first 100 bytes (hex):\n";
echo bin2hex(substr($cart, 0, 100)) . "\n";
