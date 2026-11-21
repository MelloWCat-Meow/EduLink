<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HalamanGalatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_pages_show_the_custom_404_for_guests(): void
    {
        $this->get('/tidak-ada-di-sini')
            ->assertNotFound()
            ->assertSeeText('Halaman ini tidak ditemukan.')
            ->assertSee(route('landing'), false);
    }

    public function test_404_inside_the_app_sends_signed_in_users_to_their_home(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();

        $this->actingAs($ortu)->get('/pesanan/ORD-2026-999999')->assertNotFound()->assertSeeText('Ke beranda saya')->assertSee(route('beranda'), false);
    }

    public function test_an_expired_form_shows_the_custom_419(): void
    {
        Route::middleware('web')->post('/_uji/419', fn () => abort(419));

        $this->post('/_uji/419')->assertStatus(419)->assertSeeText('Halaman ini sudah terlalu lama terbuka.')->assertSeeText('Kembali dan muat ulang');
    }

    public function test_too_many_attempts_shows_the_custom_429(): void
    {
        Route::middleware('web')->get('/_uji/429', fn () => abort(429));

        $this->get('/_uji/429')->assertStatus(429)->assertSeeText('Terlalu banyak percobaan.');
    }
}
