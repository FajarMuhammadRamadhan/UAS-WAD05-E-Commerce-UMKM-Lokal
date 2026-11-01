<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarungGenZWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);
    }

    public function test_customer_can_view_catalog_but_cannot_manage_products_or_orders(): void
    {
        $this->actingAs($this->customer);
        $product = $this->createProduct('Teh Botol', 5000, 10);

        $this->get(route('products.index'))->assertOk()->assertSee('Teh Botol');
        $this->get(route('products.show', $product))->assertOk()->assertDontSee('Edit Produk');
        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('orders.index'))->assertForbidden();
        $this->post(route('orders.store'))->assertForbidden();
        $this->get(route('expenses.index'))->assertForbidden();
    }

    public function test_registration_assigns_customer_role(): void
    {
        $this->post(route('register'), [
            'name' => 'New Customer',
            'email' => 'new-customer@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('products.index'));

        $user = User::where('email', 'new-customer@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role);

        $this->actingAs($user)->get(route('products.create'))->assertForbidden();
    }

    public function test_admin_can_create_an_order_with_multiple_products_and_stock_is_reserved(): void
    {
        $first = $this->createProduct('Kopi', 1200, 5);
        $second = $this->createProduct('Teh', 2500, 3);

        $this->actingAs($this->admin)->post(route('orders.store'), [
            'customer_name' => 'Budi',
            'status' => 'pending',
            'items' => [
                ['product_id' => $first->id, 'quantity' => 2],
                ['product_id' => $second->id, 'quantity' => 2],
            ],
        ])->assertRedirect(route('orders.index'));

        $order = Order::with('items')->firstOrFail();
        $this->assertCount(2, $order->items);
        $this->assertEquals(7400, $order->total_price);
        $this->assertSame(3, $first->fresh()->stock);
        $this->assertSame(1, $second->fresh()->stock);
    }

    public function test_dashboard_asset_value_tracks_stock_sales_and_cancellations(): void
    {
        $product = $this->createProduct('Kopi', 5000, 10);
        $this->actingAs($this->admin);

        $this->get(route('products.index'))->assertOk()->assertSee('Rp 50.000');

        $this->post(route('orders.store'), [
            'customer_name' => 'Budi',
            'status' => 'completed',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertRedirect(route('orders.index'));

        $this->get(route('products.index'))->assertOk()->assertSee('Rp 35.000');

        $order = Order::firstOrFail();
        $this->patch(route('orders.updateStatus', $order), ['status' => 'canceled'])
            ->assertRedirect();

        $this->get(route('products.index'))->assertOk()->assertSee('Rp 50.000');
    }

    public function test_expense_is_saved_and_reflected_in_dashboard_net_income(): void
    {
        $product = $this->createProduct('Kopi', 5000, 10);
        $order = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Budi',
            'invoice_number' => 'INV-INCOME-001',
            'total_price' => 50000,
            'status' => 'completed',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => $product->price,
        ]);

        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'title' => 'Tagihan listrik',
            'category' => 'tagihan',
            'amount' => '12.500',
            'description' => 'Listrik bulan ini',
            'expense_date' => '2026-10-29',
        ])->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();
        $this->assertSame($this->admin->id, $expense->user_id);
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Rp 50.000')
            ->assertSee('Rp 12.500')
            ->assertSee('Rp 37.500');

        $this->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index'));
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Rp 50.000')
            ->assertSee('Rp 0')
            ->assertDontSee('Rp 37.500');
    }

    public function test_order_is_rejected_without_changing_stock_when_any_item_exceeds_stock(): void
    {
        $product = $this->createProduct('Susu', 8000, 2);

        $this->actingAs($this->admin)->post(route('orders.store'), [
            'customer_name' => 'Budi',
            'status' => 'completed',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_cancellation_restores_stock_once_and_cannot_be_reversed(): void
    {
        $product = $this->createProduct('Roti', 4000, 5);
        $this->actingAs($this->admin)->post(route('orders.store'), [
            'customer_name' => 'Siti',
            'status' => 'pending',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);
        $order = Order::firstOrFail();

        $this->patch(route('orders.updateStatus', $order), ['status' => 'canceled'])
            ->assertRedirect();
        $this->assertSame(5, $product->fresh()->stock);

        $this->patch(route('orders.updateStatus', $order), ['status' => 'canceled'])
            ->assertRedirect();
        $this->assertSame(5, $product->fresh()->stock);

        $this->patch(route('orders.updateStatus', $order), ['status' => 'paid'])
            ->assertSessionHasErrors('status');
        $this->assertSame('canceled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_product_and_order_search_filters_return_matching_records(): void
    {
        $matching = $this->createProduct('Kopi Susu', 7000, 4);
        $this->createProduct('Teh Manis', 4000, 0);
        $this->actingAs($this->admin);

        $this->get(route('products.index', ['search' => 'Kopi', 'stock' => 'in_stock']))
            ->assertOk()
            ->assertSee('Kopi Susu')
            ->assertDontSee('Teh Manis');

        $order = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Pelanggan Kopi',
            'invoice_number' => 'INV-SEARCH-001',
            'total_price' => 7000,
            'status' => 'paid',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $matching->id,
            'quantity' => 1,
            'price' => $matching->price,
        ]);

        $this->get(route('orders.index', ['search' => 'Kopi', 'status' => 'paid']))
            ->assertOk()
            ->assertSee('INV-SEARCH-001');
    }

    public function test_revenue_is_grouped_by_month_and_filtered_by_month(): void
    {
        $this->actingAs($this->admin);

        $orderCurrent = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Pelanggan Bulan Ini',
            'invoice_number' => 'INV-MONTH-CURRENT',
            'total_price' => 50000,
            'status' => 'completed',
            'created_at' => now(),
        ]);

        $orderPast = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Pelanggan Bulan Lalu',
            'invoice_number' => 'INV-MONTH-PAST',
            'total_price' => 30000,
            'status' => 'paid',
        ]);
        $orderPast->created_at = now()->subMonthNoOverflow();
        $orderPast->save();

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('INV-MONTH-CURRENT')
            ->assertSee('INV-MONTH-PAST')
            ->assertSee('Kelompok Rekap Pemasukan Bulanan');

        $this->get(route('orders.index', ['month' => now()->subMonthNoOverflow()->format('Y-m')]))
            ->assertOk()
            ->assertSee('INV-MONTH-PAST')
            ->assertDontSee('INV-MONTH-CURRENT');
    }

    public function test_pagination_supports_custom_per_page_options(): void
    {
        $this->actingAs($this->admin);

        for ($i = 1; $i <= 15; $i++) {
            Order::create([
                'user_id' => $this->admin->id,
                'customer_name' => 'Pelanggan ' . $i,
                'invoice_number' => 'INV-PAGE-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'total_price' => 10000,
                'status' => 'paid',
            ]);
        }

        // Default: 10 per page -> 15 orders split into pages
        $response10 = $this->get(route('orders.index', ['per_page' => 10]))->assertOk();
        $response10->assertSee('Tampilkan:');
        $response10->assertSee('per-page-select');

        // All: displays all 15 orders on a single page
        $responseAll = $this->get(route('orders.index', ['per_page' => 'all']))->assertOk();
        $responseAll->assertSee('INV-PAGE-001');
        $responseAll->assertSee('INV-PAGE-015');
    }

    public function test_customer_cannot_access_admin_or_orders_management(): void
    {
        $this->actingAs($this->customer);

        // Customer cannot access orders management
        $this->get(route('orders.index'))->assertForbidden();
        $this->get(route('orders.create'))->assertForbidden();

        // Customer cannot create product or expenses
        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('expenses.index'))->assertForbidden();

        // Role labels test (2 roles: Administrator & Customer)
        $this->assertSame('Customer', $this->customer->getRoleLabel());
        $this->assertSame('Administrator', $this->admin->getRoleLabel());
    }

    public function test_customer_can_order_online_and_admin_receives_notification(): void
    {
        $product = $this->createProduct('Minyak Goreng 2L', 35000, 10);

        // 1. Customer places order online with payment method QRIS
        $this->actingAs($this->customer);
        $response = $this->post(route('orders.store_customer'), [
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Mawar No. 12',
            'payment_method' => 'qris',
            'notes' => 'Tolong antarkan sore hari',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->firstOrFail();
        $response->assertRedirect(route('orders.show_customer', $order->id));
        $this->assertSame('pending', $order->status);
        $this->assertSame(70000.0, (float) $order->total_price);
        $this->assertSame('qris', $order->payment_method);
        $this->assertSame(8, $product->fresh()->stock);

        // 2. Customer sees invoice & confirms payment
        $this->get(route('orders.show_customer', $order->id))
            ->assertOk()
            ->assertSee('Minyak Goreng 2L')
            ->assertSee('Saya Sudah Selesai Bayar');

        $this->post(route('orders.confirm_payment', $order->id))
            ->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);

        // 3. Admin receives notification via API
        $this->actingAs($this->admin);
        $notifResponse = $this->get(route('notifications.unread_orders'))
            ->assertOk()
            ->assertJsonStructure([
                'count',
                'total_notif',
                'recent',
            ]);

        $this->assertGreaterThanOrEqual(1, $notifResponse->json('total_notif'));

        // 4. Admin views the order -> notification is marked as read and cleared!
        $this->get(route('orders.show', $order->id))->assertOk();
        $this->assertTrue($order->fresh()->is_admin_read);

        $clearedResponse = $this->get(route('notifications.unread_orders'))->assertOk();
        $this->assertSame(0, $clearedResponse->json('count'));
    }

    public function test_customer_can_change_payment_method_when_pending(): void
    {
        $product = $this->createProduct('Gula Pasir 1kg', 18000, 10);

        $this->actingAs($this->customer);

        // Buat pesanan dengan metode transfer
        $this->post(route('orders.store_customer'), [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Mawar No. 12',
            'payment_method' => 'transfer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->firstOrFail();
        $this->assertSame('transfer', $order->payment_method);

        // Ganti ke COD
        $response = $this->patch(route('orders.change_payment_method', $order->id), [
            'payment_method' => 'cod',
        ]);
        $response->assertSessionHas('success');
        $this->assertSame('cod', $order->fresh()->payment_method);

        // Ganti ke QRIS
        $response = $this->patch(route('orders.change_payment_method', $order->id), [
            'payment_method' => 'qris',
        ]);
        $response->assertSessionHas('success');
        $this->assertSame('qris', $order->fresh()->payment_method);

        // Customer lain tidak bisa mengganti metode pesanan ini
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($otherCustomer);
        $this->patch(route('orders.change_payment_method', $order->id), [
            'payment_method' => 'transfer',
        ])->assertForbidden();
    }

    public function test_customer_can_cancel_pending_order_with_reason(): void
    {
        $product = $this->createProduct('Kopi Kapal Api', 15000, 10);

        $this->actingAs($this->customer);

        // Buat pesanan
        $this->post(route('orders.store_customer'), [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Anggrek No. 5',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->firstOrFail();
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame('pending', $order->status);

        // Customer membatalkan pesanan dengan alasan
        $response = $this->patch(route('orders.cancel_customer', $order->id), [
            'cancellation_reason' => 'Ingin menambah produk lain',
        ]);

        $response->assertSessionHas('success');
        $freshOrder = $order->fresh();
        $this->assertSame('canceled', $freshOrder->status);
        $this->assertStringContainsString('Ingin menambah produk lain', $freshOrder->cancellation_reason);
        // Stok dikembalikan
        $this->assertSame(10, $product->fresh()->stock);

        // Customer tidak bisa membatalkan ulang pesanan yang sudah dibatalkan
        $this->patch(route('orders.cancel_customer', $order->id), [
            'cancellation_reason' => 'Coba lagi',
        ])->assertSessionHas('error');
    }

    public function test_admin_can_cancel_order_with_custom_typed_reason(): void
    {
        $product = $this->createProduct('Susu Ultra Milk 1L', 20000, 5);

        // Customer checkout
        $this->actingAs($this->customer);
        $this->post(route('orders.store_customer'), [
            'customer_name' => 'Customer A',
            'customer_phone' => '0811111111',
            'customer_address' => 'Jl. Kenanga 10',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->firstOrFail();
        $this->assertSame(3, $product->fresh()->stock);

        // Admin membatalkan pesanan dengan catatan bebas
        $this->actingAs($this->admin);
        $response = $this->patch(route('orders.updateStatus', $order->id), [
            'status' => 'canceled',
            'cancellation_reason' => 'Stok di toko fisik baru saja habis terjual.',
        ]);

        $response->assertSessionHas('success');
        $freshOrder = $order->fresh();
        $this->assertSame('canceled', $freshOrder->status);
        $this->assertSame('Stok di toko fisik baru saja habis terjual.', $freshOrder->cancellation_reason);
        $this->assertSame(5, $product->fresh()->stock); // Stok kembali
    }

    private function createProduct(string $name, int $price, int $stock): Product
    {
        return Product::create([
            'name' => $name,
            'category_id' => $this->category->id,
            'price' => $price,
            'stock' => $stock,
        ]);
    }
}
