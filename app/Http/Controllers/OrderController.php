<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    // Menampilkan riwayat seluruh transaksi penjualan
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,paid,completed,canceled'],
            'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'per_page' => ['nullable', 'in:10,20,50,all,semua'],
        ]);
        $search = trim($filters['search'] ?? '');
        $selectedMonth = trim($filters['month'] ?? '');
        $perPageInput = $filters['per_page'] ?? '10';
        $perPage = ($perPageInput === 'all' || $perPageInput === 'semua') ? 1000 : (int) $perPageInput;
        if (!in_array($perPage, [10, 20, 50, 1000], true)) {
            $perPage = 10;
        }

        $monthsList = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $orders = Order::with(['user', 'items.product'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhereHas('items.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when($selectedMonth !== '', function ($query) use ($selectedMonth) {
                [$filterYear, $filterMonth] = explode('-', $selectedMonth);
                $query->whereYear('created_at', $filterYear)->whereMonth('created_at', $filterMonth);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalRevenue = (float) Order::whereIn('status', ['paid', 'completed'])->sum('total_price');
        $totalOrdersCount = Order::where('status', '!=', 'canceled')->count();

        // Pemasukan khusus bulan berjalan
        $currentMonthName = ($monthsList[now()->format('m')] ?? now()->format('F')) . ' ' . now()->format('Y');
        $currentMonthRevenue = (float) Order::whereIn('status', ['paid', 'completed'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_price');

        // Pemasukan berdasarkan filter bulan yang dipilih
        $filteredMonthRevenue = null;
        $selectedMonthLabel = null;
        if ($selectedMonth !== '') {
            [$y, $m] = explode('-', $selectedMonth);
            $filteredMonthRevenue = (float) Order::whereIn('status', ['paid', 'completed'])
                ->whereYear('created_at', $y)
                ->whereMonth('created_at', $m)
                ->sum('total_price');
            $selectedMonthLabel = ($monthsList[$m] ?? $m) . ' ' . $y;
        }

        $monthlyBreakdown = $this->getMonthlyBreakdown($monthsList);
        $availableMonths = $this->getAvailableMonths($monthsList);

        return view('orders.index', compact(
            'orders',
            'totalRevenue',
            'totalOrdersCount',
            'currentMonthRevenue',
            'currentMonthName',
            'filteredMonthRevenue',
            'selectedMonthLabel',
            'monthlyBreakdown',
            'availableMonths',
            'selectedMonth',
            'search',
            'filters'
        ));
    }

    // Rekap kelompok pemasukan per bulan
    private function getMonthlyBreakdown(array $monthsList)
    {
        return Order::whereIn('status', ['paid', 'completed'])
            ->get(['created_at', 'total_price'])
            ->groupBy(fn ($order) => $order->created_at->format('Y-m'))
            ->map(function ($items, $monthKey) use ($monthsList) {
                [$year, $month] = explode('-', $monthKey);
                $monthLabel = ($monthsList[$month] ?? $month) . ' ' . $year;
                return (object) [
                    'month_key' => $monthKey,
                    'month_label' => $monthLabel,
                    'transaction_count' => $items->count(),
                    'total_revenue' => (float) $items->sum('total_price'),
                ];
            })
            ->sortByDesc('month_key')
            ->values();
    }

    // Daftar bulan untuk opsi filter dropdown
    private function getAvailableMonths(array $monthsList)
    {
        return Order::pluck('created_at')
            ->map(fn ($date) => $date->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values()
            ->map(function ($key) use ($monthsList) {
                [$y, $m] = explode('-', $key);
                return [
                    'key' => $key,
                    'label' => ($monthsList[$m] ?? $m) . ' ' . $y,
                ];
            });
    }

    // Menampilkan form pencatatan transaksi penjualan baru
    public function create()
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $productsGrouped = $products->groupBy(function ($item) {
            return $item->category->name ?? 'Lainnya';
        })->sortKeys();

        return view('orders.create', compact('products', 'productsGrouped'));
    }

    // Menyimpan transaksi penjualan baru ke database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:completed,paid,pending',
        ]);

        DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);
            $products = Product::whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalPrice = 0;
            foreach ($items as $index => $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" => 'Produk sudah tidak tersedia.',
                    ]);
                }
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" => "Jumlah pesanan untuk {$product->name} melebihi sisa stok yang ada. Sisa stok tersedia: {$product->stock} pcs.",
                    ]);
                }
                $totalPrice += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'total_price' => $totalPrice,
                'status' => $validated['status'],
            ]);

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                $product->decrement('stock', $item['quantity']);
            }
        });

        return redirect()->route('orders.index')->with('success', 'Transaksi penjualan berhasil disimpan dan stok produk telah diperbarui!');
    }

    // Mengubah status pembayaran transaksi
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,completed,canceled',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $isCod = ($order->payment_method ?? 'cod') === 'cod';
        $labels = [
            'pending' => 'Pending',
            'paid' => $isCod ? 'Diproses' : 'Lunas',
            'completed' => 'Selesai',
            'canceled' => 'Dibatalkan',
        ];

        DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $nextStatus = $validated['status'];

            if ($lockedOrder->status === 'canceled' && $nextStatus !== 'canceled') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi yang sudah dibatalkan tidak dapat diaktifkan kembali.',
                ]);
            }

            if ($lockedOrder->status !== 'canceled' && $nextStatus === 'canceled') {
                $items = $lockedOrder->items()->get();
                $productIds = $items->pluck('product_id')->unique()->sort()->values();
                $products = Product::whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $products->get($item->product_id)?->increment('stock', $item->quantity);
                }
            }

            $updateData = [
                'status' => $nextStatus,
                'is_admin_read' => true,
                'is_customer_read' => false, // Set false agar customer dapat notif baru
            ];

            if ($nextStatus === 'canceled') {
                $updateData['cancellation_reason'] = !empty($validated['cancellation_reason']) 
                    ? trim($validated['cancellation_reason']) 
                    : 'Dibatalkan oleh pihak toko.';
            }

            $lockedOrder->update($updateData);
        });

        return back()->with('success', 'Status transaksi '.$order->invoice_number.' diubah menjadi '.$labels[$validated['status']].'.');
    }

    // Menampilkan detail invoice transaksi tertentu
    public function show(Order $order)
    {
        if (Auth::user()?->canManageOrders() && ! $order->is_admin_read) {
            $order->update(['is_admin_read' => true]);
        }

        $order->load(['user', 'items.product']);

        return view('orders.show', compact('order'));
    }

    // Customer: Halaman Riwayat Pesanan Saya
    public function myOrders(Request $request): View
    {
        $orders = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('orders.my_orders', compact('orders'));
    }

    // Customer: Halaman Belanja / Checkout Online
    public function customerCheckout(Request $request): View
    {
        $products = Product::with('category')->where('stock', '>', 0)->orderBy('name')->get();
        $categories = Category::has('products')
            ->orderBy('name')
            ->get();
        $selectedProductId = $request->query('product_id');

        return view('orders.checkout', compact('products', 'categories', 'selectedProductId'));
    }

    // Customer: Simpan Pesanan Online Baru
    public function storeCustomerOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'payment_method' => 'required|in:cod,transfer,qris',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);
            $products = Product::whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalPrice = 0;
            foreach ($items as $index => $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" => 'Produk sudah tidak tersedia.',
                    ]);
                }
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" => "Jumlah pesanan untuk {$product->name} melebihi sisa stok yang ada. Sisa stok tersedia: {$product->stock} pcs.",
                    ]);
                }
                $totalPrice += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => Auth::user()->name,
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'total_price' => $totalPrice,
                'status' => 'pending', // Otomatis status 'pending' (menunggu konfirmasi)
            ]);

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        return redirect()->route('orders.show_customer', $order->id)
            ->with('success', 'Pesanan online Anda berhasil dibuat! Pemilik warung telah diberitahu dan sedang menyiapkan pesanan.');
    }

    // Customer: Detail Pesanan Saya
    public function showCustomerOrder(Order $order): View
    {
        if ($order->user_id !== Auth::id() && ! Auth::user()->canManageOrders()) {
            abort(403, 'Anda tidak berhak melihat pesanan ini.');
        }

        $order->load(['user', 'items.product']);

        return view('orders.show_customer', compact('order'));
    }

    // Customer: Ganti Metode Pembayaran (COD / Transfer / QRIS) jika salah pilih di awal
    public function changePaymentMethod(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Metode pembayaran hanya dapat diubah saat pesanan masih dalam status pending.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cod,transfer,qris',
        ]);

        $order->update([
            'payment_method' => $validated['payment_method'],
        ]);

        $labels = [
            'cod' => 'COD (Bayar Tunai di Tempat)',
            'transfer' => 'Transfer Bank Manual',
            'qris' => 'QRIS',
        ];

        return back()->with('success', 'Metode pembayaran berhasil diubah menjadi ' . ($labels[$validated['payment_method']] ?? $validated['payment_method']) . '.');
    }

    // Customer: Batalkan Pesanan Online Sendiri (jika masih status pending)
    public function cancelCustomerOrder(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Pesanan hanya dapat dibatalkan saat masih dalam status pending / belum diproses.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'canceled') {
                $items = $lockedOrder->items()->get();
                $productIds = $items->pluck('product_id')->unique()->sort()->values();
                $products = Product::whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $products->get($item->product_id)?->increment('stock', $item->quantity);
                }
            }

            $userReason = trim($validated['cancellation_reason']);
            $lockedOrder->update([
                'status' => 'canceled',
                'cancellation_reason' => 'Dibatalkan oleh Pelanggan: ' . $userReason,
                'is_customer_read' => true,
                'is_admin_read' => false, // Set false agar admin dapat notif ada pesanan dibatalkan
            ]);
        });

        return back()->with('success', 'Pesanan Anda berhasil dibatalkan dan seluruh stok produk telah dikembalikan.');
    }

    // Customer: Konfirmasi Pembayaran Berhasil (Transfer / QRIS)
    public function confirmPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->status === 'canceled') {
            return back()->with('error', 'Pesanan ini telah dibatalkan.');
        }

        $order->update([
            'status' => 'paid',
            'is_admin_read' => false, // Set false agar admin dapat notif pembayaran baru masuk
        ]);

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi! Admin warung telah menerima notifikasi dan sedang memproses pengiriman pesanan Anda.');
    }

    // API Notifikasi untuk Admin & Kasir: Menampilkan pesanan aktif yang belum close (pending / paid)
    public function unreadOrdersCount()
    {
        // Pesanan aktif yang belum close (belum selesai/batal)
        $activeOrders = Order::with('user')
            ->whereIn('status', ['pending', 'paid'])
            ->latest('updated_at')
            ->take(12)
            ->get();

        // Jumlah pesanan baru yang belum dibuka/dibaca oleh admin (indikator badge merah)
        $unreadCount = Order::where('is_admin_read', false)
            ->whereIn('status', ['pending', 'paid'])
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'count' => $unreadCount, // backward compatibility untuk test
            'total_notif' => $unreadCount,
            'active_count' => $activeOrders->count(),
            'recent' => $activeOrders->map(function ($order) {
                $isCod = ($order->payment_method ?? 'cod') === 'cod';
                $statusBadge = match ($order->status) {
                    'paid' => $isCod ? 'Diproses' : 'Lunas Online',
                    'pending' => $isCod ? 'COD Menunggu' : 'Menunggu Bayar',
                    default => ucfirst($order->status),
                };

                return [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'customer_name' => $order->customer_name ?? $order->user->name ?? 'Pelanggan',
                    'total_price' => number_format($order->total_price, 0, ',', '.'),
                    'status' => $order->status,
                    'status_label' => $statusBadge,
                    'is_unread' => ! $order->is_admin_read,
                    'payment_method' => strtoupper($order->payment_method ?? 'COD'),
                    'time_ago' => $order->updated_at->diffForHumans(),
                    'url' => route('orders.show', $order->id),
                ];
            }),
        ]);
    }

    // Admin/Kasir: Tandai semua notifikasi sudah dibaca (Tutup Notifikasi)
    public function markAllNotificationsRead()
    {
        Order::where('is_admin_read', false)->update(['is_admin_read' => true]);

        return response()->json(['success' => true]);
    }

    // Customer & Real-time: Cek status terkini satu pesanan untuk auto-update status di pelanggan
    public function checkOrderStatus(Order $order)
    {
        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'status_label' => $order->getStatusLabel(),
            'is_paid' => in_array($order->status, ['paid', 'completed']),
            'is_completed' => $order->status === 'completed',
            'is_canceled' => $order->status === 'canceled',
            'updated_at' => $order->updated_at->toIso8601String(),
        ]);
    }

    // Customer Real-time Sync: Cek pembaruan status pesanan-pesanan milik customer
    public function checkCustomerOrdersUpdates(Request $request)
    {
        $orders = Order::where('user_id', Auth::id())
            ->select('id', 'invoice_number', 'status', 'updated_at')
            ->latest('updated_at')
            ->take(15)
            ->get();

        $hashString = $orders->map(fn ($o) => "{$o->id}:{$o->status}:{$o->updated_at->timestamp}")->implode('|');

        return response()->json([
            'hash' => md5($hashString),
            'latest_canceled' => $orders->firstWhere('status', 'canceled')?->invoice_number,
        ]);
    }

    // API Notifikasi untuk Customer: Menampilkan notifikasi pembaruan pesanan (hanya yang BELUM dibaca)
    public function customerNotifications()
    {
        $userId = Auth::id();
        $unreadOrders = Order::where('user_id', $userId)
            ->where('is_customer_read', false)
            ->latest('updated_at')
            ->take(8)
            ->get();

        $canceledOrders = $unreadOrders->where('status', 'canceled');
        $hasCanceled = $canceledOrders->isNotEmpty();
        $canceledCount = $canceledOrders->count();

        return response()->json([
            'unread_count' => $unreadOrders->count(),
            'canceled_count' => $canceledCount,
            'has_canceled' => $hasCanceled,
            'items' => $unreadOrders->map(function (Order $order) {
                $isCanceled = $order->status === 'canceled';

                $title = match ($order->status) {
                    'canceled' => '⚠️ Pesanan Dibatalkan Toko',
                    'paid' => '📦 Pesanan Sedang Diproses',
                    'completed' => '✅ Pesanan Selesai Diterima',
                    default => '🕒 Pesanan Diterima Warung',
                };

                $badgeClass = match ($order->status) {
                    'canceled' => 'bg-danger text-white',
                    'paid' => 'bg-primary text-white',
                    'completed' => 'bg-success text-white',
                    default => 'bg-warning text-dark',
                };

                return [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'title' => $title,
                    'status' => $order->status,
                    'status_label' => $order->getStatusLabel(),
                    'cancellation_reason' => $order->cancellation_reason,
                    'badge_class' => $badgeClass,
                    'total_price' => number_format($order->total_price, 0, ',', '.'),
                    'time_ago' => $order->updated_at->diffForHumans(),
                    'url' => route('orders.show_customer', $order->id),
                    'is_canceled' => $isCanceled,
                ];
            }),
        ]);
    }

    // Customer: Tandai semua notifikasi pelanggan sudah dibaca (menghilangkan tanda merah dan membersihkan history popup)
    public function markCustomerNotificationsRead()
    {
        Order::where('user_id', Auth::id())
            ->where('is_customer_read', false)
            ->update(['is_customer_read' => true]);

        return response()->json(['success' => true]);
    }
}
