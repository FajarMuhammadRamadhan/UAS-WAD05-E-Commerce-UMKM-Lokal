@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="modern-card p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Tambah Produk Baru</h4>
                    <p class="text-muted small mb-0">Lengkapi data barang dagang untuk ditambahkan ke katalog inventaris WarungGenZ.</p>
                </div>
                <div class="stat-icon-wrapper" style="background: #eff6ff; color: #2563eb;">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Minyak Goreng Filma 2 Liter" required>
                    @error('name') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Kategori Produk</label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="" {{ old('category_id') ? '' : 'selected' }} disabled>-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Harga Jual Satuan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">Rp</span>
                            <input type="text" 
                                   class="form-control border-start-0 @error('price') is-invalid @enderror" 
                                   style="border-radius: 0 10px 10px 0;"
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price') ? number_format((int)preg_replace('/[^0-9]/', '', old('price')), 0, ',', '.') : '' }}" 
                                   placeholder="Contoh: 35.000" 
                                   autocomplete="off" 
                                   required>
                        </div>
                        @error('price') 
                            <div class="invalid-feedback d-block">{{ $message }}</div> 
                        @enderror
                        <div class="form-text mt-1" id="price_helper">
                            <small class="text-muted">Ketik angka nominal harga, pemisah ribuan titik akan terisi otomatis.</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stock" class="form-label">Jumlah Stok (Pcs)</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock') }}" placeholder="Contoh: 25" min="0" required>
                        @error('stock') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi Barang (Opsional)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Informasi ukuran, kadaluarsa, atau catatan produk...">{{ old('description') }}</textarea>
                    @error('description') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Foto / Gambar Produk (Opsional)</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div class="form-text small text-muted">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
                    @error('image') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('products.index') }}" class="btn-outline-soft text-decoration-none">
                        <i class="bi bi-arrow-left"></i>
                        <span>Batal & Kembali</span>
                    </a>
                    <button type="submit" class="btn-dark-brand px-4">
                        <i class="bi bi-check2"></i>
                        <span>Simpan Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
