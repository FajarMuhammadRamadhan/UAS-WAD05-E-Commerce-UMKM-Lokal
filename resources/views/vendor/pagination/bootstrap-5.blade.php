@if ($paginator->total() > 0)
    @php
        $currentPerPage = (string) request('per_page', '10');
        $perPageOptions = [
            '10' => '10',
            '20' => '20',
            '50' => '50',
            'all' => 'Semua',
        ];
    @endphp
    <nav class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 w-100 p-3 bg-white modern-card" aria-label="Navigasi Halaman">
        <!-- Kolom Pilihan Tampilkan Data per Halaman -->
        <div class="d-flex align-items-center gap-2">
            <label for="per-page-select" class="small text-muted mb-0 fw-medium">Tampilkan:</label>
            <select id="per-page-select" onchange="window.location.href=this.value" class="form-select form-select-sm" style="width: auto; min-width: 95px; border-radius: 8px; border-color: #cbd5e1; font-size: 0.85rem; font-weight: 500; cursor: pointer;">
                @foreach($perPageOptions as $val => $label)
                    @php
                        $isSelected = ($val === 'all' && ($currentPerPage === 'all' || $currentPerPage === 'semua')) 
                            || ($val === $currentPerPage);
                    @endphp
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $val, 'page' => 1]) }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tombol Halaman Modern (hanya tampil jika lebih dari 1 halaman) -->
        @if ($paginator->hasPages())
            <div>
                <ul class="pagination pagination-sm m-0 gap-1 align-items-center">
                    {{-- Tombol Sebelumnya --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link text-muted border-0 bg-transparent py-1 px-2" style="border-radius: 8px;">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link text-dark border py-1 px-2 text-decoration-none" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="border-radius: 8px; border-color: #e2e8f0 !important;" title="Halaman Sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Nomor Halaman --}}
                    @foreach ($elements as $element)
                        {{-- Separator titik-titik --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link border-0 bg-transparent text-muted px-2 py-1">{{ $element }}</span>
                            </li>
                        @endif

                        {{-- Link Nomor Halaman --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link fw-bold border-0 text-white px-3 py-1 shadow-sm" style="background-color: #0f172a !important; border-radius: 8px;">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link text-dark border px-3 py-1 text-decoration-none" href="{{ $url }}" style="border-radius: 8px; border-color: #e2e8f0 !important; background-color: #ffffff;">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Tombol Selanjutnya --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link text-dark border py-1 px-2 text-decoration-none" href="{{ $paginator->nextPageUrl() }}" rel="next" style="border-radius: 8px; border-color: #e2e8f0 !important;" title="Halaman Selanjutnya">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link text-muted border-0 bg-transparent py-1 px-2" style="border-radius: 8px;">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        @else
            <div></div>
        @endif
    </nav>
@endif
