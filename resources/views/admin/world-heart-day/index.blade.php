@extends('layouts.admin')
@section('title', 'World Heart Day')
@section('page-title', 'World Heart Day Campaign')

@push('styles')
<style>
    .whd-file,.whd-select{padding:9px 12px;border:1px solid var(--border);border-radius:9px;background:var(--surface);color:var(--text)}
    .whd-header-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.whd-filter-selects{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .whd-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;border:0;border-radius:9px;background:#dc2626;color:#fff;font-weight:700;cursor:pointer;text-decoration:none}
    .whd-btn.secondary{background:#2563eb}.whd-btn.dark{background:#334155}
    .whd-table-wrap{overflow:auto}.whd-table{width:100%;border-collapse:collapse;min-width:1200px}
    .whd-table th,.whd-table td{padding:11px 10px;border-bottom:1px solid var(--border);text-align:left;vertical-align:middle;font-size:13px}
    .whd-table th{color:var(--muted);white-space:nowrap}.whd-thumb{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border)}
    .preview-trigger{border:0;background:transparent;padding:0;cursor:pointer}.banner-btn{padding:7px 11px;border:0;border-radius:8px;background:#7c3aed;color:#fff;font-weight:700;cursor:pointer}
    .gender-form,.banner-actions{display:flex;gap:5px;align-items:center}.gender-form select{padding:6px;border:1px solid var(--border);border-radius:7px;background:var(--surface);color:var(--text)}.mini-btn{padding:6px 8px;border:0;border-radius:7px;background:#0f766e;color:#fff;cursor:pointer}.delete-banner-btn{background:#dc2626}
    .whd-alert{padding:12px 16px;border-radius:9px;margin-bottom:14px}.success{background:#dcfce7;color:#166534}.warning{background:#fef3c7;color:#92400e}.error{background:#fee2e2;color:#991b1b}
    .preview-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(2,6,23,.9)}.preview-modal.open{display:flex}
    .preview-modal img{max-width:94vw;max-height:88vh;border-radius:10px}.preview-close{position:fixed;top:20px;right:25px;background:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:25px;cursor:pointer}
    .import-modal{position:fixed;inset:0;z-index:9998;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(2,6,23,.8)}.import-modal.open{display:flex}
    .import-dialog{width:min(480px,95vw);padding:24px;border:1px solid var(--border);border-radius:14px;background:var(--surface);box-shadow:0 25px 70px rgba(0,0,0,.45)}
    .import-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.import-title h3{margin:0;color:var(--text)}.import-x{border:0;background:transparent;color:var(--text);font-size:25px;cursor:pointer}.import-dialog .whd-file{display:block;width:100%;box-sizing:border-box;margin-bottom:15px}
    .js-pagination{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-top:18px}.pagination-info{font-size:13px;color:var(--muted)}.pagination-buttons{display:flex;gap:6px;align-items:center}.page-button{min-width:36px;height:36px;padding:0 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-weight:700;cursor:pointer}.page-button:hover:not(:disabled),.page-button.active{background:#2563eb;border-color:#2563eb;color:#fff}.page-button:disabled{opacity:.4;cursor:not-allowed}
    @media(max-width:700px){.whd-header-actions,.whd-header-actions .whd-btn,.whd-filter-selects,.whd-select{width:100%}.whd-file{max-width:100%}}
</style>
@endpush

@section('content')
    @if(session('success'))<div class="whd-alert success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="whd-alert warning">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="whd-alert error">{{ $errors->first() }}</div>@endif

    <div class="card">
        <div class="card-header">
            <div><div class="card-title">World Heart Day Doctors</div><div class="card-sub">{{ number_format($entries->total()) }} Records Found</div></div>
            <div class="whd-header-actions">
                <button class="whd-btn" type="button" id="openImport"><i class="fas fa-file-import"></i> Import Excel</button>
                <a class="whd-btn secondary" href="{{ route('admin.world-heart-day.export', request()->query()) }}"><i class="fas fa-file-excel"></i> Export Excel</a>
                <a class="whd-btn dark" href="{{ route('admin.world-heart-day.download-banners', request()->query()) }}"><i class="fas fa-file-zipper"></i> Download Banner ZIP</a>
            </div>
        </div>

        <form method="GET">
            <div class="filters-bar">
                <div class="search-box"><i class="fas fa-search"></i><input name="search" value="{{ request('search') }}" placeholder="Search by doctor name, MSL, employee or speciality..." autocomplete="off"></div>
                <div class="whd-filter-selects">
                    <select class="whd-select" name="speciality"><option value="">All specialities</option>@foreach($specialities as $speciality)<option value="{{ $speciality }}" @selected(request('speciality') === $speciality)>{{ $speciality }}</option>@endforeach</select>
                    <select class="whd-select" name="gender"><option value="">All genders</option><option value="Male" @selected(request('gender') === 'Male')>Male</option><option value="Female" @selected(request('gender') === 'Female')>Female</option></select>
                    <select class="whd-select" name="banner"><option value="">All banners</option><option value="ready" @selected(request('banner') === 'ready')>Banner ready</option><option value="pending" @selected(request('banner') === 'pending')>Banner pending</option></select>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                @if(request()->hasAny(['search','speciality','gender','banner']))<a class="btn btn-ghost" href="{{ route('admin.world-heart-day.index') }}"><i class="fas fa-times"></i> Reset</a>@endif
            </div>
        </form>

        <div class="whd-table-wrap">
            <table class="whd-table">
                <thead><tr><th>Sr.</th><th>Photo</th><th>Doctor</th><th>MSL Code</th><th>Speciality</th><th>Employee</th><th>Employee Code</th><th>Gender</th><th>Banner</th></tr></thead>
                <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ $entry->source_row ?? '-' }}</td>
                        <td>
                            @if($entry->photo_url)
                                <button class="preview-trigger" type="button" data-image="{{ $entry->photo_url }}" data-alt="{{ $entry->doctor_name }} photo"><img class="whd-thumb" src="{{ $entry->photo_url }}" alt="{{ $entry->doctor_name }}" loading="lazy"></button>
                            @else - @endif
                        </td>
                        <td><strong>{{ $entry->doctor_name }}</strong></td>
                        <td>{{ $entry->msl_code }}</td>
                        <td>{{ $entry->speciality ?? '-' }}</td>
                        <td>{{ $entry->employee_name ?? '-' }}</td>
                        <td>{{ $entry->employee_code ?? '-' }}</td>
                        <td>
                            <form class="gender-form" method="POST" action="{{ route('admin.world-heart-day.gender', $entry) }}">
                                @csrf
                                <select name="gender" aria-label="Gender for {{ $entry->doctor_name }}">
                                    <option value="">Select</option>
                                    <option value="Male" @selected($entry->gender === 'Male')>Male</option>
                                    <option value="Female" @selected($entry->gender === 'Female')>Female</option>
                                </select>
                                <button class="mini-btn" type="submit" title="Save gender"><i class="fas fa-save"></i></button>
                            </form>
                        </td>
                        <td>
                            @if($entry->banner_path)
                                <div class="banner-actions">
                                    <button class="preview-trigger banner-btn" type="button" data-image="https://swarnimpolling.s3.ap-south-1.amazonaws.com/{{ $entry->banner_path }}" data-alt="{{ $entry->doctor_name }} banner"><i class="fas fa-eye"></i> Preview</button>
                                    <form class="delete-banner-form" method="POST" action="{{ route('admin.world-heart-day.banner.delete', $entry) }}" data-doctor="{{ $entry->doctor_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="mini-btn delete-banner-btn" type="submit" title="Delete generated banner"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </div>
                            @else
                                <span style="color:var(--muted)">Save gender to generate</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;padding:35px;color:var(--muted)">No records found. Search/filter clear karein ya Excel import karein.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->total())
            <div class="js-pagination" id="jsPagination">
                <div class="pagination-info">Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ number_format($entries->total()) }}</div>
                <div class="pagination-buttons">
                    <button type="button" class="page-button" data-page-url="{{ $entries->previousPageUrl() }}" @disabled($entries->onFirstPage()) aria-label="Previous page">&lsaquo;</button>
                    @foreach($entries->getUrlRange(max(1, $entries->currentPage() - 2), min($entries->lastPage(), $entries->currentPage() + 2)) as $page => $url)
                        <button type="button" class="page-button {{ $page === $entries->currentPage() ? 'active' : '' }}" data-page-url="{{ $url }}" @disabled($page === $entries->currentPage())>{{ $page }}</button>
                    @endforeach
                    <button type="button" class="page-button" data-page-url="{{ $entries->nextPageUrl() }}" @disabled(!$entries->hasMorePages()) aria-label="Next page">&rsaquo;</button>
                </div>
            </div>
        @endif
    </div>

    <div id="importModal" class="import-modal" aria-hidden="true">
        <div class="import-dialog">
            <div class="import-title"><h3><i class="fas fa-file-import"></i> Import World Heart Day Excel</h3><button class="import-x" type="button" aria-label="Close">&times;</button></div>
            <form method="POST" action="{{ route('admin.world-heart-day.import') }}" enctype="multipart/form-data">
                @csrf
                <input class="whd-file" type="file" name="file" accept=".xlsx,.xls,.csv" required>
                <button class="whd-btn" type="submit"><i class="fas fa-upload"></i> Upload & Import</button>
            </form>
        </div>
    </div>
    <div id="previewModal" class="preview-modal" aria-hidden="true"><button class="preview-close" type="button">&times;</button><img src="" alt=""></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (() => {
            const importModal = document.getElementById('importModal');
            const closeImport = () => { importModal.classList.remove('open'); importModal.setAttribute('aria-hidden','true'); };
            document.getElementById('openImport').addEventListener('click', () => { importModal.classList.add('open'); importModal.setAttribute('aria-hidden','false'); });
            importModal.querySelector('.import-x').addEventListener('click', closeImport);
            importModal.addEventListener('click', event => { if(event.target === importModal) closeImport(); });
            document.querySelectorAll('#jsPagination [data-page-url]').forEach(button => button.addEventListener('click', () => {
                if (!button.disabled && button.dataset.pageUrl) window.location.assign(button.dataset.pageUrl);
            }));
            document.querySelectorAll('.delete-banner-form').forEach(form => form.addEventListener('submit', event => {
                event.preventDefault();
                Swal.fire({
                    title: 'Delete generated banner?',
                    text: `${form.dataset.doctor || 'Doctor'} ka banner S3 se permanently delete ho jayega.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(result => { if (result.isConfirmed) form.submit(); });
            }));
            const modal = document.getElementById('previewModal'); const image = modal.querySelector('img');
            const close = () => { modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); image.src=''; };
            document.querySelectorAll('.preview-trigger').forEach(button => button.addEventListener('click', () => { image.src=button.dataset.image; image.alt=button.dataset.alt || 'Preview'; modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); }));
            modal.querySelector('.preview-close').addEventListener('click', close); modal.addEventListener('click', event => { if(event.target === modal) close(); });
            document.addEventListener('keydown', event => { if(event.key === 'Escape') { close(); closeImport(); } });
        })();
    </script>
@endsection
