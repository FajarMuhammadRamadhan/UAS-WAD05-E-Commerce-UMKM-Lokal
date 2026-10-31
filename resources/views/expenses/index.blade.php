@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4 g-2">
    <div class="col-lg-6">
        <h3 class="section-title mb-1">Catatan Pengeluaran Toko</h3>
        <p class="section-subtitle mb-0">Kelola biaya operasional, belanja stok, tagihan utilitas, dan pengeluaran UMKM lainnya.</p>
    </div>
    <div class="col-lg-6 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end mt-2 mt-lg-0">
        <a href="{{ route('expenses.create') }}" class="btn-dark-brand text-decoration-none">
            <i class="bi bi-dash-circle"></i>
            <span>Catat Pengeluaran</span>
        </a>
        <a href="{{ route('products.index') }}" class="btn-outline-soft text-decoration-none">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="modern-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Total Akumulasi Pengeluaran</span>
                <div class="stat-icon-wrapper" style="background: #fff1f2; color: #e11d48;">
                    <i class="bi bi-arrow-up-right"></i>
                </div>
            </div>
            <div class="stat-value text-danger">
                Rp {{ number_format($totalExpenses, 0, ',', '.') }}
            </div>
            <div class="stat-subtext mt-1">
                <span>Total seluruh biaya yang tercatat</span>
            </div>
        </div>
    </div>
</div>

<!-- Filter Pencarian Pengeluaran -->
<form action="{{ route('expenses.index') }}" method="GET" class="filter-card mb-4">
    @if(request('per_page'))
        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    @endif
    <div class="row g-2 align-items-end">
        <div class="col-md-7">
            <label for="expense-search" class="form-label">Cari Catatan Pengeluaran</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                    <i class="bi bi-search"></i>
                </span>
                <input id="expense-search" type="search" name="search" value="{{ $search }}" class="form-control border-start-0" style="border-radius: 0 10px 10px 0;" placeholder="Ketik nama atau keterangan pengeluaran...">
            </div>
        </div>
        <div class="col-md-3">
            <label for="expense-category" class="form-label">Kategori Pengeluaran</label>
            <select id="expense-category" name="category" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['category'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-dark-brand flex-grow-1 justify-content-center">
                Filter
            </button>
            <a href="{{ route('expenses.index') }}" class="btn-outline-soft justify-content-center" title="Reset filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </div>
</form>

<!-- Modern Table Pengeluaran -->
<div class="modern-table-container mb-4">
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 130px;">Tanggal</th>
                    <th>Nama Pengeluaran</th>
                    <th>Kategori</th>
                    <th>Keterangan Tambahan</th>
                    <th>Dicatat Oleh</th>
                    <th class="text-end" style="width: 160px;">Nominal (Rp)</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td class="small text-muted fw-semibold">
                        {{ $expense->expense_date->format('d M Y') }}
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $expense->title }}</div>
                    </td>
                    <td>
                        <span class="badge-soft badge-soft-slate">
                            <i class="bi bi-tag" style="font-size: 0.65rem;"></i>
                            {{ $categories[$expense->category] ?? $expense->category }}
                        </span>
                    </td>
                    <td class="small text-muted">
                        {{ $expense->description ?: '-' }}
                    </td>
                    <td class="small text-muted">
                        {{ $expense->user->name ?? '-' }}
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-danger">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pengeluaran {{ $expense->title }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-outline-soft btn-action-sm text-danger" title="Hapus catatan">
                                <i class="bi bi-trash3"></i>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <span class="fw-semibold">Belum ada catatan pengeluaran.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-5">
    {{ $expenses->links() }}
</div>
@endsection
