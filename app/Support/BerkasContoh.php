<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-page placeholder PDFs for seeded verification documents, stored on the
 * private disk like real uploads, so the admin can open them in a demo.
 */
class BerkasContoh
{
    /**
     * Folder on the private disk that holds verification documents.
     */
    public const FOLDER = 'dokumen-tutor';

    /**
     * Writes the PDF under a random name and returns its path on the disk.
     */
    public static function simpan(string $idTutor, string $judul): string
    {
        $path = self::FOLDER.'/'.$idTutor.'/'.Str::random(40).'.pdf';

        Storage::disk('local')->put($path, self::pdf($judul));

        return $path;
    }

    /**
     * A valid single-page PDF with two lines of text.
     */
    public static function pdf(string $judul): string
    {
        $teks = fn (string $baris): string => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::ascii($baris));

        $isi = "BT /F1 20 Tf 72 760 Td ({$teks($judul)}) Tj 0 -32 Td /F1 12 Tf (Berkas contoh dari seeder EduLink, bukan dokumen asli.) Tj ET";

        $objek = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($isi)." >>\nstream\n{$isi}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $posisi = [];

        foreach ($objek as $indeks => $badan) {
            $posisi[] = strlen($pdf);
            $pdf .= ($indeks + 1)." 0 obj\n{$badan}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objek) + 1)."\n0000000000 65535 f \n";

        foreach ($posisi as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf.'trailer'."\n<< /Size ".(count($objek) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
