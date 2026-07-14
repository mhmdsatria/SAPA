<?php

namespace Tests\Unit;

use App\Services\CensorService;
use Tests\TestCase;

class CensorServiceTest extends TestCase
{
    public function test_it_censors_configured_words_without_changing_safe_words(): void
    {
        config()->set('censor.words', ['kasar', 'bodoh']);
        $service = app(CensorService::class);

        $result = $service->censor('Kalimat kasar dan bodoh, tetapi informasi lokasi tetap aman.');

        $this->assertSame('Kalimat *** dan ***, tetapi informasi lokasi tetap aman.', $result);
    }

    public function test_it_accepts_nullable_content(): void
    {
        $service = app(CensorService::class);

        $this->assertSame('', $service->censor(null));
    }
}
