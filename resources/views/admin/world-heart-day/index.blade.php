@extends('layouts.admin')
@section('title', 'World Heart Day')
@section('page-title', 'World Heart Day Campaign')

@push('styles')
<style>
    .whd-actions,.whd-search{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px}
    .whd-actions form,.whd-search{margin:0}.whd-file,.whd-input{padding:9px 12px;border:1px solid var(--border);border-radius:9px;background:var(--surface);color:var(--text)}
    .whd-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;border:0;border-radius:9px;background:#dc2626;color:#fff;font-weight:700;cursor:pointer;text-decoration:none}
    .whd-btn.secondary{background:#2563eb}.whd-btn.dark{background:#334155}
    .whd-stats{display:grid;grid-template-columns:repeat(4,minmax(130px,1fr));gap:12px;margin-bottom:18px}
    .whd-stat{padding:16px;border:1px solid var(--border);border-radius:12px;background:var(--surface)}
    .whd-stat strong{display:block;font-size:24px;color:var(--text)}.whd-stat span{font-size:12px;color:var(--muted)}
    .whd-table-wrap{overflow:auto}.whd-table{width:100%;border-collapse:collapse;min-width:1200px}
    .whd-table th,.whd-table td{padding:11px 10px;border-bottom:1px solid var(--border);text-align:left;vertical-align:middle;font-size:13px}
    .whd-table th{color:var(--muted);white-space:nowrap}.whd-thumb{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border)}
    .preview-trigger{border:0;background:transparent;padding:0;cursor:pointer}.banner-btn{padding:7px 11px;border:0;border-radius:8px;background:#7c3aed;color:#fff;font-weight:700;cursor:pointer}
    .status{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:700}.matched{background:#dcfce7;color:#166534}.unmatched{background:#fee2e2;color:#991b1b}
    .gender-form{display:flex;gap:5px}.gender-form select{padding:6px;border:1px solid var(--border);border-radius:7px;background:var(--surface);color:var(--text)}.mini-btn{padding:6px 8px;border:0;border-radius:7px;background:#0f766e;color:#fff;cursor:pointer}
    .whd-alert{padding:12px 16px;border-radius:9px;margin-bottom:14px}.success{background:#dcfce7;color:#166534}.warning{background:#fef3c7;color:#92400e}.error{background:#fee2e2;color:#991b1b}
    .preview-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(2,6,23,.9)}.preview-modal.open{display:flex}
    .preview-modal img{max-width:94vw;max-height:88vh;border-radius:10px}.preview-close{position:fixed;top:20px;right:25px;background:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:25px;cursor:pointer}
    @media(max-width:700px){.whd-stats{grid-template-columns:repeat(2,1fr)}.whd-actions form{width:100%}.whd-file{max-width:100%}}
</style>
@endpush

@section('content')
    @if(session('success'))<div class="whd-alert success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="whd-alert warning">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="whd-alert error">{{ $errors->first() }}</div>@endif

    <div class="whd-stats">
        <div class="whd-stat"><strong>{{ number_format($summary['total']) }}</strong><span>Total imported</span></div>
        <div class="whd-stat"><strong>{{ number_format($summary['matched']) }}</strong><span>Matched doctors</span></div>
        <div class="whd-stat"><strong>{{ number_format($summary['photos']) }}</strong><span>S3 photos</span></div>
        <div class="whd-stat"><strong>{{ number_format($summary['banners']) }}</strong><span>Ready banners</span></div>
    </div>

    <div class="card">
        <div class="whd-actions">
            <form method="POST" action="{{ route('admin.world-heart-day.import') }}" enctype="multipart/form-data">
                @csrf
                <input class="whd-file" type="file" name="file" accept=".xlsx,.xls,.csv" required>
                <button class="whd-btn" type="submit"><i class="fas fa-file-import"></i> Import Excel</button>
            </form>
            <form method="POST" action="{{ route('admin.world-heart-day.regenerate-banners') }}" onsubmit="return confirm('Update banners for every matched doctor? This can take some time.');">
                @csrf
                <button class="whd-btn secondary" type="submit"><i class="fas fa-images"></i> Generate / Update Banners</button>
            </form>
        </div>

        <form class="whd-search" method="GET">
            <input class="whd-input" name="search" value="{{ request('search') }}" placeholder="Doctor, MSL, employee, speciality...">
            <button class="whd-btn dark" type="submit"><i class="fas fa-search"></i> Search</button>
            @if(request('search'))<a class="whd-btn dark" href="{{ route('admin.world-heart-day.index') }}">Clear</a>@endif
        </form>

        <div class="whd-table-wrap">
            <table class="whd-table">
                <thead><tr><th>Sr.</th><th>Photo</th><th>Doctor</th><th>MSL Code</th><th>Speciality</th><th>Employee</th><th>Employee Code</th><th>Gender</th><th>Match</th><th>Banner</th></tr></thead>
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
                        <td><span class="status {{ $entry->doctor_id ? 'matched' : 'unmatched' }}">{{ $entry->doctor_id ? 'Matched' : 'Unmatched' }}</span></td>
                        <td>
                            @if($entry->banner_path)
                                <button class="preview-trigger banner-btn" type="button" data-image="https://swarnimpolling.s3.ap-south-1.amazonaws.com/{{ $entry->banner_path }}" data-alt="{{ $entry->doctor_name }} banner"><i class="fas fa-eye"></i> Preview</button>
                            @endif
                            <form method="POST" action="{{ route('admin.world-heart-day.banner', $entry) }}" style="display:inline-block;margin-top:5px">@csrf<button class="mini-btn" type="submit"><i class="fas fa-wand-magic-sparkles"></i> {{ $entry->banner_path ? 'Update' : 'Generate' }}</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align:center;padding:35px;color:var(--muted)">Excel import karne ke baad data yahan dikhega.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:18px">{{ $entries->links() }}</div>
    </div>

    <div id="previewModal" class="preview-modal" aria-hidden="true"><button class="preview-close" type="button">&times;</button><img src="" alt=""></div>
    <script>
        (() => {
            const modal = document.getElementById('previewModal'); const image = modal.querySelector('img');
            const close = () => { modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); image.src=''; };
            document.querySelectorAll('.preview-trigger').forEach(button => button.addEventListener('click', () => { image.src=button.dataset.image; image.alt=button.dataset.alt || 'Preview'; modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); }));
            modal.querySelector('.preview-close').addEventListener('click', close); modal.addEventListener('click', event => { if(event.target === modal) close(); });
            document.addEventListener('keydown', event => { if(event.key === 'Escape') close(); });
        })();
    </script>
@endsection
