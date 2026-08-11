<?php

namespace App\Http\Controllers;

use App\Imports\WorldHeartDayImport;
use App\Models\WorldHeartDayEntry;
use App\Services\DoctorBannerService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WorldHeartDayController extends Controller
{
    public function index(Request $request)
    {
        $query = WorldHeartDayEntry::with('doctor.employee');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                    ->orWhere('msl_code', 'like', "%{$search}%")
                    ->orWhere('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('speciality', 'like', "%{$search}%");
            });
        }

        $entries = $query->orderByRaw('source_row is null, source_row')->paginate(100)->withQueryString();
        $summary = [
            'total' => WorldHeartDayEntry::count(),
            'matched' => WorldHeartDayEntry::whereNotNull('doctor_id')->count(),
            'photos' => WorldHeartDayEntry::whereNotNull('photo_url')->count(),
            'banners' => WorldHeartDayEntry::whereNotNull('banner_path')->count(),
        ];

        return view('admin.world-heart-day.index', compact('entries', 'summary'));
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
            "Import complete: {$stats['inserted']} inserted, {$stats['updated']} updated, " .
            "{$stats['matched']} doctor matches, {$stats['skipped']} skipped."
        );
    }

    public function regenerateBanners(DoctorBannerService $bannerService)
    {
        $generated = 0;
        $skipped = 0;

        WorldHeartDayEntry::whereNotNull('gender')
            ->chunkById(50, function ($entries) use ($bannerService, &$generated, &$skipped) {
                foreach ($entries as $entry) {
                    if (!$bannerService->generateCampaign($entry)) {
                        $skipped++;
                        continue;
                    }
                    $generated++;
                }
            });

        return back()->with(
            $skipped ? 'warning' : 'success',
            "Banner update complete: {$generated} generated, {$skipped} skipped (unmatched/missing gender/upload failure)."
        );
    }

    public function updateGender(Request $request, WorldHeartDayEntry $entry)
    {
        $validated = $request->validate(['gender' => ['required', 'in:Male,Female']]);
        $entry->update($validated);

        return back()->with('success', "Gender saved for {$entry->doctor_name}.");
    }

    public function generateBanner(WorldHeartDayEntry $entry, DoctorBannerService $bannerService)
    {
        if (!$entry->gender) {
            return back()->with('warning', "Select gender for {$entry->doctor_name} before generating the banner.");
        }

        $path = $bannerService->generateCampaign($entry);

        return back()->with(
            $path ? 'success' : 'warning',
            $path ? "Banner generated for {$entry->doctor_name}." : "Banner generation failed for {$entry->doctor_name}. Check S3 configuration/logs."
        );
    }
}
