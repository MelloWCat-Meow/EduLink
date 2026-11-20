<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_key_sections_for_parents_and_tutors(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSeeText('guru les privat');
        $response->assertSeeText('Lembar cari guru');
        $response->assertSeeText('Tampilkan tutor');
        $response->assertSeeText('Dari mencari guru sampai sesi pertama');
        $response->assertSeeText('Online atau tutor datang ke rumah?');
        $response->assertSeeText('Daftar jadi tutor');
        $response->assertSeeText('Tanya jawab');
        $response->assertSeeText('Kebijakan privasi');
    }

    public function test_search_form_submits_subject_level_and_location_to_tutor_search(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSee('action="'.route('tutor.index').'"', false);
        $response->assertSee('name="mapel"', false);
        $response->assertSee('name="jenjang"', false);
        $response->assertSee('name="lokasi"', false);
    }

    public function test_subject_links_open_tutor_search_filtered_by_subject_and_level(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSee('/tutor?mapel=Calistung&jenjang=sd');
        $response->assertSee('/tutor?mapel=Persiapan%20UTBK&jenjang=sma');
        $response->assertSee('/tutor?jenjang=smp');
    }

    public function test_tutor_verification_lists_the_checked_documents(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSeeTextInOrder(['Lembar pemeriksaan berkas tutor', 'KTP', 'Ijazah terakhir', 'Sertifikat']);
        $response->assertSeeText('Contoh berkas');
    }

    public function test_sample_tutor_cards_are_labelled_as_examples(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSeeText('Contoh tampilan. Bukan tutor asli.');
        $response->assertSeeText('Fadli Ramadhan');
        $response->assertSeeText('Rp110.000/jam');
    }

    public function test_faq_answers_the_questions_parents_ask_before_booking(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSeeText('Bagaimana kalau tutor menolak pesanan saya?');
        $response->assertSeeText('Data anak saya dipakai untuk apa?');
    }

    public function test_landing_page_does_not_expose_internal_system_terms(): void
    {
        $response = $this->get(route('landing'));

        foreach (['EduManage', 'API', 'SSO', 'TOGAF', 'komisi'] as $internalTerm) {
            $response->assertDontSeeText($internalTerm);
        }
    }
}
