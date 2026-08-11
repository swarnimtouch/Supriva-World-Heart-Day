<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\WorldHeartDayEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DoctorBannerService
{
    public function generate(Doctor $doctor): ?string
    {
        return $this->generateBanner(
            $doctor->gender,
            $doctor->doctor_name,
            $doctor->speciality,
            $doctor->banner_path,
            "employee_" . ($doctor->employee?->employee_code ?? 'emp_' . $doctor->employee_id) . '_' .
                str($doctor->employee?->name ?: 'employee')->slug('_')->value(),
            $doctor->id,
            fn (string $path) => $doctor->forceFill(['banner_path' => $path])->saveQuietly(),
            ['doctor_id' => $doctor->id]
        );
    }

    public function generateCampaign(WorldHeartDayEntry $entry): ?string
    {
        $employeeCode = str($entry->employee_code ?: 'unknown')->slug('_')->value();
        $employeeName = str($entry->employee_name ?: 'employee')->slug('_')->value();

        return $this->generateBanner(
            $entry->gender,
            $entry->doctor_name,
            $entry->speciality,
            $entry->banner_path,
            "world_heart_day/employee_{$employeeCode}_{$employeeName}",
            $entry->id,
            fn (string $path) => $entry->forceFill(['banner_path' => $path])->saveQuietly(),
            ['world_heart_day_entry_id' => $entry->id]
        );
    }

    private function generateBanner(
        ?string $gender,
        string $doctorName,
        ?string $speciality,
        ?string $oldBanner,
        string $directory,
        int $recordId,
        callable $savePath,
        array $logContext
    ): ?string {
        $templateName = match (strtolower(trim((string) $gender))) {
            'male' => 'male-template.png',
            'female' => 'female-template.png',
            default => null,
        };

        if (!$templateName) {
            return null;
        }

        try {
            $templatePath = public_path('images/doctor-banners/' . $templateName);

            if (!extension_loaded('gd') || !function_exists('imagettftext')) {
                throw new RuntimeException('The PHP GD extension with FreeType support is required.');
            }

            if (!is_file($templatePath)) {
                throw new RuntimeException("Banner template not found: {$templateName}");
            }

            $template = imagecreatefrompng($templatePath);

            if (!$template) {
                throw new RuntimeException('The banner template could not be read.');
            }

            $this->placeText($template, $doctorName, $speciality);

            ob_start();
            imagepng($template, null, 8);
            $bannerContents = ob_get_clean();

            imagedestroy($template);

            $doctorSlug = str($doctorName)->slug('_')->value() ?: 'doctor';
            $path = "{$directory}/banners/{$doctorSlug}_{$recordId}_" . time() . '.png';

            Storage::disk('s3')->put($path, $bannerContents, [
                'visibility' => 'public',
                'ContentType' => 'image/png',
            ]);

            $savePath($path);

            if ($oldBanner && $oldBanner !== $path) {
                Storage::disk('s3')->delete($oldBanner);
            }

            return $path;
        } catch (Throwable $exception) {
            Log::error('Doctor banner generation failed.', [
                ...$logContext,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function placeText(\GdImage $banner, string $doctorName, ?string $doctorSpeciality): void
    {
        $nameFont = public_path('fonts/RobotoSlab-Bold.ttf');
        $specialityFont = public_path('fonts/RobotoSlab-Regular.ttf');

        if (!is_file($nameFont) || !is_file($specialityFont)) {
            throw new RuntimeException('Banner fonts are missing.');
        }
        $white = imagecolorallocate($banner, 255, 255, 255);
        $shadow = imagecolorallocatealpha($banner, 0, 0, 0, 55);
        $name = $this->fitText($doctorName, $nameFont, 70, 1800);
        $speciality = $this->fitText($doctorSpeciality ?: 'Doctor', $specialityFont, 55, 1800);

        $nameX = $this->centeredX($name, $nameFont, 70, 0, 1860);
        $specialityX = $this->centeredX($speciality, $specialityFont, 55, 0, 1860);

        imagettftext($banner, 70, 0, $nameX + 3, 2433, $shadow, $nameFont, $name);
        imagettftext($banner, 70, 0, $nameX, 2430, $white, $nameFont, $name);
        imagettftext($banner, 55, 0, $specialityX + 3, 2533, $shadow, $specialityFont, $speciality);
        imagettftext($banner, 55, 0, $specialityX, 2530, $white, $specialityFont, $speciality);
    }

    private function centeredX(string $text, string $font, int $fontSize, int $startX, int $endX): int
    {
        $box = imagettfbbox($fontSize, 0, $font, $text);
        $width = $box[2] - $box[0];

        return (int) round($startX + (($endX - $startX - $width) / 2) - $box[0]);
    }

    private function fitText(string $text, string $font, int $fontSize, int $maxWidth): string
    {
        $text = trim($text);
        while (mb_strlen($text) > 1) {
            $box = imagettfbbox($fontSize, 0, $font, $text);
            if (($box[2] - $box[0]) <= $maxWidth) {
                return $text;
            }
            $text = rtrim(mb_substr($text, 0, -1));
        }

        return $text;
    }
}
