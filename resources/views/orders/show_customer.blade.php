@extends('layouts.app')

@section('title', 'Nota Belanja ' . $order->invoice_number)

@section('content')
<style>
/* Hilangkan header & footer browser otomatis (URL, IP Publik, tanggal, nomor halaman) */
@page {
    size: auto;
    margin: 0mm;
}
@media print {
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
    }
    body * {
        visibility: hidden;
    }
    #printableInvoice, #printableInvoice * {
        visibility: visible;
    }
    #printableInvoice {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0 !important;
        padding: 15mm 20mm !important;
        border: none !important;
        box-shadow: none !important;
        background: #ffffff !important;
    }
    .no-print {
        display: none !important;
    }
}
.payment-card {
    border: 2px dashed #059669;
    background: #f0fdf4;
    border-radius: 16px;
}
.qris-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    display: inline-block;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Tombol Aksi Atas (Sembunyi saat cetak) -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
            <a href="{{ route('orders.my_orders') }}" class="btn-outline-soft text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Pesanan Saya
            </a>
            <div class="d-flex gap-2 flex-wrap">
                @if($order->status === 'pending')
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#changePaymentModal">
                    <i class="bi bi-arrow-left-right me-1"></i> Ganti Metode
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#customerCancelModal">
                    <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                </button>
                @endif
                @php
                    $waText = urlencode("Halo WarungKita, saya ingin konfirmasi pesanan *{$order->invoice_number}* atas nama *{$order->customer_name}* total *Rp " . number_format($order->total_price, 0, ',', '.') . "*. Mohon bantuannya ya!");
                @endphp
                <a href="https://wa.me/6281234567890?text={{ $waText }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Toko
                </a>
                <button onclick="window.print()" class="btn-dark-brand btn-sm text-decoration-none">
                    <i class="bi bi-printer me-1"></i> Cetak / Simpan Nota
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi Pembayaran -->
        @if(session('success'))
        <div class="alert alert-success rounded-4 shadow-sm mb-4 no-print d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        <!-- Tracker Status Pesanan -->
        <div class="modern-card p-4 mb-4 no-print">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-truck me-1 text-primary"></i> Status Pengiriman & Pembayaran
            </h6>

            @php
                $isCanceled = $order->status === 'canceled';
                $isPending = $order->status === 'pending';
                $isPaid = $order->status === 'paid';
                $isCompleted = $order->status === 'completed';
                $isCod = ($order->payment_method ?? 'cod') === 'cod';

                // Penyesuaian teks alur untuk COD dan Non-COD
                if ($isCod) {
                    $step1Title = '1. Pesanan Diterima';
                    $step1Subtitle = 'Pesanan Diterima Warung';

                    $step2Title = '2. Diproses';
                    $step2Subtitle = 'Sedang Disiapkan & Dikemas'; // Hapus kata "Lunas" untuk COD

                    $step3Title = '3. Selesai';
                    $step3Subtitle = 'Bayar Tunai & Diterima';
                } else {
                    $step1Title = '1. Diterima';
                    $step1Subtitle = $isPending ? 'Menunggu Pembayaran' : 'Pembayaran Terkonfirmasi';

                    $step2Title = '2. Diproses';
                    $step2Subtitle = 'Lunas & Disiapkan';

                    $step3Title = '3. Selesai';
                    $step3Subtitle = 'Pesanan Selesai Diterima';
                }
            @endphp

            @if($isCanceled)
            <!-- Notifikasi Pesanan Dibatalkan -->
            <div class="alert alert-danger mb-0 rounded-4 p-3 border border-danger-subtle shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper" style="background: #fee2e2; color: #dc2626; width: 44px; height: 44px;">
                        <i class="bi bi-x-octagon-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-danger mb-1">Pesanan Ini Telah Dibatalkan</h6>
                        @if($order->cancellation_reason)
                            <div class="small text-dark mb-1">
                                <strong>Penyebab Dibatalkan:</strong> <span class="text-danger fst-italic">"{{ $order->cancellation_reason }}"</span>
                            </div>
                        @endif
                        <div class="small text-muted">Seluruh stok produk telah dikembalikan ke inventaris toko WarungKita.</div>
                    </div>
                </div>
            </div>
            @else
            <div class="position-relative text-center px-2 pt-3 pb-2">
                <!-- Garis Background Abu-abu -->
                <div class="position-absolute bg-secondary-subtle" style="top: 36px; left: 16%; width: 68%; height: 4px; border-radius: 99px; z-index: 1;"></div>
                <!-- Garis Progres Hijau -->
                <div class="position-absolute bg-success" style="top: 36px; left: 16%; height: 4px; border-radius: 99px; z-index: 1; width: {{ $isCompleted ? '68%' : ($isPaid ? '34%' : '0%') }}; transition: width 0.4s ease;"></div>

                <div class="row g-0 position-relative" style="z-index: 2;">
                    <!-- Step 1 -->
                    <div class="col-4 px-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $isPending || $isPaid || $isCompleted ? 'bg-success text-white shadow-sm' : 'bg-light text-muted border' }}" style="width: 42px; height: 42px; font-size: 1.15rem; border: 3px solid #ffffff;">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="fw-bold text-dark small" style="font-size: 0.85rem;">{{ $step1Title }}</div>
                        <div class="text-muted mt-1" style="font-size: 0.75rem; line-height: 1.3;">{{ $step1Subtitle }}</div>
                    </div>

                    <!-- Step 2 -->
                    <div class="col-4 px-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $isPaid || $isCompleted ? 'bg-success text-white shadow-sm' : 'bg-white text-muted border border-2' }}" style="width: 42px; height: 42px; font-size: 1.15rem; border: 3px solid #ffffff;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="fw-bold {{ $isPaid || $isCompleted ? 'text-dark' : 'text-muted' }} small" style="font-size: 0.85rem;">{{ $step2Title }}</div>
                        <div class="text-muted mt-1" style="font-size: 0.75rem; line-height: 1.3;">{{ $step2Subtitle }}</div>
                    </div>

                    <!-- Step 3 -->
                    <div class="col-4 px-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $isCompleted ? 'bg-success text-white shadow-sm' : 'bg-white text-muted border border-2' }}" style="width: 42px; height: 42px; font-size: 1.15rem; border: 3px solid #ffffff;">
                            <i class="bi bi-check2-all"></i>
                        </div>
                        <div class="fw-bold {{ $isCompleted ? 'text-dark' : 'text-muted' }} small" style="font-size: 0.85rem;">{{ $step3Title }}</div>
                        <div class="text-muted mt-1" style="font-size: 0.75rem; line-height: 1.3;">{{ $step3Subtitle }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Instruksi Pembayaran Khusus Transfer / QRIS (Jika masih Pending) -->
        @if($isPending && in_array($order->payment_method, ['transfer', 'qris']))
        <div class="payment-card p-4 mb-4 no-print text-center">
            @if($order->payment_method === 'transfer')
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: #ecfdf5; color: #059669; width: 48px; height: 48px;">
                    <i class="bi bi-bank fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Instruksi Pembayaran Transfer Bank</h5>
                <p class="text-muted small mb-3">Silakan transfer nominal pas ke salah satu rekening resmi WarungKita UMKM berikut:</p>

                <div class="row g-2 justify-content-center mb-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded-3 border">
                            <span class="badge bg-primary-subtle text-primary mb-1">BCA</span>
                            <div class="fw-bold fs-5 text-dark">8830-1928-33</div>
                            <small class="text-muted">a.n WarungKita UMKM</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded-3 border">
                            <span class="badge bg-warning-subtle text-dark mb-1">Mandiri</span>
                            <div class="fw-bold fs-5 text-dark">132-00-9876543-1</div>
                            <small class="text-muted">a.n WarungKita UMKM</small>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-3 border d-inline-block px-4 mb-3">
                    <span class="text-muted small d-block">Total yang Harus Ditransfer:</span>
                    <span class="fw-bold text-success fs-4">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            @elseif($order->payment_method === 'qris')
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px;">
                    <i class="bi bi-qr-code-scan fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Scan QRIS WarungKita</h5>
                <p class="text-muted small mb-3">Buka aplikasi mobile banking atau e-wallet (GoPay, OVO, Dana, LinkAja, BCA, dll) lalu scan QR code berikut:</p>

                <div class="qris-box mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=WarungKita-{{ $order->invoice_number }}-Rp{{ $order->total_price }}" alt="QRIS WarungKita" class="img-fluid" style="width: 180px; height: 180px;">
                    <div class="fw-bold text-dark mt-2 small">QRIS Standar Pembayaran Nasional</div>
                </div>

                <div class="p-2 bg-white rounded-3 border d-inline-block px-4 mb-3">
                    <span class="text-muted small d-block">Total Pembayaran QRIS:</span>
                    <span class="fw-bold text-success fs-4">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            @endif

            <form action="{{ route('orders.confirm_payment', $order->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Apakah Anda sudah menyelesaikan pembayaran sebesar Rp {{ number_format($order->total_price, 0, ',', '.') }}?')">
                @csrf
                <button type="submit" class="btn btn-success btn-lg px-4 py-2 rounded-pill shadow-sm fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i> Saya Sudah Selesai Bayar!
                </button>
            </form>
            <div class="text-muted small mt-2">
                <i class="bi bi-shield-check text-success me-1"></i> Setelah klik tombol di atas, Admin warung akan langsung menerima notifikasi pembayaran Anda.
            </div>

            <!-- Tombol Aksi: Ganti Metode & Batalkan Pesanan -->
            <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center justify-content-center gap-2">
                <span class="small text-muted"><i class="bi bi-question-circle me-1"></i>Opsi Pesanan:</span>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#changePaymentModal">
                    <i class="bi bi-arrow-left-right me-1"></i> Ganti Metode Pembayaran
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#customerCancelModal">
                    <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                </button>
            </div>
        </div>
        @endif

        @if($isPaid)
        <div class="alert alert-success rounded-4 shadow-sm mb-4 no-print d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                <div>
                    <h6 class="fw-bold text-success mb-0">Pembayaran Terkonfirmasi & Lunas!</h6>
                    <small class="text-muted">Admin warung telah menerima notifikasi pembayaran Anda dan sedang menyiapkan pesanan untuk dikirim.</small>
                </div>
            </div>
            <span class="badge bg-success px-3 py-2">LUNAS</span>
        </div>
        @endif

        @if($order->payment_method === 'cod' && $isPending)
        <div class="alert alert-info rounded-4 shadow-sm mb-4 no-print p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-cash-coin fs-2 text-primary"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Metode Pembayaran: COD (Cash on Delivery)</h6>
                        <small class="text-muted">Silakan siapkan uang tunai sebesar <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong> saat pesanan tiba di alamat Anda.</small>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold bg-white shadow-sm" data-bs-toggle="modal" data-bs-target="#changePaymentModal">
                        <i class="bi bi-arrow-left-right me-1"></i> Ganti Metode
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold bg-white shadow-sm" data-bs-toggle="modal" data-bs-target="#customerCancelModal">
                        <i class="bi bi-x-circle me-1"></i> Batalkan
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Card Nota Faktur Resmi WarungKita (Bisa Dicetak) -->
        <div class="modern-card p-3 p-sm-4 p-md-5" id="printableInvoice">
            <!-- Header Nota -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon" style="width: 44px; height: 44px; font-size: 1.3rem;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div>
                        <div class="brand-title" style="font-size: 1.4rem;">WarungKita</div>
                        <small class="text-muted d-block">Inventaris & Belanja Online UMKM</small>
                    </div>
                </div>

                <div class="text-end">
                    <span class="stat-label text-muted d-block small" style="letter-spacing: 0.05em;">NOTA PENJUALAN</span>
                    <div class="fw-bold text-dark fs-5">{{ $order->invoice_number }}</div>
                    @php
                        $badgeStyle = match($order->status) {
                            'completed' => 'badge-soft-emerald',
                            'paid' => 'badge-soft-blue',
                            'pending' => 'badge-soft-amber',
                            'canceled' => 'badge-soft-rose',
                            default => 'badge-soft-slate'
                        };
                    @endphp
                    <span class="badge-soft {{ $badgeStyle }}">
                        {{ $order->getStatusLabel() }}
                    </span>
                </div>
            </div>

            <!-- Meta Data Nota (Pelanggan, Alamat, Tanggal, Pembayaran) -->
            <div class="row g-3 p-3 bg-light rounded-4 mb-4 border small">
                <div class="col-sm-6">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Dipesan Oleh:</div>
                    <div class="fw-bold text-dark fs-6">{{ $order->customer_name }}</div>
                    <div class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->customer_phone ?? '-' }}</div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Waktu Pemesanan:</div>
                    <div class="fw-bold text-dark">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                    <div class="text-muted">
                        Metode: <strong class="text-uppercase text-dark">{{ $order->payment_method ?? 'COD' }}</strong>
                        @if($isPending)
                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-decoration-none no-print" data-bs-toggle="modal" data-bs-target="#changePaymentModal" title="Ganti Metode Pembayaran">
                                <i class="bi bi-arrow-left-right"></i> Ubah
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-12 border-top pt-2 mt-2">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Alamat Pengantaran:</div>
                    <div class="text-dark">{{ $order->customer_address ?? 'Ambil di Tempat / Toko WarungKita' }}</div>
                    @if($order->notes)
                        <div class="text-muted mt-1"><em>Catatan: {{ $order->notes }}</em></div>
                    @endif
                </div>
            </div>

            <!-- Tabel Daftar Produk Yang Dipesan -->
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>Nama Produk</th>
                            <th class="text-end" style="width: 140px;">Harga Satuan</th>
                            <th class="text-center" style="width: 90px;">Jumlah</th>
                            <th class="text-end" style="width: 140px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <strong class="text-dark">{{ $item->product->name ?? 'Produk Dihapus' }}</strong>
                                @if(optional($item->product->category)->name)
                                    <div class="text-muted" style="font-size: 0.75rem;">Kategori: {{ $item->product->category->name }}</div>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }} pcs</td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light border-top">
                            <td colspan="4" class="text-end fw-bold fs-6">Total Pembayaran:</td>
                            <td class="text-end fw-bold text-success fs-5">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer Nota -->
            <div class="p-3 bg-light rounded-4 text-center text-muted small border">
                <div class="fw-semibold text-dark mb-1">Terima Kasih Telah Berbelanja di WarungKita UMKM!</div>
                <div>Nota ini adalah bukti sah transaksi pemesanan belanja online. Simpan nota ini untuk pengecekan saat kurir tiba.</div>
            </div>
        </div>
    </div>
</div>

@if($isPending)
<!-- Modal Ganti Metode Pembayaran -->
<div class="modal fade" id="changePaymentModal" tabindex="-1" aria-labelledby="changePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="changePaymentModalLabel">Ganti Metode Pembayaran</h5>
                        <small class="text-muted">Pilih metode pembayaran yang Anda inginkan</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.change_payment_method', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body py-3">
                    <div class="alert alert-light border small text-muted mb-3 rounded-3">
                        Nomor Pesanan: <strong class="text-dark">{{ $order->invoice_number }}</strong><br>
                        Total Tagihan: <strong class="text-success fs-6">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <label class="border rounded-3 p-3 d-flex align-items-start gap-3 cursor-pointer position-relative {{ $order->payment_method === 'cod' ? 'border-success bg-success-subtle bg-opacity-10' : 'bg-white' }}" style="cursor: pointer;">
                            <input type="radio" name="payment_method" value="cod" class="form-check-input mt-1" {{ $order->payment_method === 'cod' ? 'checked' : '' }} required>
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-cash-stack text-success fs-5"></i> COD (Bayar Tunai di Tempat)
                                </div>
                                <small class="text-muted d-block mt-1">Bayar uang tunai langsung ke kurir saat pesanan tiba di alamat Anda.</small>
                            </div>
                        </label>

                        <label class="border rounded-3 p-3 d-flex align-items-start gap-3 cursor-pointer position-relative {{ $order->payment_method === 'transfer' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'bg-white' }}" style="cursor: pointer;">
                            <input type="radio" name="payment_method" value="transfer" class="form-check-input mt-1" {{ $order->payment_method === 'transfer' ? 'checked' : '' }} required>
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-bank text-primary fs-5"></i> Transfer Bank Manual
                                </div>
                                <small class="text-muted d-block mt-1">Transfer langsung ke rekening resmi BCA / Mandiri WarungKita.</small>
                            </div>
                        </label>

                        <label class="border rounded-3 p-3 d-flex align-items-start gap-3 cursor-pointer position-relative {{ $order->payment_method === 'qris' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'bg-white' }}" style="cursor: pointer;">
                            <input type="radio" name="payment_method" value="qris" class="form-check-input mt-1" {{ $order->payment_method === 'qris' ? 'checked' : '' }} required>
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-qr-code-scan text-dark fs-5"></i> QRIS (Gopay / OVO / Dana / BCA)
                                </div>
                                <small class="text-muted d-block mt-1">Scan kode barcode QRIS via aplikasi e-wallet atau mobile banking apa saja.</small>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark-brand rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Batalkan Pesanan oleh Pelanggan -->
<div class="modal fade" id="customerCancelModal" tabindex="-1" aria-labelledby="customerCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0" id="customerCancelModalLabel">Batalkan Pesanan Saya</h5>
                        <small class="text-muted">Nomor Pesanan: {{ $order->invoice_number }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.cancel_customer', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body py-3">
                    <div class="alert alert-warning border small text-dark mb-3 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                        Apakah Anda yakin ingin membatalkan pesanan belanja ini? Stok produk akan otomatis dikembalikan ke inventaris toko.
                    </div>

                    <div class="mb-3">
                        <label for="customer_cancellation_reason" class="form-label small fw-bold text-dark">
                            Penyebab / Alasan Pembatalan <span class="text-danger">*</span>:
                        </label>
                        <textarea name="cancellation_reason" id="customer_cancellation_reason" class="form-control" rows="3" placeholder="Ketik alasan mengapa Anda membatalkan pesanan ini (misal: Ingin menambah barang lain, salah pilih produk, ganti alamat, dll)..." required autofocus></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Kembali</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-x-circle me-1"></i> Ya, Batalkan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
window.addEventListener('beforeprint', function() {
    document.title = "Nota-{{ $order->invoice_number }}";
});

@if(!in_array($order->status, ['completed', 'canceled']))
// Auto-sync status pesanan dari Admin ke layar pelanggan secara real-time
document.addEventListener('DOMContentLoaded', function() {
    let currentStatus = "{{ $order->status }}";
    const statusUrl = "{{ route('orders.check_status', $order->id) }}";

    setInterval(function() {
        fetch(statusUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) return null;
            return response.json();
        })
        .then(data => {
            if (data && data.status && data.status !== currentStatus) {
                // Status diubah oleh Admin (misal: Pending -> Diproses atau Diproses -> Selesai)
                // Reload halaman otomatis agar layar pelanggan langsung ter-update secara instan
                window.location.reload();
            }
        })
        .catch(() => {});
    }, 2000); // Polling setiap 2 detik
});
@endif
@if($order->status === 'canceled')
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Pesanan Dibatalkan',
            text: "{{ $order->cancellation_reason ?? 'Pesanan telah dibatalkan.' }}",
            confirmButtonColor: '#0f172a',
            confirmButtonText: 'Tutup'
        });
    }
});
@endif
</script>
@endsection
