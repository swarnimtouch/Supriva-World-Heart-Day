<?php

namespace App\Http\Controllers;

use App\Imports\WorldHeartDayImport;
use App\Exports\WorldHeartDayExport;
use App\Models\WorldHeartDayEntry;
use App\Services\DoctorBannerService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class WorldHeartDayController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $entries = $query->orderByRaw('source_row is null, source_row')->paginate(100)->withQueryString();
        $employees = WorldHeartDayEntry::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', '!=', '')
            ->select('employee_code', 'employee_name')
            ->distinct()
            ->orderBy('employee_name')
            ->orderBy('employee_code')
            ->get();

        return view('admin.world-heart-day.index', compact('entries', 'employees'));
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new WorldHeartDayImport();
        Excel::import($import, $validated['file']);
        $stats = $import->stats();

        return redirect()->route('admin.world-heart-day.index')->with(
            'success',
            "Import complete: {$stats['inserted']} inserted, {$stats['updated']} updated, {$stats['skipped']} skipped."
        );
    }

    public function export(Request $request)
    {
        $entries = $this->filteredQuery($request)
            ->orderByRaw('source_row is null, source_row')
            ->get();

        return Excel::download(
            new WorldHeartDayExport($entries),
            'world_heart_day_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function downloadBanners(Request $request)
    {
        set_time_limit(0);
        $entries = $this->filteredQuery($request)
            ->whereNotNull('banner_path')
            ->orderBy('id')
            ->get();

        if ($entries->isEmpty()) {
            return back()->with('warning', 'No banners are available for the selected records.');
        }

        $directory = storage_path('app/temp/world-heart-day/' . Str::uuid());
        File::ensureDirectoryExists($directory);
        $zipPath = $directory . DIRECTORY_SEPARATOR . 'world_heart_day_banners_' . now()->format('Ymd_His') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::deleteDirectory($directory);
            return back()->with('warning', 'The banner ZIP file could not be created.');
        }

        $added = 0;
        $stagedFiles = [];
        foreach ($entries as $entry) {
            try {
                $stream = Storage::disk('s3')->readStream($entry->banner_path);
            } catch (Throwable) {
                continue;
            }
            if (!is_resource($stream)) {
                continue;
            }

            $employeeFolder = $this->safeName(($entry->employee_code ?: 'unknown') . '_' . ($entry->employee_name ?: 'employee'));
            $doctorFile = $this->safeName($entry->doctor_name ?: 'doctor') . '_' . $entry->msl_code . '.png';
            $localPath = $directory . DIRECTORY_SEPARATOR . Str::uuid() . '.png';
            $output = fopen($localPath, 'wb');
            stream_copy_to_stream($stream, $output);
            fclose($stream);
            fclose($output);
            $zip->addFile($localPath, "{$employeeFolder}/{$doctorFile}");
            $stagedFiles[] = $localPath;
            $added++;
        }

        $zip->close();
        File::delete($stagedFiles);
        if ($added === 0) {
            File::deleteDirectory($directory);
            return back()->with('warning', 'No banners could be downloaded from S3.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function updateGender(
        Request $request,
        WorldHeartDayEntry $entry,
        DoctorBannerService $bannerService
    )
    {
        set_time_limit(max(60, (int) config('services.openai_images.timeout', 180) + 30));
        $validated = $request->validate(['gender' => ['required', 'in:Male,Female']]);
        $entry->update($validated);
        $path = $bannerService->generateCampaign($entry->fresh());

        return back()->with(
            $path ? 'success' : 'warning',
            $path
                ? "Gender saved and banner generated for {$entry->doctor_name}."
                : "Gender saved, but banner generation failed for {$entry->doctor_name}. Check S3 configuration/logs."
        );
    }

    public function deleteBanner(WorldHeartDayEntry $entry)
    {
        if (!$entry->banner_path) {
            return back()->with('warning', "No generated banner is available for {$entry->doctor_name}.");
        }

        try {
            $deleted = Storage::disk('s3')->delete($entry->banner_path);
        } catch (Throwable) {
            $deleted = false;
        }

        if (!$deleted) {
            return back()->with('warning', "The banner for {$entry->doctor_name} could not be deleted from S3. Please try again.");
        }

        $entry->forceFill([
            'banner_path' => null,
            'gender' => null,
        ])->save();

        return back()->with('success', "The generated banner for {$entry->doctor_name} was deleted successfully.");
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = WorldHeartDayEntry::query();
        $terms = preg_split('/\s+/', trim((string) $request->input('search', '')), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($terms as $term) {
            $query->where(function ($q) use ($term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $q->where('doctor_name', 'like', $like)
                    ->orWhere('msl_code', 'like', $like);
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('employee')) {
            $query->where('employee_code', $request->employee);
        }
        if ($request->input('banner') === 'ready') {
            $query->whereNotNull('banner_path');
        } elseif ($request->input('banner') === 'pending') {
            $query->whereNull('banner_path');
        }

        return $query;
    }

    private function safeName(string $value): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9_-]+/', '_', trim($value));
        return trim($clean ?: 'unknown', '_');
    }
}
