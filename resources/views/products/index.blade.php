@extends('layouts.app')

@section('content')
@if($isAdmin)
<!-- Ringkasan Finansial Utama (Pemasukan, Pengeluaran, Saldo Bersih) -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('orders.index') }}" class="text-decoration-none d-block h-100">
            <div class="modern-card modern-card-hover p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Pemasukan ({{ $currentMonthName }})</span>
                    <div class="stat-icon-wrapper" style="background: #ecfdf5; color: #059669;">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                </div>
                <div class="stat-value text-success">
                    Rp {{ number_format($currentMonthRevenue, 0, ',', '.') }}
                </div>
                <div class="stat-subtext mt-1 d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-1">
                        <i class="bi bi-calendar2-check text-success"></i>
                        <span>Bulan {{ $currentMonthName }}</span>
                    </span>
                    <span class="text-primary fw-semibold small">Rekap &rarr;</span>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="{{ route('expenses.index') }}" class="text-decoration-none d-block h-100">
            <div class="modern-card modern-card-hover p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Total Pengeluaran</span>
                    <div class="stat-icon-wrapper" style="background: #fff1f2; color: #e11d48;">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                </div>
                <div class="stat-value text-danger">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </div>
                <div class="stat-subtext mt-1 d-flex justify-content-between align-items-center">
                    <span>Biaya operasional & belanja</span>
                    <span class="text-danger fw-semibold small">Buka &rarr;</span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Saldo Bersih (Net Profit)</span>
                <div class="stat-icon-wrapper" style="background: {{ $netIncome >= 0 ? '#eff6ff' : '#fff1f2' }}; color: {{ $netIncome >= 0 ? '#2563eb' : '#e11d48' }};">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="stat-value {{ $netIncome >= 0 ? 'text-primary' : 'text-danger' }}">
                Rp {{ number_format($netIncome, 0, ',', '.') }}
            </div>
            <div class="stat-subtext mt-1">
                <span>Pemasukan dikurangi pengeluaran</span>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Operasional & Inventaris Barang -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('orders.index') }}" class="text-decoration-none">
            <div class="modern-card modern-card-hover p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Total Penjualan</span>
                    <div class="stat-icon-wrapper" style="background: #f1f5f9; color: #0f172a;">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
                <div class="stat-value">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="stat-subtext mt-1 d-flex align-items-center justify-content-between">
                    <span>{{ $totalOrders }} transaksi tercatat</span>
                    <span class="text-primary fw-semibold small">Buka &rarr;</span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Nilai Aset Stok</span>
                <div class="stat-icon-wrapper" style="background: #eff6ff; color: #2563eb;">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
            <div class="stat-value text-dark">
                Rp {{ number_format($totalAssetValue, 0, ',', '.') }}
            </div>
            <div class="stat-subtext mt-1">
                <span>Total valuasi barang dagang</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Jenis Produk</span>
                <div class="stat-icon-wrapper" style="background: #fffbeb; color: #d97706;">
                    <i class="bi bi-grid"></i>
                </div>
            </div>
            <div class="stat-value text-dark">
                {{ $totalProducts }} <span style="font-size: 1rem; font-weight: 600;">Produk</span>
            </div>
            <div class="stat-subtext mt-1">
                <span>Katalog aktif terdaftar</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Total Stok Barang</span>
                <div class="stat-icon-wrapper" style="background: #f5f3ff; color: #7c3aed;">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
            <div class="stat-value text-dark">
                {{ number_format($totalStock, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Pcs</span>
            </div>
            <div class="stat-subtext mt-1">
                <span>Kuantitas seluruh inventaris</span>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Header Daftar Produk -->
<div class="row align-items-center mb-3 g-2">
    <div class="col-lg-6">
        <h3 class="section-title mb-1">{{ $isAdmin ? 'Daftar Produk WarungGenZ' : 'Katalog Produk WarungGenZ' }}</h3>
        <p class="section-subtitle mb-0">{{ $isAdmin ? 'Kelola katalog produk, update stok barang, dan kontrol harga.' : 'Lihat ketersediaan produk, varian kategori, dan informasi harga.' }}</p>
    </div>
    @if(!empty($canManageOrders) || $isAdmin)
    <div class="col-lg-6 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
        <a href="{{ route('orders.create') }}" class="btn-emerald-brand text-decoration-none">
            <i class="bi bi-plus-lg"></i>
            <span>Catat Penjualan</span>
        </a>
        @if($isAdmin)
        <a href="{{ route('expenses.create') }}" class="btn-outline-soft text-decoration-none text-danger border-danger-subtle">
            <i class="bi bi-dash-circle"></i>
            <span>Catat Pengeluaran</span>
        </a>
        <a href="{{ route('products.create') }}" class="btn-dark-brand text-decoration-none">
            <i class="bi bi-box-seam"></i>
            <span>Tambah Produk</span>
        </a>
        @endif
    </div>
    @else
    <div class="col-lg-6 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
        <a href="{{ route('orders.checkout') }}" class="btn-emerald-brand text-decoration-none">
            <i class="bi bi-cart3"></i>
            <span>Pesan Sekarang</span>
        </a>
        <a href="{{ route('orders.my_orders') }}" class="btn-outline-soft text-decoration-none">
            <i class="bi bi-bag-check"></i>
            <span>Pesanan Saya</span>
        </a>
    </div>
    @endif
</div>

<!-- Filter Pencarian Modern -->
<form action="{{ route('products.index') }}" method="GET" class="filter-card mb-4">
    @if(request('per_page'))
        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    @endif
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label for="product-search" class="form-label">Cari Produk / Kategori</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                    <i class="bi bi-search"></i>
                </span>
                <input id="product-search" type="search" name="search" value="{{ $search }}" class="form-control border-start-0" style="border-radius: 0 10px 10px 0;" placeholder="Ketik nama produk...">
            </div>
        </div>
        <div class="col-md-3">
            <label for="category-filter" class="form-label">Kategori</label>
            <select id="category-filter" name="category_id" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="stock-filter" class="form-label">Status Stok</label>
            <select id="stock-filter" name="stock" class="form-select">
                <option value="">Semua Stok</option>
                <option value="in_stock" {{ ($filters['stock'] ?? '') === 'in_stock' ? 'selected' : '' }}>Tersedia (&gt; 0)</option>
                <option value="out_of_stock" {{ ($filters['stock'] ?? '') === 'out_of_stock' ? 'selected' : '' }}>Habis (0)</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-dark-brand flex-grow-1 justify-content-center">
                Filter
            </button>
            <a href="{{ route('products.index') }}" class="btn-outline-soft justify-content-center" title="Reset filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </div>
</form>

<!-- Tampilan Desktop: Modern Table Produk -->
<div class="d-none d-md-block modern-table-container mb-4">
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th style="width: 70px;">Foto</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Jual</th>
                    <th>Sisa Stok</th>
                    @if($isAdmin)
                    <th class="text-center" style="width: 140px;">Tindakan</th>
                    @elseif(empty($canManageOrders))
                    <th class="text-center" style="width: 110px;">Beli Online</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                    <td>
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-thumb">
                        @else
                            <div class="product-thumb-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                        @if($product->description)
                            <div class="text-muted small text-truncate" style="max-width: 280px;">{{ $product->description }}</div>
                        @endif
                    </td>
                    <td>
                        @if(optional($product->category)->name)
                            <span class="badge-soft badge-soft-blue">
                                <i class="bi bi-tag-fill" style="font-size: 0.65rem;"></i>
                                {{ $product->category->name }}
                            </span>
                        @else
                            <span class="badge-soft badge-soft-slate">Tanpa Kategori</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold text-dark">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        @if($product->stock === 0)
                            <span class="badge-soft badge-soft-rose">
                                <i class="bi bi-x-circle-fill" style="font-size: 0.65rem;"></i>
                                Habis (0)
                            </span>
                        @elseif($product->stock <= 5)
                            <span class="badge-soft badge-soft-amber">
                                <i class="bi bi-exclamation-circle-fill" style="font-size: 0.65rem;"></i>
                                Menipis ({{ $product->stock }})
                            </span>
                        @else
                            <span class="badge-soft badge-soft-emerald">
                                <i class="bi bi-check2-circle" style="font-size: 0.65rem;"></i>
                                {{ $product->stock }} Pcs
                            </span>
                        @endif
                    </td>
                    @if($isAdmin)
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn-outline-soft btn-action-sm text-decoration-none" title="Ubah data produk">
                                <i class="bi bi-pencil-square text-primary"></i>
                                <span>Edit</span>
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline-soft btn-action-sm text-danger" title="Hapus produk">
                                    <i class="bi bi-trash3"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </td>
                    @elseif(empty($canManageOrders))
                    <td class="text-center">
                        @if($product->stock > 0)
                            <a href="{{ route('orders.checkout', ['product_id' => $product->id]) }}" class="btn-emerald-brand btn-sm text-decoration-none py-1 px-3 fw-semibold" style="font-size: 0.78rem;">
                                <i class="bi bi-cart-plus me-1"></i> Pesan Sekarang
                            </a>
                        @else
                            <span class="badge-soft badge-soft-slate small">Habis</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isAdmin || empty($canManageOrders) ? 7 : 6 }}" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                            <i class="bi bi-inbox fs-1 mb-2"></i>
                            <span class="fw-semibold">Tidak ada produk yang cocok dengan pencarian.</span>
                            <small class="mt-1">Coba sesuaikan kata kunci atau reset filter pencarian.</small>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tampilan Khusus Mobile / HP: Kartu Produk Praktis -->
<div class="d-md-none d-flex flex-column gap-3 mb-4">
    @forelse($products as $product)
    <div class="modern-card p-3 border shadow-sm">
        <div class="d-flex align-items-center gap-3">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 58px; height: 58px; object-fit: cover;">
            @else
                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 58px; height: 58px; font-size: 1.5rem;">
                    <i class="bi bi-image"></i>
                </div>
            @endif
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start gap-1">
                    <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $product->name }}</h6>
                </div>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge-soft badge-soft-blue" style="font-size: 0.68rem;">
                        {{ $product->category->name ?? 'Lainnya' }}
                    </span>
                    @if($product->stock === 0)
                        <span class="badge-soft badge-soft-rose" style="font-size: 0.68rem;">Habis</span>
                    @elseif($product->stock <= 5)
                        <span class="badge-soft badge-soft-amber" style="font-size: 0.68rem;">Sisa {{ $product->stock }}</span>
                    @else
                        <span class="badge-soft badge-soft-emerald" style="font-size: 0.68rem;">Stok: {{ $product->stock }}</span>
                    @endif
                </div>
                <div class="fw-bold text-success mt-1 fs-6">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </div>
            </div>
        </div>

        @if($product->description)
            <div class="text-muted small mt-2 pt-2 border-top" style="font-size: 0.78rem;">
                {{ $product->description }}
            </div>
        @endif

        <div class="mt-2 pt-2 border-top d-flex gap-2">
            @if($isAdmin)
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-soft flex-grow-1 text-center justify-content-center">
                    <i class="bi bi-pencil-square text-primary me-1"></i> Edit
                </a>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline flex-grow-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-soft w-100 text-danger text-center justify-content-center">
                        <i class="bi bi-trash3 me-1"></i> Hapus
                    </button>
                </form>
            @elseif(empty($canManageOrders))
                @if($product->stock > 0)
                    <a href="{{ route('orders.checkout', ['product_id' => $product->id]) }}" class="btn btn-emerald-brand btn-sm w-100 text-center justify-content-center">
                        <i class="bi bi-cart-plus me-1"></i> Pesan Sekarang
                    </a>
                @else
                    <button class="btn btn-light btn-sm w-100 text-muted" disabled>
                        Stok Produk Habis
                    </button>
                @endif
            @endif
        </div>
    </div>
    @empty
    <div class="modern-card p-4 text-center text-muted">
        <i class="bi bi-inbox fs-1 mb-2 text-secondary"></i>
        <div class="fw-semibold">Tidak ada produk yang cocok dengan pencarian.</div>
    </div>
    @endforelse
</div>

<!-- Pagination Nav -->
<div class="mb-5">
    {{ $products->links() }}
</div>

@if($isAdmin)
<!-- Transaksi Terbaru (Clean Activity Feed Layout) -->
<div class="modern-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Transaksi Penjualan Terbaru</h5>
            <small class="text-muted">Aktivitas penjualan yang baru saja dicatat pada sistem</small>
        </div>
        <a href="{{ route('orders.index') }}" class="btn-outline-soft btn-sm text-decoration-none">
            <span>Lihat Semua Riwayat</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="ps-3">No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Rincian Produk</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Waktu Transaksi</th>
                        <th class="text-center" style="width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('orders.show', $order->id) }}" class="fw-bold text-decoration-none text-primary">
                                {{ $order->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $order->customer_name ?? 'Pelanggan Umum' }}</div>
                            <small class="text-muted">{{ $order->user->name ?? 'Kasir' }}</small>
                        </td>
                        <td>
                            <span class="small text-muted">
                                {{ $order->items->map(fn($i) => ($i->product->name ?? 'Produk dihapus') . ' x' . $i->quantity)->implode(', ') }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-success">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            @php
                                $badgeStyle = match($order->status) {
                                    'completed' => 'badge-soft-emerald',
                                    'paid' => 'badge-soft-blue',
                                    'pending' => 'badge-soft-amber',
                                    'canceled' => 'badge-soft-rose',
                                    default => 'badge-soft-slate'
                                };
                                $statusLabel = match($order->status) {
                                    'completed' => 'Selesai & Lunas',
                                    'paid' => 'Lunas',
                                    'pending' => 'Pending',
                                    'canceled' => 'Dibatalkan',
                                    default => ucfirst($order->status)
                                };
                            @endphp
                            <span class="badge-soft {{ $badgeStyle }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        </td>
                        <td class="text-center">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn-outline-soft btn-action-sm text-decoration-none" title="Buka Faktur Penjualan">
                                <i class="bi bi-receipt"></i>
                                <span>Buka Faktur</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi penjualan yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
