<?php

namespace App\Services;

use App\Enums\StatusPembayaran;
use App\Models\Pembayaran;
use App\Models\PencairanDanaTutor;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * One CSV file per period with a section per kind of record, made to open
 * cleanly in Excel set to Indonesian: UTF-8 with BOM, semicolons between
 * fields, CRLF line ends, whole-rupiah amounts without thousands separators,
 * and dates as YYYY-MM-DD HH:MM. Each section uses the same date the summary
 * page uses for it; commission is listed per confirmed session.
 */
class EksporLaporan
{
    private const PEMISAH = ';';

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function rentang(string $periode): array
    {
        $mulai = Carbon::createFromFormat('!Y-m', $periode)->startOfMonth();

        return [$mulai, $mulai->copy()->endOfMonth()];
    }

    public function csv(string $periode): string
    {
        $rentang = self::rentang($periode);
        $baris = [['Laporan EduLink Tutor', 'Periode '.$periode, 'Dibuat '.now()->format('Y-m-d H:i')], []];

        $pesanan = Pesanan::query()->whereBetween('created_at', $rentang)->with(['siswa.wali', 'tutor', 'mapel'])->orderBy('created_at')->get();
        $baris[] = ['PESANAN (menurut tanggal dibuat)'];
        $baris[] = ['ID pesanan', 'Tanggal', 'Orang tua', 'Anak', 'Tutor', 'Mapel', 'Status', 'Total (Rp)'];
        foreach ($pesanan as $p) {
            $baris[] = [$p->id_pesanan, $this->waktu($p->created_at), $p->siswa->wali->nama, $p->siswa->nama, $p->tutor->nama, $p->mapel->label(), $p->status->label(), $this->rupiah($p->total_biaya)];
        }
        $baris[] = ['Jumlah', (string) $pesanan->count(), '', '', '', '', '', $this->rupiah($pesanan->sum('total_biaya'))];
        $baris[] = [];

        $pembayaran = Pembayaran::query()->where('status', StatusPembayaran::Berhasil)->whereBetween('diproses_pada', $rentang)->orderBy('diproses_pada')->get();
        $baris[] = ['PEMBAYARAN BERHASIL (menurut tanggal diproses)'];
        $baris[] = ['ID pembayaran', 'Tanggal', 'ID pesanan', 'Metode', 'Jumlah (Rp)'];
        foreach ($pembayaran as $b) {
            $baris[] = [$b->id_pembayaran, $this->waktu($b->diproses_pada), $b->id_pesanan, $b->metode->label(), $this->rupiah($b->jumlah)];
        }
        $baris[] = ['Jumlah', (string) $pembayaran->count(), '', '', $this->rupiah($pembayaran->sum('jumlah'))];
        $baris[] = [];

        // Commission is recognised per session, in the month the session was confirmed.
        $diakui = [];
        $ditahan = '0.00';
        foreach (Pesanan::query()->whereHas('komisi')->with(['komisi', 'sesi'])->get() as $p) {
            foreach (SaldoTutor::porsiSesi($p) as $porsi) {
                $pada = $porsi['sesi']->dikonfirmasi_pada;
                if ($porsi['keadaan'] === 'tersedia' && $pada !== null && $pada->between($rentang[0], $rentang[1])) {
                    $diakui[] = [$p, $porsi];
                } elseif ($porsi['keadaan'] === 'ditahan') {
                    $ditahan = bcadd($ditahan, $porsi['komisi'], 2);
                }
            }
        }
        usort($diakui, fn (array $a, array $b): int => $a[1]['sesi']->dikonfirmasi_pada <=> $b[1]['sesi']->dikonfirmasi_pada);

        $baris[] = ['KOMISI PLATFORM DIAKUI (per sesi, menurut tanggal sesi dikonfirmasi)'];
        $baris[] = ['ID sesi', 'Tanggal dikonfirmasi', 'ID pesanan', 'Dikonfirmasi oleh', 'Persentase', 'Nilai sesi (Rp)', 'Komisi (Rp)'];
        $totalKomisi = '0.00';
        foreach ($diakui as [$p, $porsi]) {
            $baris[] = [$porsi['sesi']->id_sesi, $this->waktu($porsi['sesi']->dikonfirmasi_pada), $p->id_pesanan, (string) $porsi['sesi']->dikonfirmasi_oleh, str_replace('.', ',', rtrim(rtrim((string) $p->komisi->persentase, '0'), '.')), $this->rupiah($porsi['nilai']), $this->rupiah($porsi['komisi'])];
            $totalKomisi = bcadd($totalKomisi, $porsi['komisi'], 2);
        }
        $baris[] = ['Jumlah', (string) count($diakui), '', '', '', '', $this->rupiah($totalKomisi)];
        $baris[] = ['Komisi ditahan (sesi belum dikonfirmasi, saat laporan dibuat)', '', '', '', '', '', $this->rupiah($ditahan)];
        $baris[] = [];

        $pencairan = PencairanDanaTutor::query()->whereBetween('created_at', $rentang)->with('tutor')->orderBy('created_at')->get();
        $baris[] = ['PENCAIRAN DANA TUTOR (menurut tanggal pengajuan)'];
        $baris[] = ['ID pencairan', 'Tanggal', 'Tutor', 'Periode pendapatan', 'Status', 'Nominal (Rp)'];
        foreach ($pencairan as $c) {
            $baris[] = [$c->id_pencairan, $this->waktu($c->created_at), $c->tutor->nama, $c->periode, $c->status->label(), $this->rupiah($c->nominal)];
        }
        $baris[] = ['Jumlah', (string) $pencairan->count(), '', '', '', $this->rupiah($pencairan->sum('nominal'))];
        $baris[] = [];

        $pengembalian = PengembalianDana::query()->whereBetween('created_at', $rentang)->with('pesanan.siswa.wali')->withCount('sesi')->orderBy('created_at')->get();
        $baris[] = ['PENGEMBALIAN DANA (menurut tanggal dicatat)'];
        $baris[] = ['ID pengembalian', 'Tanggal', 'ID pesanan', 'Orang tua', 'Sesi dikembalikan', 'Status', 'Nominal (Rp)'];
        foreach ($pengembalian as $r) {
            $baris[] = [$r->id_pengembalian, $this->waktu($r->created_at), $r->id_pesanan, $r->pesanan->siswa->wali->nama, (string) $r->sesi_count, $r->status->label(), $this->rupiah($r->nominal)];
        }
        $baris[] = ['Jumlah', (string) $pengembalian->count(), '', '', '', '', $this->rupiah($pengembalian->sum('nominal'))];

        return "\u{FEFF}".implode("\r\n", array_map(fn (array $kolom): string => implode(self::PEMISAH, array_map($this->sel(...), $kolom)), $baris))."\r\n";
    }

    /**
     * Quotes a field when needed, and defuses text Excel would run as a formula.
     */
    private function sel(string $nilai): string
    {
        if ($nilai !== '' && in_array($nilai[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($nilai)) {
            $nilai = "'".$nilai;
        }

        if (str_contains($nilai, self::PEMISAH) || str_contains($nilai, '"') || str_contains($nilai, "\n") || str_contains($nilai, "\r")) {
            return '"'.str_replace('"', '""', $nilai).'"';
        }

        return $nilai;
    }

    private function rupiah(int|float|string|null $nilai): string
    {
        return bcadd(bcadd((string) ($nilai ?? 0), '0.5', 0), '0', 0);
    }

    private function waktu(?DateTimeInterface $waktu): string
    {
        return $waktu?->format('Y-m-d H:i') ?? '';
    }
}
