@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="modern-card p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Catat Pengeluaran Baru</h4>
                    <p class="text-muted small mb-0">Dokumentasikan biaya operasional, belanja persediaan, atau tagihan toko.</p>
                </div>
                <div class="stat-icon-wrapper" style="background: #fff1f2; color: #e11d48;">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Nama Pengeluaran</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}"
                           class="form-control @error('title') is-invalid @enderror"
                           maxlength="150" placeholder="Contoh: Pembayaran Listrik Toko Bulan Ini" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expense_date" class="form-label">Tanggal Transaksi</label>
                        <input id="expense_date" name="expense_date" type="date"
                               value="{{ old('expense_date', now()->toDateString()) }}"
                               class="form-control @error('expense_date') is-invalid @enderror" required>
                        @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Nominal Pengeluaran (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">Rp</span>
                        <input id="amount" name="amount" type="text" inputmode="numeric" autocomplete="off" value="{{ old('amount') }}"
                               class="form-control border-start-0 @error('amount') is-invalid @enderror"
                               style="border-radius: 0 10px 10px 0;"
                               placeholder="Contoh: 150.000" required>
                    </div>
                    @error('amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <div class="form-text small text-muted">Format titik otomatis ditambahkan saat mengetik.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Keterangan / Catatan Tambahan (Opsional)</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              maxlength="1000" placeholder="Rincian nota, nomor transaksi, atau catatan lainnya...">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('expenses.index') }}" class="btn-outline-soft text-decoration-none">
                        <i class="bi bi-arrow-left"></i>
                        <span>Batal & Kembali</span>
                    </a>
                    <button type="submit" class="btn-dark-brand px-4">
                        <i class="bi bi-check2"></i>
                        <span>Simpan Pengeluaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');

    function formatRupiah(value) {
        const digits = value.replace(/\D/g, '');
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    if (amountInput.value) {
        amountInput.value = formatRupiah(amountInput.value);
    }
    
    amountInput.addEventListener('input', function() {
        this.value = formatRupiah(this.value);
    });
});
</script>
@endsection
