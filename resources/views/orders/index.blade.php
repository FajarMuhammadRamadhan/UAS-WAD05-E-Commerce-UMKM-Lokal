@extends('layouts.app')

@section('content')
<!-- Header Riwayat Transaksi -->
<div class="row align-items-center mb-4 g-2">
    <div class="col-lg-6">
        <h3 class="section-title mb-1">Riwayat Transaksi Penjualan</h3>
        <p class="section-subtitle mb-0">Catatan faktur penjualan dan status pembayaran produk WarungGenZ.</p>
    </div>
    <div class="col-lg-6 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
        <a href="{{ route('orders.create') }}" class="btn-emerald-brand text-decoration-none">
            <i class="bi bi-plus-lg"></i>
            <span>Catat Penjualan Baru</span>
        </a>
        <a href="{{ route('products.index') }}" class="btn-outline-soft text-decoration-none">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Produk</span>
        </a>
    </div>
</div>

<!-- Ringkasan Finansial Transaksi Berdasarkan Kelompok Bulan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">
                    {{ $selectedMonth ? 'Pemasukan ' . $selectedMonthLabel : 'Pemasukan Bulan Ini (' . $currentMonthName . ')' }}
                </span>
                <div class="stat-icon-wrapper" style="background: #ecfdf5; color: #059669;">
                    <i class="bi bi-calendar2-check"></i>
                </div>
            </div>
            <div class="stat-value text-success">
                Rp {{ number_format($filteredMonthRevenue ?? $currentMonthRevenue, 0, ',', '.') }}
            </div>
            <div class="stat-subtext mt-1">
                <span>{{ $selectedMonth ? 'Total pemasukan lunas pada ' . $selectedMonthLabel : 'Akumulasi pemasukan lunas bulan berjalan' }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Total Pemasukan Semua Waktu</span>
                <div class="stat-icon-wrapper" style="background: #eff6ff; color: #2563eb;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-value text-dark">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="stat-subtext mt-1">
                <span>Total dari seluruh transaksi lunas sejak awal buka</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="modern-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Total Jumlah Transaksi</span>
                <div class="stat-icon-wrapper" style="background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0;">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <div class="stat-value text-dark">
                {{ $totalOrdersCount }} <span style="font-size: 1rem; font-weight: 600;">Transaksi</span>
            </div>
            <div class="stat-subtext mt-1">
                <span>Faktur penjualan yang berhasil dibuat dalam sistem</span>
            </div>
        </div>
    </div>
</div>

<!-- Kelompok Rekap Pemasukan Bulanan -->
<div class="modern-card p-4 mb-4">
    <div class="mb-3">
        <h5 class="fw-bold mb-1 text-dark">
            <i class="bi bi-calendar3 me-1 text-primary"></i> Kelompok Rekap Pemasukan Bulanan
        </h5>
        <small class="text-muted">Pilih salah satu bulan untuk melihat detail faktur pada bulan tersebut</small>
    </div>

    <div class="row g-3">
        @forelse($monthlyBreakdown as $m)
        <div class="col-md-4 col-sm-6">
            <a href="{{ $selectedMonth === $m->month_key ? route('orders.index', array_filter(['search' => $search, 'status' => $filters['status'] ?? null, 'per_page' => request('per_page')])) : route('orders.index', array_filter(array_merge(request()->query(), ['month' => $m->month_key]))) }}#daftar-transaksi" class="text-decoration-none d-block h-100">
                <div class="p-3 rounded-4 border h-100 transition-all {{ $selectedMonth === $m->month_key ? 'bg-primary-subtle border-primary shadow-sm' : 'bg-light modern-card-hover' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold fs-6 {{ $selectedMonth === $m->month_key ? 'text-primary' : 'text-dark' }}">
                            {{ $m->month_label }}
                        </span>
                        @if($selectedMonth === $m->month_key)
                            <span class="badge bg-primary text-white" style="font-size: 0.7rem;">Aktif Dipilih</span>
                        @else
                            <span class="badge-soft badge-soft-slate">{{ $m->transaction_count }} Transaksi</span>
                        @endif
                    </div>
                    <div class="fs-5 fw-bold text-success mb-1">
                        Rp {{ number_format($m->total_revenue, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted d-flex justify-content-between align-items-center mt-2">
                        <span>Pemasukan tercatat</span>
                        @if($selectedMonth === $m->month_key)
                            <span class="text-primary fw-semibold small">Sedang difilter &darr;</span>
                        @else
                            <span class="text-primary fw-semibold small">Lihat daftar transaksi &rarr;</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center text-muted py-3">
            Belum ada rekap pemasukan bulanan yang tercatat.
        </div>
        @endforelse
    </div>
</div>

<!-- Filter Pencarian Transaksi -->
<form action="{{ route('orders.index') }}" method="GET" class="filter-card mb-4" id="daftar-transaksi">
    @if(request('per_page'))
        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    @endif
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label for="order-search" class="form-label">Cari Invoice, Pelanggan, atau Produk</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                    <i class="bi bi-search"></i>
                </span>
                <input id="order-search" type="search" name="search" value="{{ $search }}" class="form-control border-start-0" style="border-radius: 0 10px 10px 0;" placeholder="Ketik nomor invoice atau nama pelanggan...">
            </div>
        </div>
        <div class="col-md-3">
            <label for="order-month" class="form-label">Kelompok Bulan</label>
            <select id="order-month" name="month" class="form-select">
                <option value="">Semua Periode Bulan</option>
                @foreach($availableMonths as $monthOpt)
                    <option value="{{ $monthOpt['key'] }}" {{ $selectedMonth === $monthOpt['key'] ? 'selected' : '' }}>
                        {{ $monthOpt['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="order-status" class="form-label">Status Pembayaran</label>
            <select id="order-status" name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="canceled" {{ ($filters['status'] ?? '') === 'canceled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-dark-brand flex-grow-1 justify-content-center">
                Filter
            </button>
            <a href="{{ route('orders.index') }}" class="btn-outline-soft justify-content-center" title="Reset filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </div>
</form>



<!-- Modern Tabel Transaksi -->
<div class="modern-table-container mb-4">
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>No. Invoice</th>
                    <th>Waktu</th>
                    <th>Pelanggan</th>
                    <th>Kasir</th>
                    <th>Rincian Produk Terjual</th>
                    <th>Total Bayar</th>
                    <th style="width: 140px;">Status</th>
                    <th class="text-center" style="width: 110px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('orders.show', $order->id) }}" class="fw-bold text-decoration-none text-primary">
                            {{ $order->invoice_number }}
                        </a>
                        <div class="mt-1 d-flex align-items-center gap-1">
                            <span class="badge bg-light text-secondary border text-uppercase" style="font-size: 0.65rem;">
                                {{ $order->payment_method ?? 'KASIR POS' }}
                            </span>
                            @if($order->status === 'paid' && in_array($order->payment_method, ['transfer', 'qris']))
                                <span class="badge bg-success-subtle text-success" style="font-size: 0.62rem;">Lunas Online</span>
                            @endif
                        </div>
                    </td>
                    <td class="small text-muted">
                        <div>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                        <div>{{ $order->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                    </td>
                    <td>
                        <span class="fw-bold text-dark">{{ $order->customer_name ?? 'Pelanggan Umum' }}</span>
                    </td>
                    <td class="small text-muted">
                        @if($order->user && $order->user->canManageOrders())
                            {{ $order->user->name }}
                        @else
                            <span class="badge bg-light text-secondary border">Online (Admin)</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            @foreach($order->items as $item)
                                <div class="small">
                                    <span class="fw-semibold text-dark">{{ $item->product->name ?? 'Produk dihapus' }}</span>
                                    <span class="text-muted">&times; {{ $item->quantity }}</span>
                                    <span class="text-muted">(@ Rp {{ number_format($item->price, 0, ',', '.') }})</span>
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <span class="fw-bold text-success fs-6">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @if($order->status === 'canceled')
                            <span class="badge-soft badge-soft-slate">Dibatalkan</span>
                        @else
                            <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm fw-semibold" style="font-size: 0.8rem; border-radius: 8px;" onchange="if (this.value === 'canceled') { let r = prompt('Ketik alasan / penyebab transaksi dibatalkan (akan dilihat pelanggan):', ''); if (r === null) { this.value = '{{ $order->status }}'; return; } let inp = this.form.querySelector('input[name=cancellation_reason]'); if (!inp) { inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'cancellation_reason'; this.form.appendChild(inp); } inp.value = r; } this.form.submit()">
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>{{ ($order->payment_method ?? 'cod') === 'cod' ? 'Diproses' : 'Lunas' }}</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="canceled">Batalkan</option>
                                </select>
                            </form>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('orders.show', $order->id) }}" class="btn-outline-soft btn-action-sm text-decoration-none" title="Buka faktur / nota penjualan">
                            <i class="bi bi-receipt"></i>
                            <span>Buka Faktur</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                        <span class="fw-semibold">Belum ada transaksi penjualan yang cocok dengan filter.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-5">
    {{ $orders->links() }}
</div>
@endsection
