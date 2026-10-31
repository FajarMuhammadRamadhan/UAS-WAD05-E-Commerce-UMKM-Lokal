@extends('layouts.app')

@section('title', 'Pemesanan Online - WarungGenZ')

@section('content')
<style>
/* Hilangkan browser default number spin arrows agar rapi & simpel */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}

.product-row-card {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    border-radius: 14px;
    padding: 18px;
    transition: all 0.2s ease;
}
.product-row-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px -2px rgba(15, 23, 42, 0.06);
}

.qty-stepper {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 3px;
}
.qty-stepper button {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #ffffff;
    border-radius: 8px;
    color: #0f172a;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.15s ease;
}
.qty-stepper button:hover:not(:disabled) {
    background: #f1f5f9;
    color: #2563eb;
}
.qty-stepper button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #e2e8f0;
}
.qty-stepper input {
    width: 50px;
    height: 38px;
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 700;
    color: #0f172a;
    font-size: 1rem;
}
.qty-stepper input:focus {
    outline: none;
}

.product-detail-grid {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header Halaman Checkout -->
        <div class="row align-items-center mb-4 g-2">
            <div class="col-md-7">
                <h3 class="section-title mb-1">
                    <i class="bi bi-cart3 me-2 text-primary"></i>Pemesanan Online WarungGenZ
                </h3>
                <p class="section-subtitle mb-0">Pilih produk sembako & kebutuhan harian, pesanan akan langsung disiapkan.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="{{ route('orders.my_orders') }}" class="btn-outline-soft text-decoration-none">
                    <i class="bi bi-bag-check me-1"></i> Pesanan Saya
                </a>
                <a href="{{ route('products.index') }}" class="btn-outline-soft text-decoration-none ms-1">
                    <i class="bi bi-arrow-left me-1"></i> Katalog
                </a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi kesalahan input:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('orders.store_customer') }}" method="POST" id="checkoutForm" onsubmit="return validateBeforeSubmit(event)">
            @csrf
            <div class="row g-4">
                <!-- Kolom Kiri: Pilih Produk & Keranjang Pesanan -->
                <div class="col-lg-7">
                    <div class="modern-card p-3 p-sm-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-basket2 me-1 text-primary"></i> Produk yang Dipesan
                                </h5>
                                <small class="text-muted">Pilih produk dan tentukan kuantitas yang ingin dibeli</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="addProductRow()">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                            </button>
                        </div>

                        <!-- Wadah Baris Produk -->
                        <div id="itemsContainer" class="d-flex flex-column gap-3 mb-3"></div>

                        <!-- Ringkasan Total Belanja -->
                        <div class="p-3 bg-light rounded-4 border mt-3">
                            <div class="d-flex justify-content-between align-items-center text-muted small mb-1">
                                <span>Estimasi Total Produk:</span>
                                <span id="totalItemsCount" class="fw-semibold">0 Item</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark fs-6">Total Pembayaran:</span>
                                <span class="fw-bold text-success fs-5" id="grandTotalDisplay">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Data Pengiriman & Metode Pembayaran -->
                <div class="col-lg-5">
                    <div class="modern-card p-3 p-sm-4">
                        <h5 class="fw-bold mb-3 pb-2 border-bottom text-dark">
                            <i class="bi bi-geo-alt me-1 text-danger"></i> Informasi Pengiriman
                        </h5>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Pemesan</label>
                            <input type="text" class="form-control bg-light" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="customer_phone" class="form-label small fw-semibold">No. WhatsApp / HP Aktif <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-whatsapp text-success"></i></span>
                                <input type="tel" name="customer_phone" id="customer_phone" class="form-control" placeholder="Contoh: 081234567890" value="{{ old('customer_phone') }}" required>
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Untuk konfirmasi pesanan dan update kurir.</small>
                        </div>

                        <div class="mb-3">
                            <label for="customer_address" class="form-label small fw-semibold">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
                            <textarea name="customer_address" id="customer_address" rows="3" class="form-control" placeholder="Tuliskan nama jalan, no. rumah, RT/RW, dan patokan..." required>{{ old('customer_address') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check p-3 border rounded-3 bg-light">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark d-block" for="pay_cod">
                                        <i class="bi bi-cash-stack text-success me-1"></i> COD (Bayar Tunai di Tempat)
                                        <div class="fw-normal small text-muted">Bayar uang tunai kepada kurir saat pesanan tiba.</div>
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded-3 bg-light">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_transfer" value="transfer" {{ old('payment_method') === 'transfer' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark d-block" for="pay_transfer">
                                        <i class="bi bi-bank text-primary me-1"></i> Transfer Bank Manual
                                        <div class="fw-normal small text-muted">BCA / Mandiri / BRI Toko WarungGenZ.</div>
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded-3 bg-light">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_qris" value="qris" {{ old('payment_method') === 'qris' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark d-block" for="pay_qris">
                                        <i class="bi bi-qr-code-scan text-dark me-1"></i> QRIS (Gopay/OVO/Dana/BCA Mobile)
                                        <div class="fw-normal small text-muted">Scan QRIS saat pesanan tiba atau via nota.</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label small fw-semibold">Catatan Khusus (Opsional)</label>
                            <input type="text" name="notes" id="notes" class="form-control" placeholder="Contoh: Titip di teras jika rumah sepi..." value="{{ old('notes') }}">
                        </div>

                        <!-- Tombol Pesan Sekarang -->
                        <button type="submit" class="btn-emerald-brand w-100 justify-content-center py-3 fs-6" id="submitBtn">
                            <i class="bi bi-cart-check-fill me-2"></i> Pesan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Template & Script Interaktif Pemilihan Produk -->
<script>
    const availableProducts = @json($products);
    const initialSelectedId = @json($selectedProductId);
    let rowCounter = 0;

    function buildOptionsHtml(preselectedId = null) {
        // Kelompokkan produk berdasarkan Kategori
        const grouped = {};
        availableProducts.forEach(prod => {
            const catName = prod.category ? prod.category.name : 'Lainnya';
            if (!grouped[catName]) {
                grouped[catName] = [];
            }
            grouped[catName].push(prod);
        });

        let html = '<option value="">-- Pilih Produk Warung (Dikelompokkan per Kategori) --</option>';
        for (const [catName, prods] of Object.entries(grouped)) {
            html += `<optgroup label="📂 Kategori: ${catName}">`;
            prods.forEach(prod => {
                const isSelected = (preselectedId && String(prod.id) === String(preselectedId));
                html += `<option value="${prod.id}" 
                    data-price="${prod.price}" 
                    data-stock="${prod.stock}" 
                    data-category="${catName}"
                    data-name="${prod.name}"
                    ${isSelected ? 'selected' : ''}>
                    ${prod.name} (Stok: ${prod.stock} pcs) - Rp ${Number(prod.price).toLocaleString('id-ID')}
                </option>`;
            });
            html += `</optgroup>`;
        }
        return html;
    }

    function addProductRow(preselectedId = null) {
        const container = document.getElementById('itemsContainer');
        const index = rowCounter++;

        const targetSelectedId = preselectedId || (index === 0 ? initialSelectedId : null);

        const row = document.createElement('div');
        row.className = 'product-row-card position-relative product-row';
        row.id = `row-${index}`;

        const optionsHtml = buildOptionsHtml(targetSelectedId);

        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge-soft badge-soft-blue" style="font-size: 0.75rem;">
                    <i class="bi bi-bag-plus me-1"></i> Produk #${index + 1}
                </span>
                <button type="button" class="btn btn-sm text-danger p-0 border-0" onclick="removeRow(${index})" title="Hapus dari daftar">
                    <i class="bi bi-trash3 me-1"></i><span style="font-size: 0.78rem;">Hapus</span>
                </button>
            </div>
            
            <!-- Kolom Pemilihan Produk yang Rapi & Dikelompokkan per Kategori -->
            <div class="mb-3">
                <select name="items[${index}][product_id]" class="form-select product-select" data-row-id="${index}" required onchange="onProductChange(${index})">
                    ${optionsHtml}
                </select>
            </div>

            <!-- Detail Kolom Spesifik Produk (Kategori, Harga, Sisa Stok, Kuantitas, Subtotal) -->
            <div class="product-detail-grid mb-2" id="detail-box-${index}" style="display: none;">
                <div class="row align-items-center g-2">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge-soft badge-soft-blue" id="cat-badge-${index}" style="font-size: 0.72rem;">Kategori</span>
                            <span class="badge-soft badge-soft-emerald" id="stock-badge-${index}" style="font-size: 0.72rem;">Stok: 0 pcs</span>
                        </div>
                        <div class="small text-muted">
                            Harga Satuan: <strong class="text-dark" id="unit-price-${index}">Rp 0</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <div class="d-inline-flex flex-column align-items-sm-end">
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted fw-semibold">Jumlah:</span>
                                <div class="qty-stepper">
                                    <button type="button" id="btn-minus-${index}" onclick="stepQty(${index}, -1)" title="Kurangi">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" 
                                           name="items[${index}][quantity]" 
                                           id="qty-input-${index}" 
                                           class="quantity-input" 
                                           value="1" 
                                           min="1" 
                                           max="1" 
                                           required 
                                           oninput="onQtyInput(${index})"
                                           onblur="onQtyBlur(${index})">
                                    <button type="button" id="btn-plus-${index}" onclick="stepQty(${index}, 1)" title="Tambah">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-1">
                                <span class="text-muted small" style="font-size: 0.75rem;">Subtotal: </span>
                                <span class="subtotal-val fw-bold text-success fs-6" id="subtotal-${index}">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Validasi Stok Dinamis -->
                <div id="qty-warning-${index}" class="alert alert-danger py-1 px-2 mt-2 mb-0 small d-none" style="font-size: 0.78rem;"></div>
            </div>
        `;

        container.appendChild(row);
        onProductChange(index);
    }

    function onProductChange(index) {
        const row = document.getElementById(`row-${index}`);
        if (!row) return;

        const select = row.querySelector('.product-select');
        const detailBox = document.getElementById(`detail-box-${index}`);
        const qtyInput = document.getElementById(`qty-input-${index}`);
        const unitPriceEl = document.getElementById(`unit-price-${index}`);
        const catBadgeEl = document.getElementById(`cat-badge-${index}`);
        const stockBadgeEl = document.getElementById(`stock-badge-${index}`);
        const warningEl = document.getElementById(`qty-warning-${index}`);
        const selectedOpt = select.options[select.selectedIndex];

        if (selectedOpt && selectedOpt.value) {
            const stock = parseInt(selectedOpt.dataset.stock, 10) || 0;
            const price = parseFloat(selectedOpt.dataset.price) || 0;
            const catName = selectedOpt.dataset.category || 'Umum';

            // Tampilkan box kolom produk
            detailBox.style.display = 'block';
            catBadgeEl.innerHTML = `<i class="bi bi-tag-fill me-1"></i>${catName}`;
            stockBadgeEl.innerHTML = `<i class="bi bi-boxes me-1"></i>Sisa Stok: ${stock} pcs`;
            unitPriceEl.innerText = `Rp ${price.toLocaleString('id-ID')}`;

            // Set batas kuantitas tidak boleh melebihi sisa stok yang ada
            qtyInput.max = stock;
            qtyInput.dataset.maxStock = stock;

            let curVal = parseInt(qtyInput.value, 10) || 1;
            if (curVal > stock) {
                curVal = stock;
                qtyInput.value = stock;
            }
            if (curVal < 1) {
                curVal = 1;
                qtyInput.value = 1;
            }

            updateStepperButtons(index, curVal, stock);
        } else {
            detailBox.style.display = 'none';
        }

        if (warningEl) warningEl.classList.add('d-none');
        calculateTotal();
    }

    // Update status disabled/enabled tombol + dan - stepper
    function updateStepperButtons(index, currentVal, maxStock) {
        const btnMinus = document.getElementById(`btn-minus-${index}`);
        const btnPlus = document.getElementById(`btn-plus-${index}`);

        if (btnMinus) {
            btnMinus.disabled = (currentVal <= 1);
        }
        if (btnPlus) {
            btnPlus.disabled = (currentVal >= maxStock);
        }
    }

    // Tambah/kurang kuantitas dengan tombol stepper
    function stepQty(index, change) {
        const qtyInput = document.getElementById(`qty-input-${index}`);
        if (!qtyInput) return;

        const maxStock = parseInt(qtyInput.dataset.maxStock || qtyInput.max, 10) || 1;
        let current = parseInt(qtyInput.value, 10) || 1;
        let next = current + change;
        const warningEl = document.getElementById(`qty-warning-${index}`);

        if (next > maxStock) {
            next = maxStock;
            if (warningEl) {
                warningEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Jumlah tidak boleh melebihi sisa stok!</strong> Maksimal pemesanan produk ini adalah <strong>${maxStock} pcs</strong>.`;
                warningEl.classList.remove('d-none');
                setTimeout(() => { if (warningEl) warningEl.classList.add('d-none'); }, 3000);
            }
        } else {
            if (warningEl) warningEl.classList.add('d-none');
        }

        if (next < 1) next = 1;

        qtyInput.value = next;
        updateStepperButtons(index, next, maxStock);
        calculateTotal();
    }

    // Mencegah pengetikan manual angka melewati stok yang ada
    function onQtyInput(index) {
        const qtyInput = document.getElementById(`qty-input-${index}`);
        if (!qtyInput) return;

        const maxStock = parseInt(qtyInput.dataset.maxStock || qtyInput.max, 10) || 1;
        let val = parseInt(qtyInput.value, 10);
        const warningEl = document.getElementById(`qty-warning-${index}`);

        if (val > maxStock) {
            qtyInput.value = maxStock;
            if (warningEl) {
                warningEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Jumlah tidak boleh melebihi sisa stok!</strong> Maksimal pemesanan produk ini adalah <strong>${maxStock} pcs</strong>.`;
                warningEl.classList.remove('d-none');
            }
        } else {
            if (warningEl) warningEl.classList.add('d-none');
        }

        updateStepperButtons(index, parseInt(qtyInput.value, 10) || 1, maxStock);
        calculateTotal();
    }

    function onQtyBlur(index) {
        const qtyInput = document.getElementById(`qty-input-${index}`);
        if (!qtyInput) return;
        const maxStock = parseInt(qtyInput.dataset.maxStock || qtyInput.max, 10) || 1;
        let val = parseInt(qtyInput.value, 10);
        if (isNaN(val) || val < 1) {
            qtyInput.value = 1;
        } else if (val > maxStock) {
            qtyInput.value = maxStock;
        }
        updateStepperButtons(index, parseInt(qtyInput.value, 10), maxStock);
        calculateTotal();
    }

    function removeRow(index) {
        const row = document.getElementById(`row-${index}`);
        if (row) {
            row.remove();
            calculateTotal();
        }
        if (document.querySelectorAll('.product-row').length === 0) {
            addProductRow();
        }
    }

    function calculateTotal() {
        let grandTotal = 0;
        let totalItems = 0;

        document.querySelectorAll('.product-row').forEach(row => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.quantity-input');
            const subtotalEl = row.querySelector('.subtotal-val');

            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const price = parseFloat(selectedOpt.dataset.price) || 0;
                const qty = parseInt(qtyInput.value, 10) || 0;
                const subtotal = price * qty;
                if (subtotalEl) {
                    subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
                }
                grandTotal += subtotal;
                totalItems += qty;
            } else {
                if (subtotalEl) {
                    subtotalEl.innerText = 'Rp 0';
                }
            }
        });

        document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        document.getElementById('totalItemsCount').innerText = `${totalItems} Item`;
    }

    // Validasi final sebelum kirim formulir
    function validateBeforeSubmit(event) {
        const rows = document.querySelectorAll('.product-row');
        let hasValidItem = false;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.quantity-input');
            const selectedOpt = select.options[select.selectedIndex];

            if (!selectedOpt || !selectedOpt.value) {
                alert('Silakan pilih produk yang ingin Anda pesan terlebih dahulu.');
                select.focus();
                event.preventDefault();
                return false;
            }

            const stock = parseInt(selectedOpt.dataset.stock, 10) || 0;
            const qty = parseInt(qtyInput.value, 10) || 0;
            const productName = selectedOpt.dataset.name || 'Produk';

            if (qty > stock) {
                alert(`Jumlah pesanan untuk ${productName} (${qty} pcs) melebihi stok yang ada (${stock} pcs). Mohon kurangi jumlahnya.`);
                qtyInput.focus();
                event.preventDefault();
                return false;
            }

            if (qty < 1) {
                alert(`Kuantitas untuk ${productName} minimal 1 pcs.`);
                qtyInput.focus();
                event.preventDefault();
                return false;
            }

            hasValidItem = true;
        }

        if (!hasValidItem) {
            alert('Silakan tambahkan minimal 1 produk untuk melanjutkan pesanan.');
            event.preventDefault();
            return false;
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        addProductRow();
    });
</script>
@endsection
