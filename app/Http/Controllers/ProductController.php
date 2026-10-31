<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // Mengambil daftar kategori produk
    private function getCategories()
    {
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $defaultCategories = [
                'Sembako',
                'Makanan & Minuman',
                'Kebutuhan Rumah Tangga',
                'Perawatan Tubuh',
                'Lainnya',
            ];
            foreach ($defaultCategories as $name) {
                Category::firstOrCreate(
                    ['name' => $name],
                    ['name' => $name, 'slug' => Str::slug($name)]
                );
            }
            $categories = Category::all();
        }

        return $categories;
    }

    // Menampilkan dashboard dan daftar produk
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'stock' => ['nullable', 'in:in_stock,out_of_stock'],
            'per_page' => ['nullable', 'in:10,20,50,all,semua'],
        ]);
        $search = trim($filters['search'] ?? '');
        $perPageInput = $filters['per_page'] ?? '10';
        $perPage = ($perPageInput === 'all' || $perPageInput === 'semua') ? 1000 : (int) $perPageInput;
        if (!in_array($perPage, [10, 20, 50, 1000], true)) {
            $perPage = 10;
        }
        $isAdmin = $request->user()->isAdmin();
        $canManageOrders = $request->user()->canManageOrders();
        $categories = Category::orderBy('name')->get();

        $products = Product::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when(isset($filters['category_id']), fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when(($filters['stock'] ?? null) === 'in_stock', fn ($query) => $query->where('stock', '>', 0))
            ->when(($filters['stock'] ?? null) === 'out_of_stock', fn ($query) => $query->where('stock', 0))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $dashboard = [];
        if ($isAdmin) {
            $totalRevenue = (float) Order::whereIn('status', ['paid', 'completed'])->sum('total_price');
            $totalExpenses = (float) Expense::sum('amount');

            $monthsList = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            ];
            $currentMonthName = ($monthsList[now()->format('m')] ?? now()->format('F')) . ' ' . now()->format('Y');
            $currentMonthRevenue = (float) Order::whereIn('status', ['paid', 'completed'])
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('total_price');

            $monthlyBreakdown = Order::whereIn('status', ['paid', 'completed'])
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

            $dashboard = [
                'totalProducts' => Product::count(),
                'totalStock' => (int) Product::sum('stock'),
                'totalAssetValue' => (float) (Product::selectRaw('SUM(price * stock) as total_asset')->value('total_asset') ?? 0),
                'totalOrders' => Order::where('status', '!=', 'canceled')->count(),
                'totalRevenue' => $totalRevenue,
                'totalExpenses' => $totalExpenses,
                'netIncome' => $totalRevenue - $totalExpenses,
                'currentMonthRevenue' => $currentMonthRevenue,
                'currentMonthName' => $currentMonthName,
                'monthlyBreakdown' => $monthlyBreakdown,
                'recentOrders' => Order::with(['user', 'items.product'])->latest()->take(5)->get(),
            ];
        }

        return view('products.index', array_merge($dashboard, compact(
            'products',
            'categories',
            'search',
            'isAdmin',
            'canManageOrders',
            'filters'
        )));
    }

    // Menampilkan form tambah produk
    public function create()
    {
        $categories = $this->getCategories();

        return view('products.create', compact('categories'));
    }

    // Menyimpan produk baru ke database
    public function store(Request $request)
    {
        // Bersihkan format titik ribuan sebelum validasi
        if ($request->has('price')) {
            $cleanedPrice = preg_replace('/[^0-9]/', '', (string) $request->price);
            $request->merge(['price' => $cleanedPrice]);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk "'.$request->name.'" berhasil ditambahkan!');
    }

    // Menampilkan detail produk
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    // Menampilkan form edit produk
    public function edit(Product $product)
    {
        $categories = $this->getCategories();

        return view('products.edit', compact('product', 'categories'));
    }

    // Memperbarui data produk
    public function update(Request $request, Product $product)
    {
        // Bersihkan format titik ribuan sebelum validasi
        if ($request->has('price')) {
            $cleanedPrice = preg_replace('/[^0-9]/', '', (string) $request->price);
            $request->merge(['price' => $cleanedPrice]);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk "'.$request->name.'" berhasil diperbarui!');
    }

    // Menghapus produk dari database
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }
}
