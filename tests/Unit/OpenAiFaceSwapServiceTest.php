<?php

namespace Tests\Unit;

use App\Services\OpenAiFaceSwapService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiFaceSwapServiceTest extends TestCase
{
    public function test_it_sends_template_reference_mask_and_prompt_to_openai(): void
    {
        config([
            'services.openai_images.enabled' => true,
            'services.openai_images.api_key' => 'test-key',
            'services.openai_images.base_url' => 'https://api.openai.test/v1',
            'services.openai_images.model' => 'gpt-image-2',
            'services.openai_images.quality' => 'low',
            'services.openai_images.timeout' => 180,
        ]);

        $templatePath = public_path('images/doctor-banners/male-template.png');
        $template = file_get_contents($templatePath);

        Http::fake([
            'https://photos.test/doctor.png' => Http::response($template, 200, ['Content-Type' => 'image/png']),
            'https://api.openai.test/v1/images/edits' => Http::response([
                'data' => [['b64_json' => base64_encode($template)]],
            ]),
        ]);

        $result = app(OpenAiFaceSwapService::class)->swap(
            $templatePath,
            'https://photos.test/doctor.png',
            'Male'
        );

        $this->assertSame($template, $result);
        Http::assertSent(fn ($request) =>
            $request->url() === 'https://api.openai.test/v1/images/edits'
            && str_contains($request->body(), 'locked campaign banner template')
            && str_contains($request->body(), 'doctor-reference.png')
            && str_contains($request->body(), 'face-mask.png')
        );
    }
}
