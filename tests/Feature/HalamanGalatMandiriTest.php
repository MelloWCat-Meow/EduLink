<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * No RefreshDatabase on purpose: these pages must render while the database is down.
 */
class HalamanGalatMandiriTest extends TestCase
{
    public function test_500_and_503_render_without_a_working_database(): void
    {
        config([
            'database.connections.rusak' => ['driver' => 'sqlite', 'database' => '/jalur/yang/tidak/ada.sqlite', 'prefix' => ''],
            'database.default' => 'rusak',
        ]);

        foreach ([500 => 'Maaf, terjadi kesalahan di sistem kami.', 503 => 'EduLink sedang dalam perbaikan.'] as $kode => $judul) {
            $html = view('errors.'.$kode)->render();

            $this->assertStringContainsString($judul, $html);
            $this->assertStringContainsString('<html lang="id">', $html);
            $this->assertStringContainsString('Ke halaman utama', $html);
        }
    }
}
