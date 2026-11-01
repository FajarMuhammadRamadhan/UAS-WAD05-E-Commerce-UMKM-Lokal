<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Hanya bisa diakses kalau BELUM login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated catalog routes
Route::middleware('auth')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('dashboard');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

    // Customer: Pesanan Online & Riwayat Belanja
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.my_orders');
    Route::get('/checkout', [OrderController::class, 'customerCheckout'])->name('orders.checkout');
    Route::post('/checkout', [OrderController::class, 'storeCustomerOrder'])->name('orders.store_customer');
    Route::get('/my-orders/{order}', [OrderController::class, 'showCustomerOrder'])->name('orders.show_customer');
    Route::patch('/my-orders/{order}/change-payment-method', [OrderController::class, 'changePaymentMethod'])->name('orders.change_payment_method');
    Route::patch('/my-orders/{order}/cancel', [OrderController::class, 'cancelCustomerOrder'])->name('orders.cancel_customer');
    Route::post('/my-orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('orders.confirm_payment');
    Route::get('/api/orders/{order}/status', [OrderController::class, 'checkOrderStatus'])->name('orders.check_status');
    Route::get('/api/my-orders/check-updates', [OrderController::class, 'checkCustomerOrdersUpdates'])->name('orders.customer_check_updates');
    Route::get('/api/customer/notifications', [OrderController::class, 'customerNotifications'])->name('notifications.customer');
    Route::post('/api/customer/notifications/mark-read', [OrderController::class, 'markCustomerNotificationsRead'])->name('notifications.customer_mark_read');
});

// Transaksi Penjualan (Khusus Admin, Customer tidak diizinkan)
Route::middleware(['auth', 'can_manage_orders'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/api/notifications/unread-orders', [OrderController::class, 'unreadOrdersCount'])->name('notifications.unread_orders');
    Route::post('/api/notifications/mark-all-read', [OrderController::class, 'markAllNotificationsRead'])->name('notifications.mark_all_read');
});

// Administrative routes (Khusus Administrator: CRUD Produk & Pengeluaran)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
});

Route::middleware('auth')->get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
