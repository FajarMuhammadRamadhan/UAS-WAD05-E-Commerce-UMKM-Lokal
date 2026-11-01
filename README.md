# WarungGenZ - Sistem E-Commerce & Manajemen Produk UMKM Lokal

> **Tugas Ujian Akhir Semester (UAS) Web Application Development**  
> **Universitas Cakrawala**  
> 
> * **Nama Mahasiswa:** Fajar Muhammad Ramadhan
> * **NIM:** 25120300023
> * **Program Studi:** Computer Science
> * **Mata Kuliah:** Web Application Development
> * **Demo Live / Server IP Public:** [http://103.142.202.202](http://103.142.202.202)

---

## 📌 Ringkasan Project

**WarungGenZ** adalah aplikasi web inventaris dan belanja online (*e-commerce*) yang dirancang khusus untuk memenuhi kebutuhan digitalisasi toko kelontong dan UMKM lokal. Aplikasi ini memungkinkan pemilik toko mengelola produk, stok, transaksi penjualan toko fisik (POS), pengeluaran operasional, serta menerima pesanan belanja online dari pelanggan secara *real-time*.

Aplikasi dibangun menggunakan arsitektur **Model-View-Controller (MVC)** dengan framework **Laravel 12**, basis data relasional **MySQL**, antarmuka responsif **Bootstrap 5**, serta notifikasi interaktif **SweetAlert2**.

---

## 🚀 Tech Stack

* **Backend Framework:** Laravel 12 (PHP 8.2+)
* **Database:** MySQL (Engine InnoDB, Relasional 6 Tabel)
* **Frontend:** Blade Templating, Bootstrap 5.3, Bootstrap Icons
* **UI/UX:** Mobile-First Responsive, Clean Modern Retail Design System
* **Alert & Pop-up:** SweetAlert2
* **Web Server Live:** Nginx + PHP 8.3-FPM di Ubuntu Server (Cloud VM)

---

## 🗄️ Struktur Database Relasional (6 Tabel)

Database dirancang dengan relasi integritas referensial yang kuat antar entitas:

```
users (1) --------< orders (N) --------< order_items (N)
                                              |
categories (1) ---< products (N) -------------+
users (1) --------< expenses (N)
```

1. **`users`** : Menyimpan akun pengguna dengan sistem role (`admin`, `customer`).
2. **`categories`** : Daftar kategori barang (Sembako, Makanan & Minuman, Kebutuhan Rumah Tangga, Perawatan Tubuh, Lainnya).
3. **`products`** : Data katalog produk (nama, harga, stok, deskripsi, gambar) berelasi dengan `category_id`.
4. **`orders`** : Data transaksi dan pesanan (nomor faktur, data pemesan, alamat, metode pembayaran, status, catatan pembatalan) berelasi dengan `user_id`.
5. **`order_items`** : Rincian item produk pada setiap pesanan (kuantitas, harga saat transaksi) berelasi dengan `order_id` dan `product_id`.
6. **`expenses`** : Catatan pengeluaran operasional warung berelasi dengan `user_id`.

---

## ✨ Fitur Utama Aplikasi

### 1. Autentikasi & Multi-Role Access Control
* **2 Hak Akses (Role):**
  * **Admin:** Akses penuh ke seluruh fitur (CRUD katalog produk, transaksi penjualan toko fisik / POS, laporan penjualan, pengeluaran operasional, rekap bulanan, serta manajemen status & pembatalan pesanan).
  * **Customer (Pelanggan):** Akses ke katalog produk, pemesanan online (*checkout*), riwayat pesanan (*My Orders*), ganti metode bayar, dan pembatalan pesanan mandiri.
* Proteksi rute dengan Middleware Laravel, session berbasis database, dan keamanan CSRF Token di seluruh form.

### 2. Belanja Online & Checkout Pelanggan
* **Katalog Produk Dinamis:** Pencarian nama produk, filter kategori, dan indikator status stok (*Tersedia, Menipis, Habis*).
* **Multi-Item Order:** Tambah beberapa item sekaligus dengan *quantity stepper* yang mudah ditekan di layar HP.
* **3 Metode Pembayaran:**
  * **COD (Cash on Delivery):** Bayar tunai ke kurir saat barang tiba.
  * **Transfer Bank Manual:** Nomor rekening resmi BCA & Mandiri otomatis tertera pada nota.
  * **QRIS:** Barcode QRIS dinamis untuk scan lewat e-wallet (GoPay, OVO, Dana, ShopeePay, Mobile Banking).
* **Ganti Metode Pembayaran:** Pelanggan dapat sewaktu-waktu mengganti metode pembayaran jika salah pilih di awal selama pesanan masih *pending*.
* **Pembatalan Pesanan oleh Pelanggan:** Pelanggan dapat membatalkan pesanan mandiri dengan menyertakan alasan pembatalan; stok produk otomatis dipulihkan ke inventaris toko.

### 3. Pelacakan Pesanan & Nota Penjualan Resmi
* **Status Tracker 3 Tahap:** *1. Diterima / Menunggu Bayar* &rarr; *2. Diproses / Lunas* &rarr; *3. Selesai Diterima*.
* **Auto-Sync Real-Time:** Status pesanan di layar pelanggan otomatis terbarui tanpa reload manual ketika admin mengubah status transaksi.
* **Cetak Faktur Standar Retail:** Tata letak faktur siap cetak ukuran A4/struk dengan header resmi, rincian barang, total pembayaran, catatan penukaran 1x24 jam, serta tanda tangan petugas/admin toko.

### 4. Transaksi Penjualan Toko (Point of Sale / POS)
* Pencatatan penjualan langsung di toko fisik dengan pemilih produk (*product picker*) interaktif dan pencarian cepat.
* Pengurangan stok produk otomatis secara aman melalui *database transaction lock*.
* Pembatalan transaksi oleh Admin disertai kolom catatan/alasan bebas yang langsung dikirim ke notifikasi pelanggan.

### 5. Ringkasan Keuangan & Manajemen Pengeluaran
* **Statistik Dashboard:** Total pemasukan, rekap penjualan bulanan, total pengeluaran operasional, saldo bersih (*net profit*), serta valuasi nilai aset stok.
* **Filter Rekap Per Bulan:** Pemilik toko dapat melihat rincian histori transaksi berdasarkan kelompok bulan tertentu.
* **Catatan Pengeluaran (`/expenses`):** Input biaya operasional, belanja kulakan stok, tagihan listrik/air, dan operasional lainnya.

### 6. Desain Antarmuka Mobile-First & Modern Retail UI
* **Mobile-First Experience:** Tampilan dioptimalkan khusus untuk layar smartphone dengan kartu pesanan vertikal tanpa scroll horizontal, target sentuh tombol minimal 44px, serta tata letak navigasi yang ergonomis.
* **Clean & Professional UI:** Mengadopsi prinsip desain retail modern dengan palet warna yang bersih, kontras warna yang nyaman di mata, serta tipografi yang jelas dan mudah dibaca oleh pengguna.

---

## 🔐 Akun Login untuk Pengujian (Testing Credentials)

Aplikasi dapat langsung diuji di **[http://103.142.202.202](http://103.142.202.202)** atau di server lokal dengan akun berikut:

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `testaja@gmail.com` | `12345678` | Akses penuh (Produk, Penjualan Toko, Pengeluaran, Finansial) |
| **Admin Sekunder** | `testweb@gmail.com` | `12345678` | Akses penuh manajemen toko |
| **Customer (Pelanggan)** | `testcustomer@gmail.com` | `12345678` | Belanja online, checkout, nota saya, pembatalan |
| **Customer Baru** | *Daftar via menu Register* | *Bebas (min. 8 karakter)* | Otomatis terdaftar sebagai role `customer` |

> 💡 **Tips Pengujian Concurrent (Multi-Device):**  
> Anda dapat membuka **Laptop sebagai Admin** (`testaja@gmail.com`) dan **HP sebagai Customer** (`testcustomer@gmail.com`). Ketika Customer di HP melakukan checkout atau konfirmasi pembayaran, lonceng notifikasi di laptop Admin akan berbunyi dan memunculkan badge merah secara *real-time*.

---

## 🛠️ Panduan Instalasi Lokal (Local Setup)

### 1. Prasyarat Sistem
* PHP >= 8.2 (dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`)
* Composer >= 2.x
* MySQL Server / XAMPP
* Git

### 2. Langkah-Langkah Instalasi

1. **Clone repository:**
   ```bash
   git clone https://github.com/FajarMuhammadRamadhan/UAS-WAD05-E-Commerce-UMKM-Lokal.git
   cd UAS-WAD05-E-Commerce-UMKM-Lokal
   ```

2. **Install dependensi Composer:**
   ```bash
   composer install
   ```

3. **Konfigurasi file `.env`:**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Sesuaikan kredensial database Anda pada file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=warunggenz_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database & Seeder:**
   Buat database bernama `warunggenz_db` di MySQL / phpMyAdmin, lalu jalankan:
   ```bash
   php artisan migrate --seed
   ```
   *(Atau import file SQL siap pakai `warunggenz_db.sql` yang tersedia di direktori utama).*

6. **Membuat Symlink Storage Gambar:**
   ```bash
   php artisan storage:link
   ```

7. **Jalankan Automated Tests (Verifikasi Sistem):**
   ```bash
   php artisan test
   ```
   *(Memastikan seluruh 17 skenario test case lulus tanpa error).*

8. **Jalankan Local Development Server:**
   ```bash
   php artisan serve
   ```
   Aplikasi siap diakses melalui browser pada: **`http://127.0.0.1:8000`**

---

## 🧪 Pengujian Otomatis (Automated Testing)

Project ini dilengkapi dengan **17 skenario automated feature & unit tests** (`118 assertions`) menggunakan PHPUnit / Laravel Test Suite yang mencakup:
* Proteksi hak akses role (Customer tidak bisa membuka menu manajemen Admin).
* Pengurangan dan pemulihan stok otomatis saat transaksi dibuat atau dibatalkan.
* Pembatalan transaksi ganda (*concurrency lock safety*).
* Skenario checkout belanja online customer hingga penerimaan notifikasi admin.
* Fitur ganti metode pembayaran dan pembatalan pesanan mandiri oleh customer.
* Fitur pembatalan transaksi oleh admin dengan catatan penyebab kustom.
* Rekap pendapatan bulanan dan filter pencarian produk.

Untuk menjalankan seluruh rangkaian pengujian:
```bash
php artisan test
```

---

## 📂 Struktur Direktori Project (MVC)

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php       # Login, Register, Logout
│   │   │   ├── ExpenseController.php    # Manajemen Pengeluaran Operasional
│   │   │   ├── OrderController.php      # Transaksi Penjualan, Checkout, Nota, Notifikasi
│   │   │   └── ProductController.php    # CRUD Produk, Kategori, Dashboard Finansial
│   │   └── Middleware/                  # Gate otorisasi Admin & Customer
│   └── Models/
│       ├── Category.php                 # Model Kategori Produk
│       ├── Expense.php                  # Model Pengeluaran
│       ├── Order.php                    # Model Transaksi Penjualan & Alur Status
│       ├── OrderItem.php                # Model Item Rincian Transaksi
│       ├── Product.php                  # Model Produk & Kontrol Stok
│       └── User.php                     # Model Pengguna & Role Helper
├── database/
│   ├── migrations/                      # 6 Berkas Migrasi Skema Tabel Database
│   └── seeders/                         # Seeder Data Awal Akun, Kategori, & Produk
├── public/
│   ├── css/custom.css                   # Stylesheet Khusus Mobile-First & Desain Responsif
│   ├── warunggenz.svg                   # Logo Identitas Brand Toko
│   └── index.php                        # Entry Point Aplikasi
├── resources/views/
│   ├── auth/                            # Tampilan Login & Register
│   ├── expenses/                        # Tampilan Pencatatan Pengeluaran
│   ├── layouts/                         # Master Layout & Navbar Responsif
│   ├── orders/                          # View Transaksi Penjualan, Checkout, Pesanan Saya, Nota
│   └── products/                        # View Katalog, Tambah/Edit Produk
├── routes/
│   └── web.php                          # Deklarasi Routing & Group Middleware
├── tests/
│   ├── Feature/
│   │   └── WarungGenZWorkflowTest.php   # 17 Test Case Alur Lengkap Sistem
│   └── Unit/
├── warunggenz_db.sql                    # Backup Dump SQL Database
├── README.md                            # Panduan & Dokumentasi Repository GitHub
└── setup-nginx.sh                       # Script Otomasi Deployment Server Ubuntu
```

---

## 📄 Lisensi & Hak Cipta

Project ini dikembangkan sebagai karya akademik Ujian Akhir Semester (UAS) pada Program Studi Computer Science, Universitas Cakrawala.  
Dilarang menyalin atau menduplikasi sebagian atau seluruh kode sumber untuk keperluan plagiarisme akademik.

&copy; 2026 **Fajar Muhammad Ramadhan (25120300023)** &mdash; All Rights Reserved.
