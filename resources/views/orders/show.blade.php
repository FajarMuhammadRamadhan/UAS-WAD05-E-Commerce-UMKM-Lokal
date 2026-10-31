@extends('layouts.app')

@section('title', $order->invoice_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Card Faktur / Nota -->
        <div class="modern-card p-3 p-sm-4 p-md-5" id="printableInvoice">
            <!-- Header Faktur -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4 invoice-header">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="brand-icon" style="width: 38px; height: 38px; font-size: 1.1rem;">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div>
                            <span class="brand-title" style="font-size: 1.35rem;">WarungKita</span>
                            <span class="brand-badge ms-1">UMKM</span>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <span class="stat-label text-muted d-block" style="font-size: 0.75rem;">FAKTUR PENJUALAN</span>
                    <div class="fw-bold text-dark fs-5 mb-1">{{ $order->invoice_number }}</div>
                    @php
                        $isCod = ($order->payment_method ?? 'cod') === 'cod';
                        $badgeStyle = match($order->status) {
                            'completed' => 'badge-soft-emerald',
                            'paid' => 'badge-soft-blue',
                            'pending' => 'badge-soft-amber',
                            'canceled' => 'badge-soft-rose',
                            default => 'badge-soft-slate'
                        };
                        $statusLabel = match($order->status) {
                            'completed' => ($isCod ? 'Selesai' : 'Selesai & Lunas'),
                            'paid' => ($isCod ? 'Diproses' : 'Lunas'),
                            'pending' => 'Pending',
                            'canceled' => 'Dibatalkan',
                            default => ucfirst($order->status)
                        };

                        // Tentukan nama Kasir / Petugas Toko (bukan nama pelanggan pemesan)
                        if ($order->user && $order->user->canManageOrders()) {
                            $staffName = $order->user->name;
                        } elseif (Auth::check() && Auth::user()->canManageOrders()) {
                            $staffName = Auth::user()->name;
                        } else {
                            $staffName = 'Petugas WarungKita';
                        }
                    @endphp
                    <span class="badge-soft {{ $badgeStyle }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <!-- Informasi Transaksi & Pelanggan (Simetris Responsif di HP & Cetak) -->
            <div class="row g-2 p-3 bg-light rounded-3 mb-4 invoice-meta border align-items-center">
                <div class="col-12 col-sm-4 text-center border-bottom border-sm-bottom-0 border-sm-end pb-2 pb-sm-0 mb-1 mb-sm-0 pe-sm-2">
                    <span class="stat-label text-muted d-block small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Nama Pelanggan</span>
                    <span class="fw-bold text-dark fs-6 mt-1 d-block">{{ $order->customer_name ?? 'Pelanggan Umum' }}</span>
                </div>
                <div class="col-12 col-sm-4 text-center border-bottom border-sm-bottom-0 border-sm-end pb-2 pb-sm-0 mb-1 mb-sm-0 px-sm-2">
                    <span class="stat-label text-muted d-block small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Kasir / Petugas</span>
                    <span class="fw-semibold text-dark fs-6 mt-1 d-block">{{ $staffName }}</span>
                </div>
                <div class="col-12 col-sm-4 text-center ps-sm-2">
                    <span class="stat-label text-muted d-block small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Tanggal & Waktu</span>
                    <span class="fw-semibold text-dark fs-6 mt-1 d-block">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            @if($order->customer_phone || $order->customer_address || $order->payment_method)
            <!-- Detail Informasi Pesanan Online & Pengiriman -->
            <div class="p-3 bg-light rounded-4 mb-4 border small">
                <div class="row g-2">
                    <div class="col-md-4">
                        <span class="text-muted d-block">No. WhatsApp / Telepon:</span>
                        <strong class="text-dark">{{ $order->customer_phone ?? '-' }}</strong>
                        @if($order->customer_phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}" target="_blank" class="small text-success text-decoration-none d-block mt-1">
                            <i class="bi bi-whatsapp"></i> Chat WhatsApp
                        </a>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Metode Pembayaran:</span>
                        <span class="badge bg-secondary-subtle text-dark text-uppercase px-2 py-1 mt-1">{{ $order->payment_method ?? 'COD' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Status Pembayaran:</span>
                        <span class="badge {{ $order->status === 'paid' || $order->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1 mt-1">
                            {{ $order->getStatusLabel() }}
                        </span>
                    </div>
                    @if($order->customer_address)
                    <div class="col-12 border-top pt-2 mt-2">
                        <span class="text-muted d-block">Alamat Pengantaran:</span>
                        <strong class="text-dark">{{ $order->customer_address }}</strong>
                        @if($order->notes)
                            <div class="text-muted mt-1"><em>Catatan Pelanggan: {{ $order->notes }}</em></div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Tabel Rincian Belanja -->
            <div class="modern-table-container mb-4 invoice-table-wrapper">
                <table class="modern-table invoice-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end" style="width: 150px;">Harga Satuan</th>
                            <th class="text-end" style="width: 160px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product->name ?? 'Produk dihapus' }}</div>
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $item->product->category->name ?? '-' }}</span>
                            </td>
                            <td class="text-center fw-semibold text-dark">{{ $item->quantity }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #475569; background: #f8fafc;">
                            <td colspan="6" class="text-end py-3 px-3">
                                <span class="fw-bold text-dark fs-6 me-2">Total Pembayaran:</span>
                                <span class="fs-4 fw-bold text-success">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Catatan Kaki & Tanda Tangan untuk Cetak Nota -->
            <div class="invoice-footer border-top pt-3 mt-3">
                <div class="row align-items-center mb-3">
                    <div class="col-8 col-sm-8">
                        <div class="small fw-semibold text-dark mb-1">Catatan Penting:</div>
                        <ul class="text-muted small ps-3 mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            <li>Terima kasih telah berbelanja di <strong>WarungKita</strong>.</li>
                            <li>Barang yang sudah dibeli dapat ditukar maksimal 1x24 jam dengan membawa nota ini.</li>
                            <li>Faktur ini merupakan bukti pembayaran yang sah dan terdata dalam sistem.</li>
                        </ul>
                    </div>
                    <div class="col-4 col-sm-4 text-center mt-3 mt-sm-0 print-signature">
                        <div class="small text-muted mb-5">Hormat Kami,</div>
                        <div class="border-top pt-1 fw-bold text-dark small" style="width: 140px; margin: 0 auto;">
                            {{ $staffName }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontrol Status Transaksi (Hanya Layar, Tersembunyi Saat Dicetak) -->
            <div class="p-3 bg-light rounded-4 mb-4 d-print-none border">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="small text-muted d-flex align-items-center gap-1">
                        <i class="bi bi-info-circle text-primary"></i>
                        <span>Status saat ini: <strong class="text-dark">{{ $statusLabel }}</strong></span>
                    </div>
                    @if($order->status !== 'canceled')
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST" class="m-0 d-flex align-items-center gap-2" id="updateStatusForm">
                            @csrf
                            @method('PATCH')
                            <label for="order-update-status" class="form-label mb-0 small fw-semibold text-dark">Ubah Status:</label>
                            <select name="status" id="order-update-status" class="form-select form-select-sm" style="max-width: 160px; font-size: 0.85rem;" onchange="if (this.value === 'canceled') { this.value = '{{ $order->status }}'; const modal = new bootstrap.Modal(document.getElementById('adminCancelModal')); modal.show(); }">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>{{ $isCod ? 'Diproses' : 'Lunas' }}</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="canceled">Batalkan...</option>
                            </select>
                            <button type="submit" class="btn-dark-brand btn-sm">Perbarui</button>
                        </form>

                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#adminCancelModal">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                        </button>
                    </div>
                    @else
                    <div class="text-danger small fw-semibold">
                        <i class="bi bi-x-circle-fill me-1"></i> Transaksi dibatalkan.
                        @if($order->cancellation_reason)
                            <div class="text-dark fw-normal mt-1">
                                <strong>Penyebab Dibatalkan:</strong> <span class="text-danger fst-italic">"{{ $order->cancellation_reason }}"</span>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer Tombol Cetak & Kembali (Hanya Layar) -->
            <div class="d-flex justify-content-between align-items-center d-print-none pt-2 flex-wrap gap-2">
                <a href="{{ route('orders.index') }}" class="btn-outline-soft text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali ke Riwayat</span>
                </a>
                <button type="button" class="btn-dark-brand px-4" onclick="document.title='{{ $order->invoice_number }}'; window.print()">
                    <i class="bi bi-printer me-1"></i>
                    <span>Cetak Faktur</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Stylesheet Khusus Agar Faktur Sangat Rapi Sesuai Template -->
<style>
@media screen {
    .print-signature {
        display: none;
    }
}

@media print {
    @page {
        size: A4 portrait;
        margin: 0 !important; /* Menghilangkan header & footer otomatis browser (tanggal, jam, dan judul) */
    }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        box-sizing: border-box !important;
    }

    html, body {
        background-color: #ffffff !important;
        color: #0f172a !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 9.5pt !important;
        width: 100% !important;
    }

    .app-navbar,
    .d-print-none,
    main.container > .alert,
    button,
    .btn {
        display: none !important;
    }

    /* Padding halaman A4 yang aman dan proporsional agar border kartu tampak utuh di semua sisi */
    main.container {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 10mm 12mm !important;
        box-sizing: border-box !important;
    }

    .row.justify-content-center {
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
        width: 100% !important;
    }

    .row.justify-content-center > .col-lg-8 {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    #printableInvoice {
        border: 1px solid #cbd5e1 !important;
        border-radius: 14px !important;
        box-shadow: none !important;
        padding: 24px 28px !important;
        margin: 0 auto !important;
        background: #ffffff !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .invoice-header {
        border-bottom: 1.5px solid #e2e8f0 !important;
        padding-bottom: 16px !important;
        margin-bottom: 20px !important;
    }

    /* Menjaga 3 kolom data pelanggan, kasir, tanggal & waktu tetap sejajar horizontal */
    .invoice-meta {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 12px 6px !important;
        margin-bottom: 20px !important;
        width: 100% !important;
    }

    .invoice-meta > div,
    .invoice-meta .col-4 {
        flex: 0 0 33.333333% !important;
        max-width: 33.333333% !important;
        width: 33.333333% !important;
        display: block !important;
        padding: 0 6px !important;
    }

    .invoice-table-wrapper {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        overflow: hidden !important;
        margin-bottom: 18px !important;
    }

    .invoice-table th {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        border-bottom: 1.5px solid #cbd5e1 !important;
        font-weight: 700 !important;
        padding: 10px 12px !important;
        font-size: 8.5pt !important;
        text-transform: uppercase !important;
    }

    .invoice-table td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 10px 12px !important;
        font-size: 9pt !important;
    }

    .invoice-table tfoot td {
        background-color: #f8fafc !important;
        border-top: 1.5px solid #cbd5e1 !important;
        padding: 12px 14px !important;
    }

    /* Footer faktur: Catatan penting di kiri, tanda tangan di kanan sejajar */
    .invoice-footer {
        border-top: 1.5px solid #e2e8f0 !important;
        padding-top: 14px !important;
        margin-top: 14px !important;
    }

    .invoice-footer .row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        margin: 0 !important;
        width: 100% !important;
    }

    .invoice-footer .col-8,
    .invoice-footer .col-sm-8 {
        flex: 0 0 65% !important;
        max-width: 65% !important;
        width: 65% !important;
        display: block !important;
    }

    .invoice-footer .col-4,
    .invoice-footer .col-sm-4 {
        flex: 0 0 35% !important;
        max-width: 35% !important;
        width: 35% !important;
        display: block !important;
    }

    .print-signature {
        display: block !important;
    }
}
</style>

@if($order->status !== 'canceled')
<!-- Modal Batalkan Pesanan oleh Admin / Kasir -->
<div class="modal fade" id="adminCancelModal" tabindex="-1" aria-labelledby="adminCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0" id="adminCancelModalLabel">Batalkan Transaksi Pesanan</h5>
                        <small class="text-muted">Nomor Faktur: {{ $order->invoice_number }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="canceled">
                <div class="modal-body py-3">
                    <div class="alert alert-warning border small text-dark mb-3 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                        Membatalkan transaksi ini akan otomatis mengembalikan stok seluruh produk pesanan ke inventaris toko.
                    </div>

                    <div class="mb-3">
                        <label for="admin_cancellation_reason" class="form-label small fw-bold text-dark">
                            Penyebab / Catatan Pembatalan Pesanan <span class="text-danger">*</span>:
                        </label>
                        <textarea name="cancellation_reason" id="admin_cancellation_reason" class="form-control" rows="3" placeholder="Ketik alasan mengapa pesanan ini dibatalkan (misal: Stok barang di toko habis, alamat tidak dapat dijangkau, permintaan pembeli, dll)..." required autofocus></textarea>
                        <small class="text-muted" style="font-size: 0.72rem;">Catatan ini akan dilihat oleh pelanggan di notifikasi dan faktur mereka.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Kembali</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-x-circle me-1"></i> Ya, Batalkan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
window.addEventListener('beforeprint', function() {
    document.title = "{{ $order->invoice_number }}";
});
</script>
@endsection
