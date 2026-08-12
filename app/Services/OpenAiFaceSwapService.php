<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiFaceSwapService
{
    private const PROMPT = <<<'PROMPT'
The first image is the locked campaign banner template. The second image is the doctor identity reference.
Replace only the face inside the transparent mask with the same identifiable face from the doctor reference.
Preserve the doctor's facial identity, apparent age, facial structure, eyes, nose, mouth, skin tone, hairline, and natural expression.
The doctor reference photo may show the face turned toward the left or right. Correct that angle and reconstruct the same person's face in a straight, front-facing view looking naturally toward the camera.
Follow the face direction and head pose already present in the campaign banner, not the direction or camera angle of the doctor reference photo.
Keep both eyes naturally aligned and visible, preserve realistic facial symmetry and proportions, and do not produce a tilted, side-facing, stretched, or distorted face.
Match the template head angle, lighting, focus, scale, perspective, and photorealistic style so the result is seamless.
Do not modify anything outside the masked face area. Keep the exact canvas, background, headline, body, pose, hair outside the mask, clothing, chair, hands, blood-pressure cuff, table, medical device, colors, and composition unchanged.
Do not add any new text, logos, objects, borders, or watermarks.
PROMPT;

    public function enabled(): bool
    {
        return (bool) config('services.openai_images.enabled');
    }

    public function swap(string $templatePath, string $doctorPhotoUrl, string $gender): string
    {
        if (!$this->enabled()) {
            return file_get_contents($templatePath) ?: throw new RuntimeException('Banner template could not be read.');
        }

        $apiKey = trim((string) config('services.openai_images.api_key'));
        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $photoResponse = Http::timeout(30)->retry(2, 500)->get($doctorPhotoUrl);
        if (!$photoResponse->successful() || $photoResponse->body() === '') {
            throw new RuntimeException('Doctor photo could not be downloaded for face swap.');
        }

        $mask = $this->faceMask($gender);
        $response = $this->client($apiKey)
            ->attach('image[]', fopen($templatePath, 'rb'), basename($templatePath))
            ->attach('image[]', $photoResponse->body(), 'doctor-reference.png')
            ->attach('mask', $mask, 'face-mask.png')
            ->post(config('services.openai_images.base_url') . '/images/edits', [
                'model' => config('services.openai_images.model'),
                'prompt' => self::PROMPT,
                'quality' => config('services.openai_images.quality'),
                'size' => '1856x2704',
                'output_format' => 'png',
                'moderation' => 'auto',
            ]);

        if (!$response->successful()) {
            $requestId = $response->header('x-request-id');
            throw new RuntimeException('OpenAI face swap failed (HTTP ' . $response->status() . ', request ' . ($requestId ?: 'unknown') . ').');
        }

        $encoded = $response->json('data.0.b64_json');
        $image = is_string($encoded) ? base64_decode($encoded, true) : false;
        if (!$image) {
            throw new RuntimeException('OpenAI face swap returned no valid image.');
        }

        return $image;
    }

    private function client(string $apiKey): PendingRequest
    {
        return Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('services.openai_images.timeout', 180))
            ->retry(3, 1500, throw: false);
    }

    private function faceMask(string $gender): string
    {
        $mask = imagecreatetruecolor(1860, 2700);
        imagealphablending($mask, false);
        imagesavealpha($mask, true);
        $opaque = imagecolorallocatealpha($mask, 255, 255, 255, 0);
        $transparent = imagecolorallocatealpha($mask, 255, 255, 255, 127);
        imagefilledrectangle($mask, 0, 0, 1859, 2699, $opaque);

        if (strtolower(trim($gender)) === 'female') {
            imagefilledellipse($mask, 650, 790, 360, 440, $transparent);
        } else {
            imagefilledellipse($mask, 660, 785, 350, 430, $transparent);
        }

        ob_start();
        imagepng($mask);
        $contents = ob_get_clean();
        imagedestroy($mask);

        return $contents ?: throw new RuntimeException('Face mask could not be created.');
    }
}
