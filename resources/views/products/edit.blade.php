@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="modern-card p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Edit Data Produk</h4>
                    <p class="text-muted small mb-0">Perbarui rincian produk, update harga jual, atau ubah stok barang.</p>
                </div>
                <div class="stat-icon-wrapper" style="background: #fffbeb; color: #d97706;">
                    <i class="bi bi-pencil-square fs-4"></i>
                </div>
            </div>

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="" disabled>-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                   value="{{ number_format((int)old('price', $product->price), 0, ',', '.') }}" 
                                   placeholder="Contoh: 35.000" 
                                   autocomplete="off" 
                                   required>
                        </div>
                        @error('price') 
                            <div class="invalid-feedback d-block">{{ $message }}</div> 
                        @enderror
                        <div class="form-text mt-1" id="price_helper">
                            <small class="text-muted">Ketik angka harga, pemisah ribuan titik akan terisi otomatis.</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stock" class="form-label">Jumlah Stok (Pcs)</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                        @error('stock') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi Produk (Opsional)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                    @error('description') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Foto / Gambar Produk</label>
                    @if($product->image)
                        <div class="d-flex align-items-center gap-3 mb-2 p-2 bg-light rounded-3 border">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-thumb" style="width: 56px; height: 56px;">
                            <div class="small text-muted">
                                Foto produk saat ini tersimpan. Unggah foto baru di bawah jika ingin menggantinya.
                            </div>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div class="form-text small text-muted">Biarkan kosong jika tidak ingin mengganti foto saat ini.</div>
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
                        <span>Perbarui Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
