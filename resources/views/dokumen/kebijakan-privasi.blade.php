@php
    $daftarIsi = [
        'tentang' => 'Tentang kebijakan ini',
        'data' => 'Data yang kami kumpulkan',
        'tujuan' => 'Untuk apa data dipakai',
        'akses' => 'Siapa yang bisa melihat data',
        'keamanan' => 'Penyimpanan dan pengamanan',
        'lama' => 'Berapa lama data disimpan',
        'hak' => 'Hak Anda atas data pribadi',
        'kontak' => 'Cara menghubungi kami',
        'perubahan' => 'Perubahan kebijakan',
    ];
@endphp

<x-publik.dokumen title="Kebijakan Privasi" :catatan="'versi '.config('edulink.versi_kebijakan').' · berlaku untuk orang tua, tutor, dan admin EduLink Tutor'" :daftar-isi="$daftarIsi">
    <section id="tentang">
        <h2>1. Tentang kebijakan ini</h2>
        <p>EduLink Tutor adalah layanan pemesanan guru les privat dari PT EduLink Teknologi Indonesia di Kota Jambi. Kebijakan ini menjelaskan data apa yang kami simpan saat Anda memakai EduLink Tutor, untuk apa, siapa yang bisa melihatnya, dan apa hak Anda.</p>
        <p>Dengan mencentang persetujuan saat mendaftar, Anda menyetujui kebijakan versi {{ config('edulink.versi_kebijakan') }}. Kami mencatat kapan Anda menyetujuinya.</p>
    </section>

    <section id="data">
        <h2>2. Data yang kami kumpulkan</h2>
        <h3>Data akun (semua pengguna)</h3>
        <ul>
            <li>Nama, email (dipakai untuk masuk), dan nomor HP.</li>
            <li>Kata sandi, yang kami simpan dalam bentuk acak (hash), bukan teks aslinya.</li>
        </ul>
        <h3>Data anak (diisi orang tua)</h3>
        <ul>
            <li>Nama anak dan jenjang sekolah (SD, SMP, atau SMA).</li>
        </ul>
        <h3>Data tutor</h3>
        <ul>
            <li>Identitas: nama, email, nomor HP, dan foto profil (opsional).</li>
            <li>Pendidikan terakhir, kompetensi, pengalaman mengajar, mata pelajaran, tarif, kecamatan, dan jadwal.</li>
            <li>Dokumen verifikasi: KTP, ijazah, dan sertifikat.</li>
            <li>Rekening pencairan: nama bank, nomor rekening, dan nama pemilik rekening.</li>
        </ul>
        <h3>Data pesanan dan pembayaran</h3>
        <ul>
            <li>Pesanan les (anak, tutor, mata pelajaran, jadwal, cara belajar, biaya) dan riwayat statusnya.</li>
            <li>Catatan kehadiran dan catatan hasil sesi dari tutor, ulasan, dan keluhan.</li>
            <li>Pembayaran (cara bayar, jumlah, status), komisi, pencairan dana tutor, dan pengembalian dana.</li>
        </ul>
    </section>

    <section id="tujuan">
        <h2>3. Untuk apa data dipakai</h2>
        <ul>
            <li><strong>Data akun</strong>: supaya Anda bisa masuk, menerima pemberitahuan pesanan, dan mengatur ulang kata sandi.</li>
            <li><strong>Data anak</strong>: supaya tutor tahu siapa yang diajar dan menyiapkan materi sesuai jenjangnya.</li>
            <li><strong>Data tutor</strong>: untuk memeriksa identitas dan kompetensi sebelum tutor tampil di pencarian, menampilkan profil publik, dan mentransfer pencairan dana.</li>
            <li><strong>Data pesanan dan pembayaran</strong>: untuk menjalankan pesanan, menghitung bagian tutor dan komisi, menangani keluhan dan pengembalian dana, serta menyusun laporan keuangan perusahaan.</li>
        </ul>
        <p>Kami tidak menjual data Anda dan tidak memakainya untuk iklan pihak lain.</p>
    </section>

    <section id="akses">
        <h2>4. Siapa yang bisa melihat data</h2>
        <ul>
            <li><strong>Pengunjung dan orang tua</strong> hanya melihat profil publik tutor: nama, foto, pendidikan, kompetensi, pengalaman, mata pelajaran, tarif, kecamatan, jadwal kosong, dan ulasan. Nomor HP dan dokumen tutor tidak pernah tampil di profil publik.</li>
            <li><strong>Nomor HP</strong> orang tua dan tutor hanya terlihat oleh kedua pihak dalam pesanan yang sudah dibayar (sampai 14 hari setelah pesanan selesai), lewat tombol WhatsApp di detail pesanan.</li>
            <li><strong>Data anak</strong> terlihat oleh tutor yang menerima pesanan untuk anak tersebut.</li>
            <li><strong>Dokumen verifikasi tutor</strong> (KTP, ijazah, sertifikat) hanya bisa dibuka oleh tutor itu sendiri dan admin EduLink.</li>
            <li><strong>Nomor rekening</strong> tampil tersamar (misalnya ••••4417) bagi tutor, dan lengkap hanya di halaman pencairan admin untuk keperluan transfer.</li>
            <li><strong>Admin EduLink</strong> dapat melihat data yang diperlukan untuk verifikasi, menangani keluhan, pembatalan, pengembalian dana, dan laporan.</li>
        </ul>
    </section>

    <section id="keamanan">
        <h2>5. Penyimpanan dan pengamanan</h2>
        <ul>
            <li>Dokumen verifikasi disimpan di penyimpanan privat dengan nama berkas acak, dan hanya bisa dibuka setelah sistem memeriksa hak akses.</li>
            <li>Kata sandi disimpan dalam bentuk hash. Tidak ada petugas EduLink yang bisa melihat kata sandi Anda.</li>
            <li>Nomor rekening disimpan terenkripsi dan ditampilkan tersamar.</li>
            <li>Percobaan masuk, pendaftaran, dan permintaan tautan dibatasi untuk mencegah penebakan.</li>
            <li>Saat kata sandi diubah, semua perangkat lain yang masih masuk dengan akun itu dikeluarkan.</li>
        </ul>
    </section>

    <section id="lama">
        <h2>6. Berapa lama data disimpan</h2>
        <p>Data akun dan data anak disimpan selama akun aktif. Data pesanan, pembayaran, komisi, pencairan, dan pengembalian dana tetap kami simpan setelah akun dihapus selama diwajibkan untuk pencatatan keuangan, dengan data pribadi yang tidak lagi diperlukan dihapus atau disamarkan.</p>
    </section>

    <section id="hak">
        <h2>7. Hak Anda atas data pribadi</h2>
        <p>Sesuai Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi, Anda berhak:</p>
        <ul>
            <li><strong>Mengakses</strong> data pribadi Anda yang kami simpan dan mendapatkan salinannya.</li>
            <li><strong>Memperbaiki</strong> data yang keliru atau tidak lengkap. Nama, nomor HP, data anak, dan profil tutor bisa Anda ubah sendiri dari dashboard.</li>
            <li><strong>Meminta penghapusan</strong> data pribadi, kecuali data yang wajib kami simpan untuk pencatatan keuangan atau penyelesaian sengketa.</li>
            <li><strong>Menarik persetujuan</strong>, dengan konsekuensi akun tidak bisa dipakai lagi untuk memesan atau mengajar.</li>
        </ul>
        <p>Kami menjawab permintaan paling lambat 3 × 24 jam sejak permintaan lengkap diterima, sesuai ketentuan undang-undang tersebut.</p>
    </section>

    <section id="kontak">
        <h2>8. Cara menghubungi kami</h2>
        <p>Kirim permintaan atau pertanyaan tentang data pribadi ke <a href="mailto:{{ config('edulink.email_privasi') }}" class="tautan">{{ config('edulink.email_privasi') }}</a> dari email yang terdaftar di akun Anda, atau ajukan keluhan dari halaman detail pesanan. Sebutkan nama dan ID akun Anda (misalnya AK0002) supaya kami bisa memastikan bahwa Anda pemilik akun.</p>
    </section>

    <section id="perubahan">
        <h2>9. Perubahan kebijakan</h2>
        <p>Kalau kebijakan ini berubah, nomor versinya ikut berubah dan kami memberi tahu lewat notifikasi di aplikasi sebelum perubahan berlaku.</p>
    </section>
</x-publik.dokumen>
