@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-5 col-lg-4">
        <div class="modern-card p-3 p-sm-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.35rem; border-radius: 14px;">
                    <i class="bi bi-shop"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Masuk ke WarungGenZ</h4>
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
                               class="form-control border-start-0 border-end-0" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan kata sandi" 
                               autocomplete="current-password"
                               required>
                        <button class="btn btn-outline-light border border-start-0 text-muted bg-white px-3" 
                                type="button" 
                                id="togglePasswordBtn" 
                                onclick="togglePasswordVisibility()" 
                                style="border-radius: 0 10px 10px 0;" 
                                title="Lihat / Sembunyikan Kata Sandi">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-dark-brand w-100 justify-content-center py-2 mb-3">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

            <script>
            // Database akun default / yang pernah didaftarkan di perangkat
            const defaultCredentials = {
                'testaja@gmail.com': '12345678',
                'testweb@gmail.com': '12345678',
                'testcustomer@gmail.com': '12345678'
            };

            function autoFillPassword() {
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                if (!emailInput || !passwordInput) return;

                const email = emailInput.value.trim().toLowerCase();
                if (!email) {
                    return;
                }

                // 1. Cek apakah ada password tersimpan di localStorage dari pendaftaran / login sebelumnya
                const savedPass = localStorage.getItem('warunggenz_saved_pass_' + email);
                if (savedPass) {
                    passwordInput.value = savedPass;
                    return;
                }

                // 2. Cek akun default pengujian
                if (defaultCredentials[email]) {
                    passwordInput.value = defaultCredentials[email];
                }
            }

            function togglePasswordVisibility() {
                const passwordInput = document.getElementById('password');
                const icon = document.getElementById('togglePasswordIcon');
                if (!passwordInput || !icon) return;

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye-warning', 'bi-eye');
                }
            }

            // Simpan password ke localStorage saat login berhasil disubmit
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');

                if (emailInput) {
                    emailInput.addEventListener('input', autoFillPassword);
                    emailInput.addEventListener('change', autoFillPassword);

                    // Jika email kosong, coba ambil email terakhir yang pernah dipakai
                    const lastEmail = localStorage.getItem('warunggenz_last_email');
                    if (!emailInput.value && lastEmail) {
                        emailInput.value = lastEmail;
                    }
                    autoFillPassword();
                }

                if (form) {
                    form.addEventListener('submit', function() {
                        const email = emailInput.value.trim().toLowerCase();
                        const pass = passwordInput.value;
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
                    Belum punya akun? <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Daftar sekarang</a>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
