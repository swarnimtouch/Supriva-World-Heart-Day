@extends('layouts.admin')
@section('title', 'World Heart Day')
@section('page-title', 'World Heart Day Campaign')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .whd-file,.whd-select{padding:9px 12px;border:1px solid var(--border);border-radius:9px;background:var(--surface);color:var(--text)}
    .whd-header-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.whd-filter-selects{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .whd-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;border:0;border-radius:9px;background:#dc2626;color:#fff;font-weight:700;cursor:pointer;text-decoration:none}
    .whd-btn.secondary{background:#2563eb}.whd-btn.dark{background:#334155}
    .employee-select-wrap{min-width:260px}.employee-select-wrap .select2-container{width:100%!important}.employee-select-wrap .select2-selection--single{height:38px!important;border:1px solid var(--border)!important;border-radius:9px!important;background:var(--surface)!important}.employee-select-wrap .select2-selection__rendered{line-height:36px!important;color:var(--text)!important;padding-left:12px!important}.employee-select-wrap .select2-selection__arrow{height:36px!important}.select2-dropdown{border-color:var(--border)!important;background:var(--surface)!important;color:var(--text)!important}.select2-search__field{border:1px solid var(--border)!important;border-radius:6px;background:var(--surface)!important;color:var(--text)!important}.select2-results__option--highlighted.select2-results__option--selectable{background:#2563eb!important}
    .whd-table-wrap{overflow:auto}.whd-table{width:100%;border-collapse:collapse;min-width:1200px}
    .whd-table th,.whd-table td{padding:11px 10px;border-bottom:1px solid var(--border);text-align:left;vertical-align:middle;font-size:13px}
    .whd-table th{color:var(--muted);white-space:nowrap}.whd-thumb{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border)}
    .preview-trigger{border:0;background:transparent;padding:0;cursor:pointer}.banner-btn{padding:7px 11px;border:0;border-radius:8px;background:#7c3aed;color:#fff;font-weight:700;cursor:pointer}
    .gender-form,.banner-actions{display:flex;gap:5px;align-items:center}.gender-form select{padding:6px;border:1px solid var(--border);border-radius:7px;background:var(--surface);color:var(--text)}.mini-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 8px;border:0;border-radius:7px;background:#0f766e;color:#fff;cursor:pointer;text-decoration:none;white-space:nowrap}.download-banner-btn{background:#2563eb}.delete-banner-btn{background:#dc2626}
    .whd-alert{padding:12px 16px;border-radius:9px;margin-bottom:14px}.success{background:#dcfce7;color:#166534}.warning{background:#fef3c7;color:#92400e}.error{background:#fee2e2;color:#991b1b}
    .preview-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(2,6,23,.9)}.preview-modal.open{display:flex}
    .preview-modal img{max-width:94vw;max-height:88vh;border-radius:10px}.preview-close{position:fixed;top:20px;right:25px;background:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:25px;cursor:pointer}
    .import-modal{position:fixed;inset:0;z-index:9998;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(2,6,23,.8)}.import-modal.open{display:flex}
    .import-dialog{width:min(480px,95vw);padding:24px;border:1px solid var(--border);border-radius:14px;background:var(--surface);box-shadow:0 25px 70px rgba(0,0,0,.45)}
    .import-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.import-title h3{margin:0;color:var(--text)}.import-x{border:0;background:transparent;color:var(--text);font-size:25px;cursor:pointer}.import-dialog .whd-file{display:block;width:100%;box-sizing:border-box;margin-bottom:15px}
    .js-pagination{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-top:18px}.pagination-info{font-size:13px;color:var(--muted)}.pagination-buttons{display:flex;gap:6px;align-items:center}.page-button{min-width:36px;height:36px;padding:0 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-weight:700;cursor:pointer}.page-button:hover:not(:disabled),.page-button.active{background:#2563eb;border-color:#2563eb;color:#fff}.page-button:disabled{opacity:.4;cursor:not-allowed}
    .banner-loader{position:fixed;inset:0;z-index:10050;display:none;align-items:center;justify-content:center;background:rgba(2,6,23,.88);backdrop-filter:blur(3px)}.banner-loader.active{display:flex}.banner-loader-box{min-width:280px;padding:30px 34px;border:1px solid rgba(255,255,255,.14);border-radius:16px;background:#0f172a;color:#fff;text-align:center;box-shadow:0 24px 70px rgba(0,0,0,.45)}.banner-loader-spinner{width:48px;height:48px;margin:0 auto 18px;border:4px solid rgba(255,255,255,.2);border-top-color:#38bdf8;border-radius:50%;animation:banner-spin .8s linear infinite}.banner-loader-title{font-size:17px;font-weight:800}.banner-loader-text{margin-top:7px;color:#cbd5e1;font-size:13px}@keyframes banner-spin{to{transform:rotate(360deg)}}
    @media(max-width:700px){.whd-header-actions,.whd-header-actions .whd-btn,.whd-filter-selects,.whd-select{width:100%}.whd-file{max-width:100%}}
</style>
@endpush

@section('content')
    <div id="bannerLoader" class="banner-loader active" role="status" aria-live="polite" aria-label="Generating banner">
        <div class="banner-loader-box">
            <div class="banner-loader-spinner"></div>
            <div class="banner-loader-title">Please wait</div>
            <div class="banner-loader-text">The banner is being generated...</div>
        </div>
    </div>

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
                <div class="search-box"><i class="fas fa-search"></i><input name="search" value="{{ request('search') }}" placeholder="Search by doctor name or MSL code..." autocomplete="off"></div>
                <div class="whd-filter-selects">
                    <div class="employee-select-wrap">
                        <select id="employeeFilter" class="whd-select" name="employee">
                            <option value="">All employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->employee_code }}" @selected((string) request('employee') === (string) $employee->employee_code)>{{ $employee->employee_name ?: 'Employee' }} ({{ $employee->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <select class="whd-select" name="gender"><option value="">All genders</option><option value="Male" @selected(request('gender') === 'Male')>Male</option><option value="Female" @selected(request('gender') === 'Female')>Female</option></select>
                    <select class="whd-select" name="banner"><option value="">All banners</option><option value="ready" @selected(request('banner') === 'ready')>Banner ready</option><option value="pending" @selected(request('banner') === 'pending')>Banner pending</option></select>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                @if(request()->hasAny(['search','employee','gender','banner']))<a class="btn btn-ghost" href="{{ route('admin.world-heart-day.index') }}"><i class="fas fa-times"></i> Reset</a>@endif
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
                                    <a class="mini-btn download-banner-btn" href="{{ route('admin.world-heart-day.banner.download', $entry) }}" title="Download generated banner"><i class="fas fa-download"></i> Download</a>
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
                    <tr><td colspan="9" style="text-align:center;padding:35px;color:var(--muted)">No records found. Clear the search filters or import an Excel file.</td></tr>
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (() => {
            $('#employeeFilter').select2({
                placeholder: 'Search employee name or code',
                allowClear: true,
                width: '100%'
            });
            const bannerLoader = document.getElementById('bannerLoader');
            const showBannerLoader = () => bannerLoader.classList.add('active');
            const hideBannerLoader = () => bannerLoader.classList.remove('active');
            window.addEventListener('load', () => window.setTimeout(hideBannerLoader, 250));
            window.addEventListener('pageshow', event => { if (event.persisted) hideBannerLoader(); });
            document.querySelectorAll('.gender-form').forEach(form => form.addEventListener('submit', showBannerLoader));
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
                    text: `The generated banner for ${form.dataset.doctor || 'this doctor'} will be permanently deleted from S3. The selected gender will also be cleared.`,
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
