<?php

namespace Modules\Order\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Order\Models\Payment;
use Modules\Order\Models\Refund;

class OrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Orders
        $orders = [];
        for ($i = 1; $i <= 5; $i++) {
            $orders[] = [
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => 1,
                'store_id' => 1,
                'source' => 'web',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'fulfillment_status' => 'unfulfilled',
                'currency_code' => 'USD',
                'subtotal' => 100.00 * $i,
                'discount_total' => 0,
                'tax_total' => 10.00 * $i,
                'shipping_total' => 5.00,
                'grand_total' => 115.00 * $i,
                'customer_note' => 'Sample order ' . $i,
                'placed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Order::insert($orders);
    }
}