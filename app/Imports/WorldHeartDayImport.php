<?php

namespace App\Imports;

use App\Models\Doctor;
use App\Models\WorldHeartDayEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorldHeartDayImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private int $insertedCount = 0;
    private int $updatedCount = 0;
    private int $skippedCount = 0;
    private int $matchedCount = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $mslCode = trim((string) ($row['msl_code'] ?? ''));
            $doctorName = trim((string) ($row['doctor_name'] ?? ''));

            if ($mslCode === '' || $doctorName === '') {
                $this->skippedCount++;
                continue;
            }

            $doctor = Doctor::where('msl_code', $mslCode)->first();
            if ($doctor) {
                $this->matchedCount++;
            }

            $photoUrl = $this->trustedPhotoUrl(trim((string) ($row['photo_url'] ?? '')));
            $values = [
                'source_row' => $this->nullableString($row['sr_no'] ?? null),
                'employee_name' => $this->nullableString($row['employee_name'] ?? null),
                'employee_code' => $this->nullableString($row['employee_code'] ?? null),
                'doctor_name' => $doctorName,
                'speciality' => $this->nullableString($row['speciality'] ?? null),
                'photo_url' => $photoUrl,
                'photo_path' => $this->s3Path($photoUrl),
                'doctor_id' => $doctor?->id,
            ];

            $entry = WorldHeartDayEntry::where('msl_code', $mslCode)->first();
            if ($entry) {
                $entry->update($values);
                $this->updatedCount++;
            } else {
                WorldHeartDayEntry::create($values + ['msl_code' => $mslCode]);
                $this->insertedCount++;
            }
        }
    }

    private function trustedPhotoUrl(string $url): ?string
    {
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        return $host === 'swarnimpolling.s3.ap-south-1.amazonaws.com' ? $url : null;
    }

    private function s3Path(?string $url): ?string
    {
        return $url ? ltrim((string) parse_url($url, PHP_URL_PATH), '/') : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function stats(): array
    {
        return [
            'inserted' => $this->insertedCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'matched' => $this->matchedCount,
        ];
    }
}
