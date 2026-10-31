@extends('layouts.app')

@section('content')
@php
    $groupedList = isset($productsGrouped) ? $productsGrouped : $products->groupBy(function ($item) {
        return $item->category->name ?? 'Lainnya';
    })->sortKeys();
@endphp

<style>
.product-item-card {
    border: 1px solid #e2e8f0 !important;
    background-color: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.product-item-card:hover {
    border-color: #0f172a !important;
    background-color: #f8fafc !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px -2px rgba(15, 23, 42, 0.08);
}
.product-picker-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 1050;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.18), 0 6px 12px -4px rgba(15, 23, 42, 0.1);
    border: 1px solid #cbd5e1;
    padding: 12px;
}
.selected-product-card {
    border: 1px solid #cbd5e1 !important;
    background-color: #ffffff;
    border-radius: 12px;
    padding: 10px 14px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.selected-product-card:hover {
    border-color: #0f172a !important;
    background-color: #f8fafc;
}
.picker-trigger-btn {
    border: 1px solid #cbd5e1 !important;
    background-color: #ffffff;
    border-radius: 12px;
    padding: 12px 14px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.picker-trigger-btn:hover {
    border-color: #0f172a !important;
    background-color: #f8fafc;
}
.currency-amount {
    white-space: nowrap !important;
    font-variant-numeric: tabular-nums;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="modern-card p-3 p-sm-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Catat Transaksi Penjualan</h4>
                    <p class="text-muted small mb-0">Input belanja pelanggan dan sistem otomatis akan mengurangi stok barang terkait.</p>
                </div>
                <div class="stat-icon-wrapper" style="background: #ecfdf5; color: #059669;">
                    <i class="bi bi-cart-check fs-4"></i>
                </div>
            </div>

            <form action="{{ route('orders.store') }}" method="POST" id="order-form">
                @csrf

                <!-- Nama Pelanggan -->
                <div class="mb-4">
                    <label for="customer_name" class="form-label fw-semibold">Nama Pelanggan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0 @error('customer_name') is-invalid @enderror"
                               style="border-radius: 0 10px 10px 0;"
                               id="customer_name"
                               name="customer_name"
                               value="{{ old('customer_name') }}"
                               maxlength="100"
                               placeholder="Contoh: Budi Santoso / Pelanggan Umum"
                               required>
                    </div>
                    @error('customer_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Bagian Item Produk -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-box-seam me-1 text-primary"></i> Daftar Produk Yang Dibeli
                        </h6>
                        <small class="text-muted">Setiap produk berbentuk persegi panjang & jumlah kuantitas dikunci agar tidak melebihi stok.</small>
                    </div>
                    <button type="button" class="btn-outline-soft btn-sm" id="add-order-item">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Baris Produk</span>
                    </button>
                </div>

                @error('items')
                    <div class="alert alert-danger py-2 small rounded-3">{{ $message }}</div>
                @enderror

                <div id="order-items" class="d-grid gap-3 mb-4">
                    @foreach(old('items', [['product_id' => '', 'quantity' => 1]]) as $index => $oldItem)
                    @php
                        $selectedProduct = null;
                        if (!empty($oldItem['product_id'])) {
                            $selectedProduct = $products->firstWhere('id', (int)$oldItem['product_id']);
                        }
                    @endphp
                    <div class="order-item row g-2 align-items-start p-3 bg-light rounded-4 border mx-0">
                        <!-- Kolom Pemilihan Produk Berbentuk Persegi Panjang -->
                        <div class="col-md-6 position-relative product-picker-col">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0 fw-semibold">Pilih Produk</label>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary btn-reopen-picker btn-change-header {{ $selectedProduct ? '' : 'd-none' }}" style="font-size: 0.8rem;">
                                        <i class="bi bi-arrow-repeat me-1"></i>Ganti Produk
                                    </button>
                                    <span class="text-muted opacity-50 btn-sep-header {{ $selectedProduct ? '' : 'd-none' }}" style="font-size: 0.75rem;">•</span>
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-secondary btn-browse-catalog" style="font-size: 0.8rem;">
                                        <i class="bi bi-grid me-1"></i>Katalog Lengkap
                                    </button>
                                </div>
                            </div>

                            <!-- Tombol Pemicu Picker (Persegi Panjang Kosong) -->
                            <div class="picker-trigger-btn d-flex justify-content-between align-items-center {{ $selectedProduct ? 'd-none' : '' }}">
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <i class="bi bi-box-seam fs-5 text-primary"></i>
                                    <span class="fw-medium text-secondary">-- Klik untuk Pilih Produk --</span>
                                </div>
                                <span class="badge bg-light text-dark border" style="font-size: 0.725rem;">
                                    Buka Daftar &darr;
                                </span>
                            </div>

                            <!-- Kartu Persegi Panjang Saat Produk Terpilih (Bersih Tanpa Tombol di Tengah) -->
                            <div class="selected-product-card d-flex justify-content-between align-items-center {{ $selectedProduct ? '' : 'd-none' }} btn-reopen-picker" role="button" title="Klik untuk mengganti produk">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary-subtle border border-primary-subtle" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="bi bi-bag-check-fill text-primary fs-5"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-dark text-truncate product-selected-name" style="font-size: 0.925rem;">
                                            {{ $selectedProduct->name ?? '-' }}
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                            <span class="badge bg-light text-secondary border product-selected-category" style="font-size: 0.7rem;">
                                                {{ $selectedProduct->category->name ?? 'Lainnya' }}
                                            </span>
                                            <span class="fw-bold text-success currency-amount ms-1 product-selected-price" style="font-size: 0.8rem;">
                                                Rp {{ $selectedProduct ? number_format($selectedProduct->price, 0, ',', '.') : '0' }}
                                            </span>
                                            <span class="badge bg-emerald-subtle text-success border border-success-subtle ms-1 product-selected-stock" style="font-size: 0.7rem;">
                                                Stok: {{ $selectedProduct->stock ?? 0 }} pcs
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted ms-2 ps-2 border-start d-flex align-items-center" style="font-size: 0.8rem;">
                                    <i class="bi bi-chevron-down text-secondary"></i>
                                </div>
                            </div>

                            <!-- Dropdown Box Berisi Daftar Barang Persegi Panjang -->
                            <div class="product-picker-dropdown d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark">Daftar Produk WarungGenZ</span>
                                    <button type="button" class="btn-close btn-sm btn-close-picker" aria-label="Close"></button>
                                </div>

                                <!-- Kolom Cari di Dalam Dropdown -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="search" class="form-control border-start-0 picker-search-input" placeholder="Ketik nama produk...">
                                </div>

                                <!-- Filter Pill Kategori -->
                                <div class="d-flex flex-wrap gap-1 mb-2 pb-1 border-bottom">
                                    <button type="button" class="btn btn-xs btn-dark rounded-pill py-0 px-2 picker-cat-filter active" data-cat="" style="font-size: 0.72rem;">Semua</button>
                                    @foreach($groupedList as $categoryName => $prods)
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2 picker-cat-filter" data-cat="{{ strtolower($categoryName) }}" style="font-size: 0.72rem;">
                                            {{ $categoryName }}
                                        </button>
                                    @endforeach
                                </div>

                                <!-- List Barang Tiap Item Berbentuk Persegi Panjang -->
                                <div class="picker-item-list" style="max-height: 270px; overflow-y: auto;">
                                    @foreach($products as $prod)
                                    <div class="product-item-card p-2 px-3 mb-2 rounded-3 d-flex justify-content-between align-items-center"
                                         data-id="{{ $prod->id }}"
                                         data-name="{{ strtolower($prod->name) }}"
                                         data-raw-name="{{ $prod->name }}"
                                         data-category="{{ strtolower($prod->category->name ?? 'lainnya') }}"
                                         data-raw-category="{{ $prod->category->name ?? 'Lainnya' }}"
                                         data-price="{{ $prod->price }}"
                                         data-formatted-price="Rp {{ number_format($prod->price, 0, ',', '.') }}"
                                         data-stock="{{ $prod->stock }}">
                                        <div class="pe-2">
                                            <div class="fw-bold text-dark item-card-name" style="font-size: 0.875rem;">
                                                {{ $prod->name }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">
                                                    {{ $prod->category->name ?? 'Lainnya' }}
                                                </span>
                                                <span class="badge bg-emerald-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                                    Stok: {{ $prod->stock }} pcs
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end ps-2 currency-amount" style="white-space: nowrap;">
                                            <div class="fw-bold text-success currency-amount" style="font-size: 0.9rem;">
                                                Rp {{ number_format($prod->price, 0, ',', '.') }}
                                            </div>
                                            <span class="badge bg-primary text-white mt-1" style="font-size: 0.65rem;">
                                                Pilih &rarr;
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="picker-empty-notice text-center py-3 text-muted small d-none">
                                        <i class="bi bi-search d-block mb-1"></i> Produk tidak ditemukan
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden Select untuk Form Submit & Validasi Backend -->
                            <select class="form-select item-product d-none @error('items.'.$index.'.product_id') is-invalid @enderror"
                                    name="items[{{ $index }}][product_id]" required>
                                <option value="">-- Pilih Produk WarungGenZ --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}"
                                            data-price="{{ $prod->price }}"
                                            data-stock="{{ $prod->stock }}"
                                            data-category="{{ $prod->category->name ?? 'Lainnya' }}"
                                            data-name="{{ $prod->name }}"
                                            {{ ($oldItem['product_id'] ?? '') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('items.'.$index.'.product_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kolom Kuantitas Bersih Tanpa Tombol + dan - -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Jumlah (Qty)</label>
                            <input type="number"
                                   class="form-control item-quantity @error('items.'.$index.'.quantity') is-invalid @enderror"
                                   name="items[{{ $index }}][quantity]"
                                   value="{{ $oldItem['quantity'] ?? 1 }}"
                                   min="1"
                                   required>
                            <div class="qty-warning-msg small text-danger fw-semibold d-none mt-1" style="font-size: 0.72rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Maksimal stok tercapai!
                            </div>
                            @error('items.'.$index.'.quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kolom Subtotal -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Subtotal</label>
                            <div class="form-control-plaintext item-subtotal fw-bold text-success pt-2 pb-0 currency-amount">
                                Rp 0
                            </div>
                        </div>

                        <!-- Tombol Hapus Baris -->
                        <div class="col-md-1 mt-md-4 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 remove-order-item" title="Hapus baris">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Template baris produk baru -->
                <template id="order-item-template">
                    <div class="order-item row g-2 align-items-start p-3 bg-light rounded-4 border mx-0">
                        <div class="col-md-6 position-relative product-picker-col">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0 fw-semibold">Pilih Produk</label>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary btn-reopen-picker btn-change-header d-none" style="font-size: 0.8rem;">
                                        <i class="bi bi-arrow-repeat me-1"></i>Ganti Produk
                                    </button>
                                    <span class="text-muted opacity-50 btn-sep-header d-none" style="font-size: 0.75rem;">•</span>
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-secondary btn-browse-catalog" style="font-size: 0.8rem;">
                                        <i class="bi bi-grid me-1"></i>Katalog Lengkap
                                    </button>
                                </div>
                            </div>

                            <div class="picker-trigger-btn d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <i class="bi bi-box-seam fs-5 text-primary"></i>
                                    <span class="fw-medium text-secondary">-- Klik untuk Pilih Produk --</span>
                                </div>
                                <span class="badge bg-light text-dark border" style="font-size: 0.725rem;">
                                    Buka Daftar &darr;
                                </span>
                            </div>

                            <div class="selected-product-card d-flex justify-content-between align-items-center d-none btn-reopen-picker" role="button" title="Klik untuk mengganti produk">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary-subtle border border-primary-subtle" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="bi bi-bag-check-fill text-primary fs-5"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-dark text-truncate product-selected-name" style="font-size: 0.925rem;">-</div>
                                        <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                            <span class="badge bg-light text-secondary border product-selected-category" style="font-size: 0.7rem;">-</span>
                                            <span class="fw-bold text-success currency-amount ms-1 product-selected-price" style="font-size: 0.8rem;">Rp 0</span>
                                            <span class="badge bg-emerald-subtle text-success border border-success-subtle ms-1 product-selected-stock" style="font-size: 0.7rem;">Stok: 0 pcs</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted ms-2 ps-2 border-start d-flex align-items-center" style="font-size: 0.8rem;">
                                    <i class="bi bi-chevron-down text-secondary"></i>
                                </div>
                            </div>

                            <div class="product-picker-dropdown d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark">Daftar Produk WarungGenZ</span>
                                    <button type="button" class="btn-close btn-sm btn-close-picker" aria-label="Close"></button>
                                </div>

                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="search" class="form-control border-start-0 picker-search-input" placeholder="Ketik nama produk...">
                                </div>

                                <div class="d-flex flex-wrap gap-1 mb-2 pb-1 border-bottom">
                                    <button type="button" class="btn btn-xs btn-dark rounded-pill py-0 px-2 picker-cat-filter active" data-cat="" style="font-size: 0.72rem;">Semua</button>
                                    @foreach($groupedList as $categoryName => $prods)
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2 picker-cat-filter" data-cat="{{ strtolower($categoryName) }}" style="font-size: 0.72rem;">
                                            {{ $categoryName }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="picker-item-list" style="max-height: 270px; overflow-y: auto;">
                                    @foreach($products as $prod)
                                    <div class="product-item-card p-2 px-3 mb-2 rounded-3 d-flex justify-content-between align-items-center"
                                         data-id="{{ $prod->id }}"
                                         data-name="{{ strtolower($prod->name) }}"
                                         data-raw-name="{{ $prod->name }}"
                                         data-category="{{ strtolower($prod->category->name ?? 'lainnya') }}"
                                         data-raw-category="{{ $prod->category->name ?? 'Lainnya' }}"
                                         data-price="{{ $prod->price }}"
                                         data-formatted-price="Rp {{ number_format($prod->price, 0, ',', '.') }}"
                                         data-stock="{{ $prod->stock }}">
                                        <div class="pe-2">
                                            <div class="fw-bold text-dark item-card-name" style="font-size: 0.875rem;">
                                                {{ $prod->name }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">
                                                    {{ $prod->category->name ?? 'Lainnya' }}
                                                </span>
                                                <span class="badge bg-emerald-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                                    Stok: {{ $prod->stock }} pcs
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end ps-2 currency-amount" style="white-space: nowrap;">
                                            <div class="fw-bold text-success currency-amount" style="font-size: 0.9rem;">
                                                Rp {{ number_format($prod->price, 0, ',', '.') }}
                                            </div>
                                            <span class="badge bg-primary text-white mt-1" style="font-size: 0.65rem;">
                                                Pilih &rarr;
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="picker-empty-notice text-center py-3 text-muted small d-none">
                                        <i class="bi bi-search d-block mb-1"></i> Produk tidak ditemukan
                                    </div>
                                </div>
                            </div>

                            <select class="form-select item-product d-none" name="items[__INDEX__][product_id]" required>
                                <option value="">-- Pilih Produk WarungGenZ --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}"
                                            data-price="{{ $prod->price }}"
                                            data-stock="{{ $prod->stock }}"
                                            data-category="{{ $prod->category->name ?? 'Lainnya' }}"
                                            data-name="{{ $prod->name }}">
                                        {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Jumlah (Qty)</label>
                            <input type="number"
                                   class="form-control item-quantity"
                                   name="items[__INDEX__][quantity]"
                                   value="1"
                                   min="1"
                                   required>
                            <div class="qty-warning-msg small text-danger fw-semibold d-none mt-1" style="font-size: 0.72rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Maksimal stok tercapai!
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Subtotal</label>
                            <div class="form-control-plaintext item-subtotal fw-bold text-success pt-2 pb-0 currency-amount">Rp 0</div>
                        </div>
                        <div class="col-md-1 mt-md-4 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 remove-order-item" title="Hapus baris">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Status Pembayaran -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold">Status Pembayaran Transaksi</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>Selesai & Lunas</option>
                            <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Lunas (Menunggu Diambil)</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Tertunda (Pending)</option>
                        </select>
                    </div>
                </div>

                <!-- Total Kalkulasi Box -->
                <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase small text-white-50 fw-semibold" style="letter-spacing: 0.05em;">Total Pembayaran</span>
                            <div class="text-white small">Otomatis dihitung dari akumulasi item</div>
                        </div>
                        <div class="fs-2 fw-bold text-emerald currency-amount" id="display_total_price" style="color: #34d399;">Rp 0</div>
                    </div>
                </div>

                <!-- Footer Nav Action -->
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('orders.index') }}" class="btn-outline-soft text-decoration-none">
                        <i class="bi bi-arrow-left"></i>
                        <span>Batal & Kembali</span>
                    </a>
                    <button type="submit" class="btn-emerald-brand px-4">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan Transaksi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Katalog Produk (SS 2: Penataan Rupiah Sempurna & Rapih) -->
<div class="modal fade" id="productCatalogModal" tabindex="-1" aria-labelledby="productCatalogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom pb-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-1" id="productCatalogModalLabel">
                        <i class="bi bi-grid me-2 text-primary"></i>Katalog Produk WarungGenZ
                    </h5>
                    <p class="text-muted small mb-0">Cari produk berdasarkan nama atau filter kategori agar cepat ditemukan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Search & Filter Bar -->
                <div class="row g-2 mb-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="search" id="modal-product-search" class="form-control border-start-0" placeholder="Ketik nama produk (misal: beras, kopi, indomie)...">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select id="modal-category-filter" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($groupedList as $categoryName => $prods)
                                <option value="{{ strtolower($categoryName) }}">{{ $categoryName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tabel Produk Modal dengan Penataan Rupiah Rapi Sejajar Satu Baris -->
                <div class="table-responsive border rounded-3" style="max-height: 400px;">
                    <table class="table table-hover align-middle mb-0" id="modal-product-table" style="table-layout: auto;">
                        <thead class="table-light sticky-top">
                            <tr class="small text-muted text-uppercase" style="font-size: 0.75rem;">
                                <th style="min-width: 200px;">Nama Produk</th>
                                <th style="min-width: 140px;">Kategori</th>
                                <th class="text-end currency-amount" style="min-width: 140px; white-space: nowrap !important;">Harga Satuan</th>
                                <th class="text-center currency-amount" style="min-width: 110px; white-space: nowrap !important;">Sisa Stok</th>
                                <th class="text-center" style="width: 90px; min-width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $p)
                            <tr class="modal-product-row"
                                data-id="{{ $p->id }}"
                                data-name="{{ strtolower($p->name) }}"
                                data-category="{{ strtolower($p->category->name ?? 'lainnya') }}"
                                data-stock="{{ $p->stock }}">
                                <td>
                                    <div class="fw-bold text-dark">{{ $p->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">{{ $p->category->name ?? 'Lainnya' }}</span>
                                </td>
                                <td class="text-end currency-amount" style="white-space: nowrap !important;">
                                    <span class="fw-bold text-dark currency-amount" style="white-space: nowrap !important;">
                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center currency-amount" style="white-space: nowrap !important;">
                                    <span class="badge bg-emerald-subtle text-success border border-success-subtle">
                                        {{ $p->stock }} pcs
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-dark px-3 rounded-pill btn-select-from-modal" data-id="{{ $p->id }}">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            <tr id="modal-empty-search" class="d-none">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-search fs-3 d-block mb-1"></i>
                                    Tidak ada produk yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('order-items');
    const itemTemplate = document.getElementById('order-item-template');
    const addItemButton = document.getElementById('add-order-item');
    const displayTotalPrice = document.getElementById('display_total_price');
    const orderForm = document.getElementById('order-form');
    let activeOrderRow = null;

    // Tutup picker dropdown bila user mengklik di luar
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.product-picker-col')) {
            document.querySelectorAll('.product-picker-dropdown').forEach(dropdown => {
                dropdown.classList.add('d-none');
            });
        }
    });

    function updateTotals() {
        let total = 0;
        const rows = [...itemsContainer.querySelectorAll('.order-item')];

        rows.forEach(function(row) {
            const select = row.querySelector('.item-product');
            const quantity = row.querySelector('.item-quantity');
            const selected = select.options[select.selectedIndex];
            const price = Number(selected?.dataset.price || 0);
            const stock = Number(selected?.dataset.stock || 0);
            const hasProduct = Boolean(selected && selected.value);
            const warningMsg = row.querySelector('.qty-warning-msg');

            // Validasi & Penguncian QTY terhadap Stok: TIDAK BISA LEWAT DARI STOK!
            if (hasProduct) {
                quantity.max = stock;
                let currentVal = parseInt(quantity.value, 10);

                if (isNaN(currentVal) || currentVal < 1) {
                    if (quantity.value !== '') {
                        quantity.value = 1;
                        currentVal = 1;
                    }
                } else if (currentVal > stock) {
                    quantity.value = stock;
                    currentVal = stock;
                    if (warningMsg) {
                        warningMsg.classList.remove('d-none');
                        setTimeout(() => warningMsg.classList.add('d-none'), 2200);
                    }
                }
            } else {
                quantity.removeAttribute('max');
            }

            const qty = Number(quantity.value || 0);
            const subtotal = price * qty;
            row.querySelector('.item-subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            total += subtotal;

            // Update kartu produk terpilih (persegi panjang)
            const triggerBtn = row.querySelector('.picker-trigger-btn');
            const selectedCard = row.querySelector('.selected-product-card');
            const changeHeaderBtn = row.querySelector('.btn-change-header');
            const sepHeader = row.querySelector('.btn-sep-header');

            if (hasProduct) {
                const name = selected.dataset.name || selected.text;
                const category = selected.dataset.category || 'Lainnya';

                const nameEl = row.querySelector('.product-selected-name');
                const catEl = row.querySelector('.product-selected-category');
                const priceEl = row.querySelector('.product-selected-price');
                const stockEl = row.querySelector('.product-selected-stock');

                if (nameEl) nameEl.textContent = name;
                if (catEl) catEl.textContent = category;
                if (priceEl) priceEl.textContent = 'Rp ' + price.toLocaleString('id-ID');
                if (stockEl) stockEl.textContent = 'Stok: ' + stock + ' pcs';

                if (triggerBtn) triggerBtn.classList.add('d-none');
                if (selectedCard) selectedCard.classList.remove('d-none');
                if (changeHeaderBtn) changeHeaderBtn.classList.remove('d-none');
                if (sepHeader) sepHeader.classList.remove('d-none');
            } else {
                if (triggerBtn) triggerBtn.classList.remove('d-none');
                if (selectedCard) selectedCard.classList.add('d-none');
                if (changeHeaderBtn) changeHeaderBtn.classList.add('d-none');
                if (sepHeader) sepHeader.classList.add('d-none');
            }
        });

        // Cegah duplikasi produk yang sama di beberapa baris
        rows.forEach(function(row) {
            const ownSelect = row.querySelector('.item-product');
            const selectedIds = rows
                .map(item => item.querySelector('.item-product').value)
                .filter(Boolean);

            [...ownSelect.options].forEach(function(option) {
                option.disabled = option.value !== ownSelect.value
                    && selectedIds.filter(id => id === option.value).length > 0;
            });
        });

        displayTotalPrice.textContent = 'Rp ' + total.toLocaleString('id-ID');
        itemsContainer.querySelectorAll('.remove-order-item').forEach(button => {
            button.disabled = rows.length === 1;
        });
    }

    // Fungsi memilih produk dari Card Persegi Panjang
    function selectProductInRow(row, productId) {
        const selectEl = row.querySelector('.item-product');
        if (!selectEl) return;

        selectEl.value = productId;
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));

        // Reset Qty ke 1 jika sebelumnya kosong atau melebihi stok produk baru
        const quantity = row.querySelector('.item-quantity');
        const selected = selectEl.options[selectEl.selectedIndex];
        const stock = Number(selected?.dataset.stock || 1);

        let currentVal = parseInt(quantity.value, 10);
        if (isNaN(currentVal) || currentVal < 1) {
            quantity.value = 1;
        } else if (currentVal > stock) {
            quantity.value = stock;
        }

        // Tutup dropdown picker
        const dropdown = row.querySelector('.product-picker-dropdown');
        if (dropdown) dropdown.classList.add('d-none');

        updateTotals();
    }

    // Event handler container baris item
    itemsContainer.addEventListener('click', function(event) {
        const row = event.target.closest('.order-item');
        if (!row) return;

        // 1. Klik Hapus Baris
        if (event.target.closest('.remove-order-item') && itemsContainer.querySelectorAll('.order-item').length > 1) {
            row.remove();
            updateTotals();
            return;
        }

        // 2. Klik Buka Dropdown Picker (Klik pada Trigger, Kartu Terpilih, atau Link Ganti Produk di Atas)
        if (event.target.closest('.picker-trigger-btn') || event.target.closest('.selected-product-card') || event.target.closest('.btn-reopen-picker')) {
            document.querySelectorAll('.product-picker-dropdown').forEach(d => {
                if (d !== row.querySelector('.product-picker-dropdown')) d.classList.add('d-none');
            });

            const dropdown = row.querySelector('.product-picker-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('d-none');
                if (!dropdown.classList.contains('d-none')) {
                    const searchInput = dropdown.querySelector('.picker-search-input');
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                        filterPickerItems(dropdown);
                    }
                }
            }
            return;
        }

        // 3. Klik Tutup Dropdown Picker
        if (event.target.closest('.btn-close-picker')) {
            const dropdown = row.querySelector('.product-picker-dropdown');
            if (dropdown) dropdown.classList.add('d-none');
            return;
        }

        // 4. Klik Kartu Barang Persegi Panjang
        const productCard = event.target.closest('.product-item-card');
        if (productCard) {
            const productId = productCard.dataset.id;
            selectProductInRow(row, productId);
            return;
        }

        // 5. Klik Buka Modal Katalog Lengkap
        const browseBtn = event.target.closest('.btn-browse-catalog');
        if (browseBtn) {
            activeOrderRow = row;
            const modalEl = document.getElementById('productCatalogModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
            return;
        }

        // 6. Klik Filter Kategori di Picker Baris
        const catFilterBtn = event.target.closest('.picker-cat-filter');
        if (catFilterBtn) {
            const dropdown = row.querySelector('.product-picker-dropdown');
            dropdown.querySelectorAll('.picker-cat-filter').forEach(b => {
                b.classList.remove('btn-dark', 'active');
                b.classList.add('btn-outline-secondary');
            });
            catFilterBtn.classList.remove('btn-outline-secondary');
            catFilterBtn.classList.add('btn-dark', 'active');
            filterPickerItems(dropdown);
            return;
        }
    });

    // Event input pencarian di dropdown baris
    itemsContainer.addEventListener('input', function(event) {
        if (event.target.classList.contains('picker-search-input')) {
            const dropdown = event.target.closest('.product-picker-dropdown');
            if (dropdown) filterPickerItems(dropdown);
            return;
        }

        // Pengetikan kuantitas QTY: LANGSUNG DIKUNCI / CLAMP AGAR TIDAK LEWAT DARI STOK
        if (event.target.classList.contains('item-quantity')) {
            const row = event.target.closest('.order-item');
            const select = row.querySelector('.item-product');
            const selected = select.options[select.selectedIndex];
            const stock = Number(selected?.dataset.stock || 0);
            const warningMsg = row.querySelector('.qty-warning-msg');

            if (stock > 0 && event.target.value !== '') {
                let val = parseInt(event.target.value, 10);
                if (val > stock) {
                    event.target.value = stock;
                    if (warningMsg) {
                        warningMsg.classList.remove('d-none');
                        setTimeout(() => warningMsg.classList.add('d-none'), 2200);
                    }
                }
            }
            updateTotals();
        }
    });

    // Pastikan saat selesai ketik (blur), kuantitas minimal 1 dan tidak melebihi stok
    itemsContainer.addEventListener('blur', function(event) {
        if (event.target.classList.contains('item-quantity')) {
            const row = event.target.closest('.order-item');
            const select = row.querySelector('.item-product');
            const selected = select.options[select.selectedIndex];
            const stock = Number(selected?.dataset.stock || 0);

            let val = parseInt(event.target.value, 10);
            if (isNaN(val) || val < 1) {
                event.target.value = 1;
            } else if (stock > 0 && val > stock) {
                event.target.value = stock;
            }
            updateTotals();
        }
    }, true);

    // Filter produk persegi panjang di dropdown
    function filterPickerItems(dropdown) {
        const query = (dropdown.querySelector('.picker-search-input')?.value || '').toLowerCase().trim();
        const activeCatBtn = dropdown.querySelector('.picker-cat-filter.active');
        const cat = (activeCatBtn?.dataset.cat || '').toLowerCase().trim();

        const cards = dropdown.querySelectorAll('.product-item-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const category = card.dataset.category || '';
            const matchQuery = !query || name.includes(query);
            const matchCat = !cat || category === cat;

            if (matchQuery && matchCat) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        const emptyNotice = dropdown.querySelector('.picker-empty-notice');
        if (emptyNotice) {
            if (visibleCount === 0) {
                emptyNotice.classList.remove('d-none');
            } else {
                emptyNotice.classList.add('d-none');
            }
        }
    }

    // Tambah baris baru
    addItemButton.addEventListener('click', function() {
        const nextIndex = Date.now();
        const row = itemTemplate.content.cloneNode(true);
        row.querySelectorAll('[name]').forEach(function(input) {
            input.name = input.name.replaceAll('__INDEX__', nextIndex);
        });
        itemsContainer.appendChild(row);
        updateTotals();
    });

    // Filter pada Modal Katalog (SS 2)
    const modalSearchInput = document.getElementById('modal-product-search');
    const modalCategoryFilter = document.getElementById('modal-category-filter');
    const modalRows = document.querySelectorAll('.modal-product-row');
    const modalEmptySearch = document.getElementById('modal-empty-search');

    function filterModalCatalog() {
        const query = (modalSearchInput ? modalSearchInput.value : '').toLowerCase().trim();
        const category = (modalCategoryFilter ? modalCategoryFilter.value : '').toLowerCase().trim();
        let visibleCount = 0;

        modalRows.forEach(function(r) {
            const name = r.dataset.name || '';
            const cat = r.dataset.category || '';
            const matchName = !query || name.includes(query);
            const matchCat = !category || cat === category;

            if (matchName && matchCat) {
                r.classList.remove('d-none');
                visibleCount++;
            } else {
                r.classList.add('d-none');
            }
        });

        if (modalEmptySearch) {
            if (visibleCount === 0) {
                modalEmptySearch.classList.remove('d-none');
            } else {
                modalEmptySearch.classList.add('d-none');
            }
        }
    }

    if (modalSearchInput) modalSearchInput.addEventListener('input', filterModalCatalog);
    if (modalCategoryFilter) modalCategoryFilter.addEventListener('change', filterModalCatalog);

    // Pemilihan produk dari Modal (SS 2)
    document.addEventListener('click', function(event) {
        const selectBtn = event.target.closest('.btn-select-from-modal');
        if (selectBtn && activeOrderRow) {
            const selectedId = selectBtn.dataset.id;
            selectProductInRow(activeOrderRow, selectedId);

            const modalEl = document.getElementById('productCatalogModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }
    });

    // Validasi Terakhir saat Form Dikirim (Submit Protection)
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            let hasError = false;
            const rows = [...itemsContainer.querySelectorAll('.order-item')];

            rows.forEach(function(row) {
                const select = row.querySelector('.item-product');
                const quantity = row.querySelector('.item-quantity');
                const selected = select.options[select.selectedIndex];
                const stock = Number(selected?.dataset.stock || 0);
                const val = Number(quantity.value || 0);

                if (selected && selected.value && (val > stock || val < 1)) {
                    hasError = true;
                    quantity.value = Math.min(Math.max(1, val), stock);
                    const warn = row.querySelector('.qty-warning-msg');
                    if (warn) warn.classList.remove('d-none');
                }
            });

            if (hasError) {
                e.preventDefault();
                alert('Kuantitas produk tidak boleh melebihi sisa stok yang tersedia!');
                updateTotals();
            }
        });
    }

    updateTotals();
});
</script>
@endsection
