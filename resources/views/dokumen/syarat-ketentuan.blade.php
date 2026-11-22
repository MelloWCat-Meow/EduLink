@use('App\Services\AlurPesanan')
@use('App\Services\KontakWhatsApp')
@use('App\Services\KonfirmasiSesi')

@php
    $jam = AlurPesanan::MASA_TAHAN_JAM;
    $batas = KonfirmasiSesi::BATAS_JAM;
    $daftarIsi = [
        'layanan' => 'Tentang layanan',
        'akun' => 'Akun',
        'pesanan' => 'Alur pesanan dan batas waktu',
        'konfirmasi' => 'Sesi, konfirmasi, dan sanggahan',
        'pembayaran' => 'Pembayaran',
        'pembatalan' => 'Pembatalan',
        'jadwal-ulang' => 'Penjadwalan ulang sesi',
        'pengembalian' => 'Pengembalian dana',
        'tutor' => 'Ketentuan untuk tutor: verifikasi, komisi, dan pencairan',
        'luar-platform' => 'Larangan bertransaksi di luar platform',
        'ulasan' => 'Ulasan dan keluhan',
        'perubahan' => 'Perubahan syarat',
    ];
@endphp

<x-publik.dokumen title="Syarat dan Ketentuan" :catatan="'versi '.config('edulink.versi_kebijakan').' · untuk orang tua dan tutor EduLink Tutor'" :daftar-isi="$daftarIsi">
    <section id="layanan">
        <h2>1. Tentang layanan</h2>
        <p>EduLink Tutor mempertemukan orang tua dengan guru les privat SD, SMP, dan SMA di Kota Jambi, secara online atau tutor datang ke rumah. EduLink memeriksa tutor, mencatat pesanan, menerima pembayaran, dan meneruskan bagian tutor setelah dikurangi komisi.</p>
    </section>

    <section id="akun">
        <h2>2. Akun</h2>
        <ul>
            <li>Satu akun untuk satu orang. Orang tua mendaftarkan anak-anaknya di akun orang tua.</li>
            <li>Jaga kerahasiaan kata sandi. Kalau lupa, atur ulang lewat "Lupa kata sandi?" di halaman masuk.</li>
            <li>EduLink dapat menonaktifkan akun yang melanggar syarat ini, dengan alasan yang dicatat.</li>
        </ul>
    </section>

    <section id="pesanan">
        <h2>3. Alur pesanan dan batas waktu</h2>
        <ul>
            <li>Orang tua memilih tutor, mata pelajaran, slot mingguan, tanggal mulai, durasi, dan jumlah pertemuan. Total biaya tampil sebelum pesanan dikirim.</li>
            <li>Slot ditahan untuk pesanan itu. <strong>Tutor menjawab (menerima atau menolak) dalam {{ $jam }} jam.</strong> Kalau tidak dijawab, pesanan batal otomatis dan slot dilepas.</li>
            <li>Setelah tutor menerima, <strong>orang tua membayar dalam {{ $jam }} jam.</strong> Kalau tidak dibayar, pesanan batal otomatis dan slot dilepas.</li>
            <li>Setelah dibayar, jadwal sesi mingguan dibuat. Untuk sesi online, tutor membagikan tautan Google Meet, Zoom, atau layanan lain miliknya; EduLink tidak menyediakan layanan video. Untuk kunjungan rumah, alamat dikoordinasikan lewat WhatsApp.</li>
            <li>Pesanan selesai saat <strong>semua sesinya sudah dikonfirmasi</strong> (lihat bagian 4), bukan saat tutor selesai mencatat.</li>
        </ul>
    </section>

    <section id="konfirmasi">
        <h2>4. Sesi, konfirmasi, dan sanggahan</h2>
        <ul>
            <li>Setelah sesi berlangsung, tutor mencatatnya sebagai <strong>Terlaksana</strong> atau <strong>Siswa tidak hadir</strong>, disertai catatan materi.</li>
            <li><strong>Siswa tidak hadir</strong> berarti tutor sudah datang ke rumah atau membuka ruang meeting pada waktunya, tetapi siswa tidak hadir tanpa kabar. Sesi itu tetap dihitung terlaksana. Kalau siswa berhalangan, ajukan jadwal ulang sebelumnya (bagian 7).</li>
            <li>Tutor yang berhalangan tidak boleh mencatat sesi. Tutor mengajukan jadwal ulang.</li>
            <li>Setelah sesi dicatat, orang tua menerima notifikasi dan punya <strong>{{ $batas }} jam</strong> untuk <strong>mengonfirmasi</strong> atau <strong>menyanggah</strong> sesi itu. Kalau tidak dijawab dalam {{ $batas }} jam, sesi <strong>dikonfirmasi otomatis</strong>.</li>
            <li>Sanggahan wajib disertai alasan, misalnya tutor tidak datang atau sesi tidak sesuai catatan. Sanggahan otomatis menjadi keluhan yang diperiksa admin.</li>
            <li>Admin memutuskan sanggahan dengan alasan yang dikirim ke kedua pihak:
                <strong>sesi sah</strong> berarti sesi dihitung terlaksana dan tutor dibayar;
                <strong>sesi tidak sah</strong> berarti biaya sesi itu dikembalikan kepada orang tua dan tutor tidak dibayar untuk sesi tersebut.</li>
        </ul>
    </section>

    <section id="pembayaran">
        <h2>5. Pembayaran</h2>
        <ul>
            <li>Semua pembayaran dilakukan lewat EduLink (virtual account, QRIS, atau dompet digital), tidak langsung ke tutor.</li>
            <li>Tarif yang berlaku adalah tarif saat pesanan dibuat. Perubahan tarif tutor tidak mengubah pesanan yang sudah ada.</li>
        </ul>
    </section>

    <section id="pembatalan">
        <h2>6. Pembatalan</h2>
        <ul>
            <li><strong>Orang tua</strong> dapat membatalkan pesanan sebelum membayar, tanpa biaya.</li>
            <li><strong>Tutor</strong> dapat menolak permintaan, atau membatalkan pesanan yang belum dibayar, dengan alasan yang dikirim ke orang tua.</li>
            <li>Pesanan yang sudah dibayar <strong>hanya bisa dibatalkan oleh admin</strong>, lewat keluhan dari orang tua atau tutor. Pengembaliannya mengikuti bagian 8.</li>
        </ul>
    </section>

    <section id="jadwal-ulang">
        <h2>7. Penjadwalan ulang sesi</h2>
        <ul>
            <li>Orang tua atau tutor dapat mengajukan jadwal ulang untuk sesi yang belum terlaksana, <strong>paling lambat 24 jam sebelum</strong> waktu sesi, dengan alasan.</li>
            <li>Waktu baru paling cepat 24 jam dari saat pengajuan, paling lambat 14 hari setelah waktu lama, dan tidak bentrok dengan sesi lain tutor.</li>
            <li>Pihak lain menyetujui atau menolak. Kalau belum dijawab sampai waktu sesi lama tiba, pengajuan kedaluwarsa dan sesi tetap pada waktu lama.</li>
            <li>Setiap sesi dapat dijadwalkan ulang paling banyak dua kali, dan hanya boleh ada satu pengajuan yang menunggu untuk setiap sesi.</li>
        </ul>
    </section>

    <section id="pengembalian">
        <h2>8. Pengembalian dana</h2>
        <ul>
            <li>Biaya setiap sesi adalah <strong>total biaya ÷ jumlah pertemuan</strong>, dibulatkan ke rupiah; selisih pembulatan masuk ke sesi terakhir sehingga jumlahnya tepat sama dengan total.</li>
            <li>Kalau pesanan yang sudah dibayar dibatalkan admin, orang tua mendapat kembali biaya <strong>sesi yang belum dicatat</strong>. Sesi yang sudah dicatat tetap melalui konfirmasi seperti biasa.</li>
            <li>Biaya <strong>sesi yang dinyatakan tidak sah</strong> setelah sanggahan juga dikembalikan kepada orang tua. Satu pengembalian bisa mencakup beberapa sesi.</li>
            <li>Sesi yang sudah dikonfirmasi tetap menjadi hak tutor.</li>
            <li>Admin mentransfer pengembalian secara manual dan mencatatnya. Status pengembalian tampil di detail pesanan dan halaman pembayaran orang tua.</li>
        </ul>
    </section>

    <section id="tutor">
        <h2>9. Ketentuan untuk tutor: verifikasi, komisi, dan pencairan</h2>
        <ul>
            <li>Tutor tampil di pencarian setelah tim akademik memeriksa identitas, dokumen, kompetensi, dan kesesuaian mata pelajaran. Pengajar bimbel EduLink mengaktifkan akun lewat aktivasi, tanpa mendaftar ulang.</li>
            <li>Mata pelajaran dan jenjang hanya bisa diubah oleh admin, karena kesesuaiannya sudah diperiksa.</li>
            <li>EduLink mengambil komisi platform dari biaya setiap sesi. Persentasenya ditetapkan admin, dan yang berlaku adalah persentase saat pesanan dibayar.</li>
            <li><strong>Dana ditahan sampai sesi dikonfirmasi.</strong> Bagian tutor dari setiap sesi (biaya sesi dikurangi komisinya) baru masuk saldo tersedia setelah sesi dikonfirmasi orang tua, dikonfirmasi otomatis setelah {{ $batas }} jam, atau dinyatakan sah oleh admin. Sebelum itu, dana tampil sebagai dana ditahan.</li>
            <li>Tutor dapat mengajukan pencairan paling banyak sebesar saldo tersedia, ke rekening yang didaftarkan, dengan satu pengajuan menunggu dalam satu waktu.</li>
        </ul>
    </section>

    <section id="luar-platform">
        <h2>10. Larangan bertransaksi di luar platform</h2>
        <ul>
            <li>Orang tua dan tutor dilarang memesan atau membayar les di luar EduLink untuk hubungan yang berawal dari EduLink.</li>
            <li>Nomor HP hanya diberikan untuk koordinasi pesanan yang sudah dibayar (sampai {{ KontakWhatsApp::HARI_SETELAH_SELESAI }} hari setelah selesai), misalnya alamat kunjungan rumah.</li>
            <li>Ulasan atau pesan yang memuat ajakan bertransaksi di luar EduLink dapat disembunyikan admin, dan pelanggaran berulang dapat membuat akun dinonaktifkan.</li>
        </ul>
    </section>

    <section id="ulasan">
        <h2>11. Ulasan dan keluhan</h2>
        <ul>
            <li>Orang tua dapat memberi satu ulasan untuk setiap pesanan yang selesai. Ulasan harus jujur dan sopan.</li>
            <li>Orang tua dan tutor dapat mengajukan keluhan dari detail pesanan. Admin menanggapi lewat aplikasi.</li>
        </ul>
    </section>

    <section id="perubahan">
        <h2>12. Perubahan syarat</h2>
        <p>Kalau syarat ini berubah, nomor versinya ikut berubah dan kami memberi tahu lewat notifikasi di aplikasi sebelum perubahan berlaku.</p>
    </section>
</x-publik.dokumen>
