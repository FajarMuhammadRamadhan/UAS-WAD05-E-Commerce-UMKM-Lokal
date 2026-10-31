<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Website',
                'email' => 'testweb@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);
        }

        $products = Product::all();
        if ($products->count() >= 2 && Order::count() === 0) {
            $prod1 = $products[0];
            $prod2 = $products[1];

            // Order 1
            $order1 = Order::create([
                'user_id' => $user->id,
                'customer_name' => 'Budi Santoso',
                'invoice_number' => 'INV-' . date('Ymd') . '-0001',
                'total_price' => ($prod1->price * 2) + ($prod2->price * 1),
                'status' => 'completed',
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $prod1->id,
                'quantity' => 2,
                'price' => $prod1->price,
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $prod2->id,
                'quantity' => 1,
                'price' => $prod2->price,
            ]);

            // Order 2
            $order2 = Order::create([
                'user_id' => $user->id,
                'customer_name' => 'Siti Aminah',
                'invoice_number' => 'INV-' . date('Ymd') . '-0002',
                'total_price' => ($prod1->price * 1),
                'status' => 'paid',
            ]);

            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $prod1->id,
                'quantity' => 1,
                'price' => $prod1->price,
            ]);
        }
    }
}

