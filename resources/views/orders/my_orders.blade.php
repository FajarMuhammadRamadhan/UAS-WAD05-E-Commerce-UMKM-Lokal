@extends('layouts.app')

@section('title', 'Pesanan Saya - WarungKita')

@section('content')
<div class="row align-items-center mb-4 g-2">
    <div class="col-lg-6">
        <h3 class="section-title mb-1">
            <i class="bi bi-bag-check me-2 text-primary"></i>Pesanan Saya
        </h3>
        <p class="section-subtitle mb-0">Pantau status pesanan belanja online dan riwayat pembelian Anda.</p>
    </div>
    <div class="col-lg-6 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
        <a href="{{ route('orders.checkout') }}" class="btn-emerald-brand text-decoration-none">
            <i class="bi bi-cart-plus"></i>
            <span>Belanja Produk Baru</span>
        </a>
        <a href="{{ route('products.index') }}" class="btn-outline-soft text-decoration-none">
            <i class="bi bi-arrow-left"></i>
            <span>Lihat Katalog</span>
        </a>
    </div>
</div>

<!-- Tampilan Desktop: Tabel Lengkap -->
<div class="d-none d-md-block modern-card p-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>No. Faktur</th>
                    <th>Tanggal Pemesanan</th>
                    <th>Rincian Barang</th>
                    <th>Total Belanja</th>
                    <th>Metode Pembayaran</th>
                    <th>Status Pesanan</th>
                    <th class="text-center" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('orders.show_customer', $order->id) }}" class="fw-bold text-decoration-none text-primary">
                            {{ $order->invoice_number }}
                        </a>
                    </td>
                    <td class="small text-muted">
                        <div>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                        <div class="text-muted small">{{ $order->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            @foreach($order->items as $item)
                                <div class="small">
                                    <span class="fw-semibold text-dark">{{ $item->product->name ?? 'Produk' }}</span>
                                    <span class="text-muted">&times; {{ $item->quantity }}</span>
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
                        <span class="badge-soft badge-soft-slate text-uppercase" style="font-size: 0.7rem;">
                            {{ $order->payment_method ?? 'COD' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($order->status) {
                                'pending' => 'badge-soft-amber',
                                'paid' => 'badge-soft-blue',
                                'completed' => 'badge-soft-emerald',
                                'canceled' => 'badge-soft-rose',
                                default => 'badge-soft-slate'
                            };
                            $statusText = match($order->status) {
                                'pending' => 'Menunggu Konfirmasi',
                                'paid' => 'Diproses / Lunas',
                                'completed' => 'Selesai Diterima',
                                'canceled' => 'Dibatalkan',
                                default => ucfirst($order->status)
                            };
                        @endphp
                        <span class="badge-soft {{ $badgeClass }}">
                            @if($order->status === 'pending')
                                <i class="bi bi-clock-history me-1"></i>
                            @elseif($order->status === 'completed')
                                <i class="bi bi-check-circle-fill me-1"></i>
                            @endif
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('orders.show_customer', $order->id) }}" class="btn-outline-soft btn-action-sm text-decoration-none" title="Lihat detail pesanan">
                            <i class="bi bi-receipt"></i>
                            <span>Detail</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Pesanan</h6>
                        <p class="small text-muted mb-3">Anda belum pernah melakukan pemesanan online di WarungKita.</p>
                        <a href="{{ route('orders.checkout') }}" class="btn-emerald-brand text-decoration-none">
                            <i class="bi bi-cart-plus me-1"></i> Belanja Sekarang
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tampilan Khusus Mobile / HP: Kartu Pesanan Ringkas & Responsif -->
<div class="d-md-none d-flex flex-column gap-3 mb-4">
    @forelse($orders as $order)
    @php
        $badgeClass = match($order->status) {
            'pending' => 'badge-soft-amber',
            'paid' => 'badge-soft-blue',
            'completed' => 'badge-soft-emerald',
            'canceled' => 'badge-soft-rose',
            default => 'badge-soft-slate'
        };
        $statusText = match($order->status) {
            'pending' => 'Menunggu Konfirmasi',
            'paid' => 'Diproses / Lunas',
            'completed' => 'Selesai Diterima',
            'canceled' => 'Dibatalkan',
            default => ucfirst($order->status)
        };
    @endphp
    <div class="modern-card p-3 border shadow-sm">
        <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
            <div>
                <span class="text-muted small d-block" style="font-size: 0.7rem; text-transform: uppercase;">No. Faktur</span>
                <a href="{{ route('orders.show_customer', $order->id) }}" class="fw-bold text-dark text-decoration-none">
                    {{ $order->invoice_number }}
                </a>
            </div>
            <span class="badge-soft {{ $badgeClass }}" style="font-size: 0.72rem;">
                {{ $statusText }}
            </span>
        </div>

        <div class="mb-2 text-muted small" style="font-size: 0.8rem;">
            <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB &bull; 
            <span class="text-uppercase fw-semibold text-dark">{{ $order->payment_method ?? 'COD' }}</span>
        </div>

        <div class="p-2 bg-light rounded-3 mb-3 small">
            <div class="text-muted mb-1 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase;">Rincian Barang:</div>
            @foreach($order->items as $item)
                <div class="d-flex justify-content-between text-dark py-0.5">
                    <span>{{ $item->product->name ?? 'Produk' }}</span>
                    <span class="text-muted fw-semibold">&times; {{ $item->quantity }}</span>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
            <div>
                <span class="text-muted small d-block" style="font-size: 0.7rem;">Total Belanja:</span>
                <span class="fw-bold text-success fs-6">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
            <a href="{{ route('orders.show_customer', $order->id) }}" class="btn btn-sm btn-dark-brand px-3">
                <i class="bi bi-receipt me-1"></i> Buka Nota
            </a>
        </div>
    </div>
    @empty
    <div class="modern-card p-4 text-center text-muted">
        <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
        <h6 class="fw-bold text-dark">Belum Ada Pesanan</h6>
        <p class="small text-muted mb-3">Anda belum pernah melakukan pemesanan online di WarungKita.</p>
        <a href="{{ route('orders.checkout') }}" class="btn-emerald-brand text-decoration-none">
            <i class="bi bi-cart-plus me-1"></i> Belanja Sekarang
        </a>
    </div>
    @endforelse
</div>

<div class="mb-5">
    {{ $orders->links() }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentHash = null;

    // Auto-sync status pesanan secara real-time di layar pelanggan
    setInterval(function() {
        fetch("{{ route('orders.customer_check_updates') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) return null;
            return res.json();
        })
        .then(data => {
            if (!data) return;
            if (currentHash === null) {
                currentHash = data.hash;
                return;
            }
            if (data.hash && data.hash !== currentHash) {
                // Admin mengubah status pesanan (misal: Diproses, Selesai, atau Dibatalkan)
                // Reload secara instan agar tabel status pelanggan langsung ter-update
                window.location.reload();
            }
        })
        .catch(() => {});
    }, 2500);
});
</script>
@endsection

