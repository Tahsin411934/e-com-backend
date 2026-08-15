<?php
$reports = file_get_contents('Modules/Reports/app/Providers/ReportsServiceProvider.php');
$cart = file_get_contents('Modules/Cart/app/Providers/CartServiceProvider.php');

echo "Reports file size: " . strlen($reports) . " bytes\n";
echo "Cart file size: " . strlen($cart) . " bytes\n";

// Check if classes can be loaded
require 'vendor/autoload.php';

echo "\nCan autoload CartServiceProvider: " . (class_exists('Modules\Cart\Providers\CartServiceProvider') ? 'YES' : 'NO') . "\n";
echo "Can autoload ReportsServiceProvider: " . (class_exists('Modules\Reports\Providers\ReportsServiceProvider') ? 'YES' : 'NO') . "\n";

// Check composer autoloader classmap
$composer = json_decode(file_get_contents('vendor/composer/installed.json'), true);
echo "\nChecking composer files...\n";

$autoloader = include 'vendor/autoload.php';
$reflection = new ReflectionClass($autoloader);
echo "Autoloader class: " . get_class($autoloader) . "\n";
