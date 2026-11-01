@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="modern-card p-3 p-sm-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.35rem; border-radius: 14px;">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Daftar Akun Baru</h4>
                <p class="text-muted small">Mulai kelola inventaris dan operasional tokomu.</p>
            </div>

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0 @error('name') is-invalid @enderror" 
                               style="border-radius: 0 10px 10px 0;"
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Contoh: Fajar Muhammad" 
                               required>
                    </div>
                    @error('name') 
                        <div class="text-danger small mt-1">{{ $message }}</div> 
                    @enderror
                </div>

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
                               required>
                    </div>
                    @error('email') 
                        <div class="text-danger small mt-1">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                   style="border-radius: 0 10px 10px 0;"
                                   id="password" 
                                   name="password" 
                                   placeholder="Min. 8 karakter" 
                                   required>
                        </div>
                        @error('password') 
                            <div class="text-danger small mt-1">{{ $message }}</div> 
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Ulangi Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                                <i class="bi bi-shield-check"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0" 
                                   style="border-radius: 0 10px 10px 0;"
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ulangi password" 
                                   required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-dark-brand w-100 justify-content-center py-2 mb-3">
                    <i class="bi bi-check2-circle"></i>
                    <span>Daftarkan Akun</span>
                </button>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        const email = document.getElementById('email')?.value.trim().toLowerCase();
                        const pass = document.getElementById('password')?.value;
                        if (email && pass) {
                            localStorage.setItem('warunggenz_saved_pass_' + email, pass);
                            localStorage.setItem('warunggenz_last_email', email);
                        }
                    });
                }
            });
            </script>

            <div class="text-center pt-2 border-top">
                <small class="text-muted">
                    Sudah memiliki akun? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Masuk di sini</a>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
