<?php

namespace App\Exports;

use App\Models\WorldHeartDayEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorldHeartDayExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private readonly Collection $entries)
    {
    }

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'Sr. No.', 'Employee Name', 'Employee Code', 'Doctor Name', 'MSL Code',
            'Speciality', 'Gender', 'Photo URL', 'Banner URL',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->source_row,
            $entry->employee_name,
            $entry->employee_code,
            $entry->doctor_name,
            $entry->msl_code,
            $entry->speciality,
            $entry->gender,
            $entry->photo_url,
            $entry->banner_path ? $this->s3Url($entry->banner_path) : null,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['ARGB' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['ARGB' => 'FFB91C1C']],
        ]);

        return [];
    }

    private function s3Url(string $path): string
    {
        return 'https://swarnimpolling.s3.ap-south-1.amazonaws.com/' . ltrim($path, '/');
    }
}
