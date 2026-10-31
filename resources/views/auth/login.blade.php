@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-5 col-lg-4">
        <div class="modern-card p-3 p-sm-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.35rem; border-radius: 14px;">
                    <i class="bi bi-shop"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Masuk ke WarungKita</h4>
                <p class="text-muted small">Kelola stok inventaris & transaksi toko UMKM.</p>
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" 
                               class="form-control border-start-0 @error('email') is-invalid @enderror" 
                               style="border-radius: 0 10px 10px 0;"
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="nama@toko.com" 
                               required 
                               autofocus>
                    </div>
                    @error('email') 
                        <div class="text-danger small mt-1">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0" 
                               style="border-radius: 0 10px 10px 0;"
                               id="password" 
                               name="password" 
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" 
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-dark-brand w-100 justify-content-center py-2 mb-3">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

            <div class="text-center pt-2 border-top">
                <small class="text-muted">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Daftar sekarang</a>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
