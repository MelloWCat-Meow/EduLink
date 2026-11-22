<?php

namespace App\Enums;

/**
 * Every in-app notification. pakaiEmail() marks the important ones that also
 * go out by email (the log mailer locally).
 */
enum JenisNotifikasi: string
{
    case PesananBaru = 'pesanan_baru';
    case PesananDiterima = 'pesanan_diterima';
    case PesananDitolak = 'pesanan_ditolak';
    case PesananDibatalkan = 'pesanan_dibatalkan';
    case PembayaranBerhasil = 'pembayaran_berhasil';
    case PengingatSesi = 'pengingat_sesi';
    case SesiDicatat = 'sesi_dicatat';
    case PesananKedaluwarsa = 'pesanan_kedaluwarsa';
    case UlasanBaru = 'ulasan_baru';
    case KeluhanDiperbarui = 'keluhan_diperbarui';
    case PencairanDisetujui = 'pencairan_disetujui';
    case PencairanDibayar = 'pencairan_dibayar';
    case KeputusanVerifikasi = 'keputusan_verifikasi';
    case PendaftaranTutorBaru = 'pendaftaran_tutor_baru';
    case PengajuanPencairanBaru = 'pengajuan_pencairan_baru';
    case KataSandiDiubah = 'kata_sandi_diubah';
    case JadwalUlangDiajukan = 'jadwal_ulang_diajukan';
    case JadwalUlangDijawab = 'jadwal_ulang_dijawab';
    case PengembalianDana = 'pengembalian_dana';
    case MapelDiubah = 'mapel_diubah';
    case SesiDikonfirmasi = 'sesi_dikonfirmasi';
    case SesiDisanggah = 'sesi_disanggah';
    case SanggahanDiputuskan = 'sanggahan_diputuskan';

    public function pakaiEmail(): bool
    {
        return in_array($this, [
            self::PesananBaru,
            self::PesananDiterima,
            self::PesananDitolak,
            self::PesananDibatalkan,
            self::PembayaranBerhasil,
            self::PengingatSesi,
            self::PesananKedaluwarsa,
            self::PencairanDibayar,
            self::KeputusanVerifikasi,
            self::KataSandiDiubah,
            self::JadwalUlangDiajukan,
            self::PengembalianDana,
            self::SesiDicatat,
            self::SesiDisanggah,
            self::SanggahanDiputuskan,
        ], true);
    }
}
