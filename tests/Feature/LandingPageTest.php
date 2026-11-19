<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_key_sections_for_parents_and_tutors(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSeeText('EduLink Tutor');
        $response->assertSeeText('Tampilkan tutor');
        $response->assertSeeText('Dari mencari sampai belajar');
        $response->assertSeeText('Tutor datang ke rumah');
        $response->assertSeeText('Daftar jadi tutor');
        $response->assertSeeText('Tanya jawab');
    }

    public function test_search_form_submits_subject_level_and_location_to_tutor_search(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSee('action="'.route('tutor.index').'"', false);
        $response->assertSee('name="mapel"', false);
        $response->assertSee('name="jenjang"', false);
        $response->assertSee('name="lokasi"', false);
    }

    public function test_sample_tutor_cards_are_labelled_as_examples(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSeeText('Contoh tampilan');
        $response->assertSeeText(config('landing.sample_tutors.0.name'));
    }

    public function test_landing_page_does_not_expose_internal_system_terms(): void
    {
        $response = $this->get(route('landing'));

        foreach (['EduManage', 'API Gateway', 'SSO', 'TOGAF', 'komisi'] as $internalTerm) {
            $response->assertDontSeeText($internalTerm);
        }
    }

    public function test_tutor_search_placeholder_echoes_the_submitted_filters(): void
    {
        $response = $this->get(route('tutor.index', ['mapel' => 'Fisika', 'jenjang' => 'sma', 'lokasi' => 'Telanaipura']));

        $response->assertOk();
        $response->assertSeeText('Fisika');
        $response->assertSeeText('SMA');
        $response->assertSeeText('Telanaipura');
    }
}
