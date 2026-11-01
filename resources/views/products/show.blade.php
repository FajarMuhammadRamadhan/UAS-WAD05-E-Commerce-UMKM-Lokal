@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="modern-card p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Detail Produk</h4>
                    <p class="text-muted small mb-0">Informasi lengkap spesifikasi barang, stok, dan harga jual.</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn-outline-soft btn-sm text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-md-5 text-center">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded-4 shadow-sm border" style="max-height: 260px; object-fit: cover; width: 100%;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-5 bg-light rounded-4 border d-flex flex-column align-items-center justify-content-center text-muted\' style=\'height: 240px;\'><i class=\'bi bi-image fs-1 mb-2\'></i><span class=\'small fw-semibold\'>Tidak ada foto produk</span></div>';">
                    @else
                        <div class="p-5 bg-light rounded-4 border d-flex flex-column align-items-center justify-content-center text-muted" style="height: 240px;">
                            <i class="bi bi-image fs-1 mb-2"></i>
                            <span class="small fw-semibold">Tidak ada foto produk</span>
                        </div>
                    @endif
                </div>
                <div class="col-md-7">
                    <div class="mb-2">
                        @if(optional($product->category)->name)
                            <span class="badge-soft badge-soft-blue">
                                <i class="bi bi-tag-fill" style="font-size: 0.65rem;"></i>
                                {{ $product->category->name }}
                            </span>
                        @else
                            <span class="badge-soft badge-soft-slate">Tanpa Kategori</span>
                        @endif
                    </div>
                    
                    <h3 class="fw-bold text-dark mb-2">{{ $product->name }}</h3>
                    
                    <div class="fs-3 fw-bold text-success mb-3">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="text-muted small">Ketersediaan Stok:</span>
                        @if($product->stock === 0)
                            <span class="badge-soft badge-soft-rose">Habis (0 Pcs)</span>
                        @elseif($product->stock <= 5)
                            <span class="badge-soft badge-soft-amber">Menipis ({{ $product->stock }} Pcs)</span>
                        @else
                            <span class="badge-soft badge-soft-emerald">{{ $product->stock }} Pcs Tersedia</span>
                        @endif
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Deskripsi Produk</h6>
                        <p class="text-muted small mb-0">{{ $product->description ?: 'Tidak ada catatan deskripsi untuk produk ini.' }}</p>
                    </div>

                    @if(Auth::user()->isAdmin())
                    <div class="mt-4 pt-2">
                        <a href="{{ route('products.edit', $product->id) }}" class="btn-dark-brand text-decoration-none">
                            <i class="bi bi-pencil-square"></i>
                            <span>Edit Rincian Produk</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
