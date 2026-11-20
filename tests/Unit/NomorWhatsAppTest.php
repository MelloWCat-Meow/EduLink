<?php

namespace Tests\Unit;

use App\Support\NomorWhatsApp;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class NomorWhatsAppTest extends TestCase
{
    #[TestWith(['081234567890', '6281234567890'])]
    #[TestWith(['0812-3456 7890', '6281234567890'])]
    #[TestWith(['+62 812 3456 7890', '6281234567890'])]
    #[TestWith(['6281234567890', '6281234567890'])]
    #[TestWith(['81234567890', '6281234567890'])]
    #[TestWith(['(0812) 3456.7890', '6281234567890'])]
    public function test_indonesian_numbers_become_international_digits(string $masukan, string $hasil): void
    {
        $this->assertSame($hasil, NomorWhatsApp::normalisasi($masukan));
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['abc'])]
    #[TestWith(['0211234567'])]
    #[TestWith(['+1 415 555 0100'])]
    #[TestWith(['0812'])]
    public function test_unusable_numbers_give_no_number(?string $masukan): void
    {
        $this->assertNull(NomorWhatsApp::normalisasi($masukan));
        $this->assertNull(NomorWhatsApp::tautan($masukan, 'Halo'));
    }

    public function test_link_carries_the_encoded_message(): void
    {
        $this->assertSame(
            'https://wa.me/6281234567890?text=Halo%20Kak%20Rina%2C%20pesanan%20ORD-2026-000001%20%28IPA%20SD%29.',
            NomorWhatsApp::tautan('0812 3456 7890', 'Halo Kak Rina, pesanan ORD-2026-000001 (IPA SD).'),
        );
    }
}
