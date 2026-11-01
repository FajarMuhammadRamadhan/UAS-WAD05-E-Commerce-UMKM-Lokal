<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WarungGenZ - Sistem Inventaris & Penjualan UMKM')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('warunggenz.svg') }}?v=3">
    <link rel="alternate icon" href="{{ asset('warunggenz.svg') }}?v=3">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Modern Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v=2">
</head>
<body>

    <!-- Modern App Navbar -->
    <nav class="navbar navbar-expand-lg app-navbar sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <div class="brand-icon">
                    <i class="bi bi-shop"></i>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="brand-title">WarungGenZ</span>
                    <span class="brand-badge">UMKM</span>
                </div>
            </a>

            <!-- Right Controls: Lonceng Notifikasi & Toggler (Akses Cepat di Mobile & Desktop) -->
            <div class="d-flex align-items-center gap-2 order-lg-last">
                @auth
                <!-- Lonceng Notifikasi (Tampil untuk Admin dan Customer) -->
                <div class="dropdown">
                    <button class="btn btn-outline-soft btn-sm position-relative shadow-none border px-2 py-1" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi" style="min-height: 38px;">
                        <i class="bi bi-bell fs-6 text-dark"></i>
                        <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">
                            0
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border rounded-3 p-2 mt-2" aria-labelledby="notifDropdown" style="width: 320px; max-width: calc(100vw - 24px); max-height: 400px; overflow-y: auto;" id="notifDropdownMenu">
                        <li class="dropdown-header d-flex justify-content-between align-items-center pb-2 border-bottom">
                            <span class="fw-bold text-dark"><i class="bi bi-bell-fill me-1 text-primary"></i> Notifikasi</span>
                            <div class="d-flex align-items-center gap-1">
                                <span id="notifHeaderCount" class="badge bg-danger-subtle text-danger">0 Baru</span>
                            </div>
                        </li>
                        <div id="notifList" class="py-1">
                            <li class="text-center text-muted small py-3" id="noNotifText">
                                <i class="bi bi-check2-circle fs-4 d-block text-success mb-1"></i>
                                Tidak ada notifikasi baru
                            </li>
                        </div>
                        <li class="border-top pt-2 text-center">
                            @if(Auth::user()->canManageOrders())
                            <a href="{{ route('orders.index') }}" class="small text-primary text-decoration-none fw-semibold">
                                Lihat Semua Pesanan &rarr;
                            </a>
                            @else
                            <a href="{{ route('orders.my_orders') }}" class="small text-primary text-decoration-none fw-semibold">
                                Lihat Semua Pesanan Saya &rarr;
                            </a>
                            @endif
                        </li>
                    </ul>
                </div>
                @endauth

                <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation" style="min-height: 38px;">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>
            
            <div class="collapse navbar-collapse" id="navMenu">
                @auth
                    <!-- Navigasi Menu -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                        <li class="nav-item">
                            <a class="nav-pill-link {{ request()->is('products*') || request()->is('/') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                <i class="bi bi-box-seam"></i>
                                <span>Katalog Produk</span>
                            </a>
                        </li>
                        @if(Auth::user()->canManageOrders())
                        <li class="nav-item">
                            <a class="nav-pill-link {{ request()->is('orders*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                <i class="bi bi-receipt"></i>
                                <span>Transaksi Penjualan</span>
                            </a>
                        </li>
                        @else
                        <li class="nav-item">
                            <a class="nav-pill-link {{ request()->is('checkout*') ? 'active' : '' }}" href="{{ route('orders.checkout') }}">
                                <i class="bi bi-cart3"></i>
                                <span>Pesan Sekarang</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-pill-link position-relative {{ request()->is('my-orders*') ? 'active' : '' }}" href="{{ route('orders.my_orders') }}">
                                <i class="bi bi-bag-check"></i>
                                <span>Pesanan Saya</span>
                                <span id="customerNavBadge" class="badge rounded-pill bg-danger ms-1 d-none" style="font-size: 0.65rem;">!</span>
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-pill-link {{ request()->is('expenses*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                                <i class="bi bi-wallet2"></i>
                                <span>Pengeluaran</span>
                            </a>
                        </li>
                        @endif
                    </ul>

                    <!-- User Profile & Logout -->
                    <div class="d-flex align-items-center gap-2 flex-wrap pt-2 pt-lg-0 border-top border-lg-0">
                        <div class="user-chip">
                            <div class="user-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="d-flex flex-column text-start">
                                <span class="fw-bold text-dark lh-1" style="font-size: 0.8rem;">{{ Auth::user()->name }}</span>
                                <span class="text-muted lh-1 mt-1" style="font-size: 0.65rem;">{{ Auth::user()->getRoleLabel() }}</span>
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-soft btn-sm" title="Keluar dari akun">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                @else
                    <ul class="navbar-nav ms-auto gap-2">
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-outline-soft btn-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn-dark-brand btn-sm text-decoration-none">
                                <i class="bi bi-person-plus me-1"></i> Daftar
                            </a>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <main class="container my-4">
        @if(session('info'))
            <div class="alert alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important; color: #1e40af; border-radius: 12px;" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2 text-primary"></i>
                <div class="small">
                    {{ session('info') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; color: #065f46; border-radius: 12px;" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div class="small">
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="background: #fff1f2; border-left: 4px solid #f43f5e !important; color: #9f1239; border-radius: 12px;" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                <div class="small">
                    <strong>Perhatian!</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 untuk Notifikasi Pop-up -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    timer: 3500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    timerProgressBar: true,
                    customClass: {
                        popup: 'rounded-4 shadow-sm'
                    }
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian',
                    text: {!! json_encode(session('error')) !!},
                    toast: true,
                    position: 'top-end',
                    timer: 4000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-4 shadow-sm'
                    }
                });
            });
        </script>
    @endif

    <!-- Script Format & Hitungan Rupiah Otomatis -->
    <script>
        function formatRupiah(angka) {
            let number_string = String(angka).replace(/[^,\d]/g, '').toString();
            let split = number_string.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        }

        function updateRupiahCalculation() {
            const priceInput = document.getElementById('price');
            const stockInput = document.getElementById('stock');
            const helper = document.getElementById('price_helper');

            if (!priceInput) return;

            const cleanPrice = priceInput.value.replace(/[^0-9]/g, '');
            const priceVal = parseInt(cleanPrice, 10) || 0;

            let text = '';
            if (priceVal > 0) {
                text = '<span class="text-dark fw-semibold">Nilai Satuan: Rp ' + priceVal.toLocaleString('id-ID') + '</span>';
                if (stockInput && stockInput.value) {
                    const stockVal = parseInt(stockInput.value, 10) || 0;
                    if (stockVal > 0) {
                        const total = priceVal * stockVal;
                        text += ' &bull; <span class="text-success fw-semibold">Total Nilai Stok (' + stockVal + ' pcs): Rp ' + total.toLocaleString('id-ID') + '</span>';
                    }
                }
            }

            if (helper) {
                helper.innerHTML = text || '<small class="text-muted">Ketik nominal harga barang, pemisah ribuan titik akan terisi otomatis.</small>';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('price');
            const stockInput = document.getElementById('stock');

            if (priceInput) {
                if (priceInput.value) {
                    priceInput.value = formatRupiah(priceInput.value);
                    updateRupiahCalculation();
                }

                priceInput.addEventListener('input', function() {
                    this.value = formatRupiah(this.value);
                    updateRupiahCalculation();
                });
            }

            if (stockInput) {
                stockInput.addEventListener('input', function() {
                    updateRupiahCalculation();
                });
            }
        });

        @if(Auth::check() && Auth::user()->canManageOrders())
        let lastPendingCount = 0;
        let isFirstCheck = true;

        function checkNewOrders() {
            fetch("{{ route('notifications.unread_orders') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                const unreadCount = data.unread_count ?? data.count ?? 0;
                const activeOrders = data.recent || [];
                const badge = document.getElementById('notifBadge');
                const headerCount = document.getElementById('notifHeaderCount');
                const list = document.getElementById('notifList');

                // Badge merah di lonceng hanya menyala jika ada pesanan yang belum dibuka
                if (badge) {
                    if (unreadCount > 0) {
                        badge.innerText = unreadCount > 99 ? '99+' : unreadCount;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                }

                if (headerCount) {
                    if (unreadCount > 0) {
                        headerCount.className = 'badge bg-danger-subtle text-danger';
                        headerCount.innerText = `${unreadCount} Baru`;
                    } else {
                        headerCount.className = 'badge bg-secondary-subtle text-secondary';
                        headerCount.innerText = `${activeOrders.length} Menunggu`;
                    }
                }

                // Daftar pesanan yang belum CLOSE tetap tampil (hanya hilang jika status sudah selesai / close)
                if (list) {
                    if (activeOrders.length > 0) {
                        let html = '';
                        activeOrders.forEach(ord => {
                            const isPaid = ord.status === 'paid';
                            const badgeTag = isPaid 
                                ? `<span class="badge bg-primary-subtle text-primary" style="font-size: 0.62rem;">${ord.status_label || 'Diproses'}</span>` 
                                : `<span class="badge bg-warning-subtle text-dark" style="font-size: 0.62rem;">${ord.status_label || 'Menunggu'}</span>`;

                            const unreadDot = ord.is_unread ? '<span class="badge bg-danger p-1 rounded-circle me-1" style="width: 7px; height: 7px; display: inline-block;"></span>' : '';

                            html += `
                                <li class="border-bottom py-2">
                                    <a href="${ord.url}" class="dropdown-item p-2 rounded-2 text-wrap ${ord.is_unread ? 'bg-light' : ''}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-primary small">${unreadDot}${ord.invoice_number}</strong>
                                            ${badgeTag}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-dark small">${ord.customer_name}</span>
                                            <span class="text-muted" style="font-size: 0.68rem;">${ord.time_ago}</span>
                                        </div>
                                        <div class="text-success small fw-bold mt-1">Rp ${ord.total_price} (${ord.payment_method})</div>
                                    </a>
                                </li>
                            `;
                        });
                        list.innerHTML = html;
                    } else {
                        list.innerHTML = `
                            <li class="text-center text-muted small py-4" id="noNotifText">
                                <i class="bi bi-check2-circle fs-3 d-block text-success mb-1"></i>
                                Semua pesanan telah selesai (close)
                            </li>
                        `;
                    }
                }

                if (!isFirstCheck && unreadCount > lastPendingCount && typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: '🔔 Pesanan / Pembayaran Baru!',
                        text: `Ada ${unreadCount} pesanan baru yang membutuhkan tindakan.`,
                        toast: true,
                        position: 'top-end',
                        timer: 5000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    });
                }

                lastPendingCount = unreadCount;
                isFirstCheck = false;
            })
            .catch(() => {});
        }

        function markAllNotificationsRead() {
            fetch("{{ route('notifications.mark_all_read') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(() => {
                const badge = document.getElementById('notifBadge');
                if (badge) badge.classList.add('d-none');
                const headerCount = document.getElementById('notifHeaderCount');
                if (headerCount) headerCount.innerText = 'Sudah Dilihat';
                lastPendingCount = 0;
            })
            .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkNewOrders();
            setInterval(checkNewOrders, 10000);

            // Ketika Admin mengklik tombol lonceng untuk MEMBUKA notifikasi:
            // Tanda merahnya baru hilang!
            const notifDropdownBtn = document.getElementById('notifDropdown');
            if (notifDropdownBtn) {
                notifDropdownBtn.addEventListener('show.bs.dropdown', function () {
                    const badge = document.getElementById('notifBadge');
                    if (badge && !badge.classList.contains('d-none')) {
                        badge.classList.add('d-none');
                        markAllNotificationsRead();
                    }
                });
            }
        });
        @elseif(Auth::check())
        // SISI CUSTOMER: Notifikasi perubahan status pesanan & jika ada pesanan DIBATALKAN oleh Admin
        let lastCustomerCanceledCount = 0;
        let isFirstCustomerCheck = true;

        function checkCustomerNotifications() {
            fetch("{{ route('notifications.customer') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                const unreadCount = data.unread_count || 0;
                const canceledCount = data.canceled_count || 0;
                const items = data.items || [];
                const badge = document.getElementById('notifBadge');
                const navBadge = document.getElementById('customerNavBadge');
                const headerCount = document.getElementById('notifHeaderCount');
                const list = document.getElementById('notifList');

                if (unreadCount > 0) {
                    if (badge) {
                        badge.innerText = unreadCount > 99 ? '99+' : unreadCount;
                        badge.classList.remove('d-none');
                    }
                    if (navBadge) {
                        navBadge.innerText = unreadCount;
                        navBadge.classList.remove('d-none');
                    }
                    if (headerCount) {
                        if (canceledCount > 0) {
                            headerCount.className = 'badge bg-danger text-white';
                            headerCount.innerText = `${canceledCount} Dibatalkan`;
                        } else {
                            headerCount.className = 'badge bg-primary text-white';
                            headerCount.innerText = `${unreadCount} Baru`;
                        }
                    }
                } else {
                    if (badge) badge.classList.add('d-none');
                    if (navBadge) navBadge.classList.add('d-none');
                    if (headerCount) {
                        headerCount.className = 'badge bg-secondary-subtle text-secondary';
                        headerCount.innerText = `${items.length} Riwayat`;
                    }
                }

                if (list) {
                    if (items.length > 0) {
                        let html = '';
                        items.forEach(ord => {
                            const isCanceled = ord.status === 'canceled';
                            const titleColor = isCanceled ? 'text-danger' : 'text-primary';
                            const bgColor = isCanceled ? 'bg-danger-subtle' : '';

                            html += `
                                <li class="border-bottom py-2">
                                    <a href="${ord.url}" class="dropdown-item p-2 rounded-2 text-wrap ${bgColor}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="${titleColor} small">
                                                ${isCanceled ? '<i class="bi bi-x-circle-fill me-1 text-danger"></i>' : '<i class="bi bi-receipt me-1"></i>'}
                                                ${ord.invoice_number}
                                            </strong>
                                            <span class="badge ${ord.badge_class}" style="font-size: 0.62rem;">${ord.status_label}</span>
                                        </div>
                                        <div class="small fw-semibold ${isCanceled ? 'text-danger' : 'text-dark'}">${ord.title}</div>
                                        ${isCanceled && ord.cancellation_reason ? `<div class="small text-danger mt-1 p-1 rounded bg-white bg-opacity-75 border border-danger-subtle" style="font-size: 0.72rem;"><strong>Alasan:</strong> ${ord.cancellation_reason}</div>` : ''}
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-success small fw-bold">Rp ${ord.total_price}</span>
                                            <span class="text-muted" style="font-size: 0.68rem;">${ord.time_ago}</span>
                                        </div>
                                    </a>
                                </li>
                            `;
                        });
                        list.innerHTML = html;
                    } else {
                        list.innerHTML = `
                            <li class="text-center text-muted small py-4" id="noNotifText">
                                <i class="bi bi-bell-slash fs-3 d-block text-secondary mb-1"></i>
                                Belum ada notifikasi pesanan
                            </li>
                        `;
                    }
                }

                // Alert Toast pop-up HANYA jika benar-benar ada pesanan baru yang dibatalkan
                if (!isFirstCustomerCheck && canceledCount > lastCustomerCanceledCount && typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: '⚠️ Pesanan Dibatalkan',
                        text: 'Pemberitahuan: Ada pesanan Anda yang telah dibatalkan.',
                        confirmButtonColor: '#dc2626'
                    });
                }

                lastCustomerCanceledCount = canceledCount;
                isFirstCustomerCheck = false;
            })
            .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkCustomerNotifications();
            setInterval(checkCustomerNotifications, 4000);

            const notifDropdownBtn = document.getElementById('notifDropdown');
            if (notifDropdownBtn) {
                notifDropdownBtn.addEventListener('show.bs.dropdown', function () {
                    const badge = document.getElementById('notifBadge');
                    const navBadge = document.getElementById('customerNavBadge');
                    const headerCount = document.getElementById('notifHeaderCount');
                    const list = document.getElementById('notifList');
                    if (badge) badge.classList.add('d-none');
                    if (navBadge) navBadge.classList.add('d-none');
                    if (headerCount) headerCount.innerText = '0 Baru';

                    // Bersihkan daftar history notifikasi di layar
                    if (list) {
                        list.innerHTML = `
                            <li class="text-center text-muted small py-4" id="noNotifText">
                                <i class="bi bi-check2-circle fs-3 d-block text-success mb-1"></i>
                                Tidak ada notifikasi baru
                            </li>
                        `;
                    }

                    // Tandai notifikasi dibaca dan bersihkan history lonceng pelanggan di database
                    fetch("{{ route('notifications.customer_mark_read') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(() => {
                        lastCustomerCanceledCount = 0;
                    })
                    .catch(() => {});
                });
            }

            @if(request()->is('my-orders*'))
            // Jika pelanggan sudah membuka halaman Pesanan Saya, otomatis bersihkan badge merah
            fetch("{{ route('notifications.customer_mark_read') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(() => {});
            @endif
        });
        @endif

        // Session Lifecycle: Logout otomatis jika tab/browser ditutup atau riwayat dibersihkan (mencegah auto-login)
        @auth
        (function() {
            var sessionKey = 'warunggenz_tab_session';
            var channelName = 'warunggenz_tab_sync';

            // Bersihkan jika pengguna mengklik link / tombol logout
            document.querySelectorAll('a[href*="logout"], form[action*="logout"]').forEach(function(el) {
                el.addEventListener('click', function() {
                    sessionStorage.removeItem(sessionKey);
                });
            });

            if (!sessionStorage.getItem(sessionKey)) {
                // Tab ini baru dibuka tanpa session storage aktif.
                // Tanyakan ke tab lain (jika ada yang masih terbuka di browser)
                if (typeof BroadcastChannel !== 'undefined') {
                    var channel = new BroadcastChannel(channelName);
                    channel.postMessage({ action: 'QUERY_ACTIVE_SESSION' });
                    channel.onmessage = function(ev) {
                        if (ev.data && ev.data.action === 'SESSION_ACTIVE_REPLY') {
                            sessionStorage.setItem(sessionKey, 'active');
                        }
                    };
                }

                setTimeout(function() {
                    if (!sessionStorage.getItem(sessionKey)) {
                        // Tidak ada tab yang aktif sebelumnya (user baru saja membuka tab baru setelah keluar/tutup tab)
                        // Segera logout agar tidak auto-login!
                        window.location.replace("{{ route('logout') }}");
                    }
                }, 200);
            } else {
                // Tab ini aktif, siap menjawab query dari tab baru yang dibuka
                if (typeof BroadcastChannel !== 'undefined') {
                    var channel = new BroadcastChannel(channelName);
                    channel.onmessage = function(ev) {
                        if (ev.data && ev.data.action === 'QUERY_ACTIVE_SESSION') {
                            channel.postMessage({ action: 'SESSION_ACTIVE_REPLY' });
                        }
                    };
                }
            }
        })();
        @else
        // Di halaman guest (Login / Register): Pasang listener form submit
        document.addEventListener('DOMContentLoaded', function() {
            sessionStorage.removeItem('warunggenz_tab_session');
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    sessionStorage.setItem('warunggenz_tab_session', 'active');
                });
            });
        });
        @endauth
    </script>
</body>
</html>
