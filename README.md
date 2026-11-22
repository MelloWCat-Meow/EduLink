# EduLink Tutor

EduLink Tutor adalah layanan pemesanan guru les privat SD, SMP, dan SMA di Kota Jambi, dari PT EduLink Teknologi Indonesia. Orang tua mencari tutor terverifikasi, memesan slot mingguan, membayar lewat platform, lalu memantau sesi dan memberi ulasan. Tutor mendaftar (atau mengaktifkan akun kalau sudah mengajar di bimbel EduLink), diverifikasi tim akademik, lalu menerima pesanan, mencatat sesi, dan mencairkan pendapatan. Admin memverifikasi tutor, memantau pesanan, menangani keluhan, pengembalian dana, pencairan, dan laporan.

Dibangun dengan Laravel 13, Blade, dan Tailwind CSS 4, untuk tugas mata kuliah Arsitektur Sistem Perusahaan.

## Kebutuhan

- PHP 8.4 atau lebih baru, dengan ekstensi `bcmath`, `intl`, `pdo_mysql`, dan `fileinfo`
- Composer 2
- Node.js 20 atau lebih baru, dengan npm
- MySQL 8 atau MariaDB 10.6 ke atas

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buka `.env`, lalu isi koneksi database (`DB_CONNECTION=mysql`, `DB_DATABASE=edulink`, `DB_USERNAME`, `DB_PASSWORD`). Buat database kosong bernama `edulink`, kemudian:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build
```

`migrate:fresh --seed` menghapus semua tabel lalu mengisinya dengan data demo. Jalankan ulang kapan saja untuk kembali ke kondisi awal demo. Tanggal dalam data demo selalu relatif terhadap hari ini.

## Menjalankan

Buka dua terminal:

```bash
php artisan serve
```

```bash
php artisan schedule:work
```

`schedule:work` menjalankan tugas terjadwal:

| Perintah | Jadwal | Fungsi |
|---|---|---|
| `edulink:lepas-penahanan` | setiap menit | Membatalkan pesanan yang tidak dijawab tutor atau tidak dibayar dalam 24 jam, dan melepas slotnya. |
| `edulink:jadwal-ulang-kedaluwarsa` | setiap menit | Menandai pengajuan jadwal ulang yang tidak dijawab sampai waktu sesi semula. |
| `edulink:konfirmasi-otomatis` | setiap jam | Mengonfirmasi otomatis sesi yang tidak dikonfirmasi atau disanggah orang tua dalam 48 jam setelah dicatat tutor. |
| `edulink:pengingat-sesi` | setiap hari 07.00 | Mengirim pengingat H-1 ke orang tua dan tutor, sekali per sesi. Tutor diingatkan kalau tautan meeting sesi besok belum diisi. |

Pelepasan slot kedaluwarsa juga terjadi otomatis setiap kali halaman membaca pesanan atau jadwal, jadi demo tetap benar walaupun `schedule:work` tidak berjalan.

Email (tautan aktivasi, reset kata sandi, notifikasi penting) memakai mailer `log` dan tercatat di `storage/logs/laravel.log`.

## Mode demo

Pembayaran disimulasikan: tidak ada payment gateway sungguhan yang tersambung. Sesi online memakai tautan Google Meet, Zoom, atau layanan lain milik tutor sendiri, yang dibagikan tutor dari detail pesanan; EduLink tidak menjalankan layanan video. Mode demo diatur lewat `EDULINK_MODE_DEMO` di `.env`:

- Kosong: aktif kalau `APP_ENV` bernilai `local` atau `testing`, mati di environment lain.
- `true`: aktif di mana saja, termasuk hosting dengan `APP_ENV=production` untuk presentasi.
- `false`: mati.

Selama mode demo aktif:

- Tombol **Bayar** membuka halaman gateway tiruan dengan pilihan Berhasil atau Gagal. Hasilnya dikirim ke `POST /webhooks/payment` dengan tanda tangan HMAC.
- Tautan aktivasi tutor internal dan tautan reset kata sandi juga ditampilkan di layar dengan label "Mode demo", karena tidak ada kotak masuk sungguhan.
- Perintah `edulink:simulasi-tutor` dan `edulink:simulasi-sesi` tersedia untuk demo cepat.

## Akun demo

| Peran | Email | Kata sandi | Keadaan |
|---|---|---|---|
| Admin | admin@edulink.test | admin12345 | Admin Dewi Lestari |
| Orang tua | siti.aminah@edulink.test | orangtua123 | Anak: Nadia (SD) dan Raka (SMP); punya pesanan di semua status |
| Orang tua | budi.santoso@edulink.test | orangtua123 | Anak: Dimas (SMA) |
| Tutor terverifikasi | rina.marlina@edulink.test | tutor12345 | Permintaan baru, sesi lewat yang belum dicatat (pesanan online tanpa tautan meeting), sesi yang disanggah dan menunggu admin, saldo tersedia, dana ditahan, dan pencairan menunggu |
| Tutor terverifikasi | andi.pratama@edulink.test | tutor12345 | Pesanan di semua status, sesi kemarin yang menunggu konfirmasi Budi, tautan Meet sudah diisi, saldo tersedia tanpa pengajuan menunggu |
| Tutor terverifikasi | dewi.anggraini@edulink.test | tutor12345 | Sesi yang dikonfirmasi otomatis, sesi besok dengan tautan Zoom khusus (untuk pengingat H-1) |
| Tutor menunggu verifikasi | nurul.aisyah@edulink.test, bayu.firmansyah@edulink.test | tutor12345 | Halaman "Sedang diperiksa" |
| Tutor perlu perbaikan | intan.permata@edulink.test | tutor12345 | KTP ditolak dan profil diberi catatan |
| Tutor draf | galih.prakoso@edulink.test | tutor12345 | Pendaftaran belum dikirim |
| Tutor internal belum aktivasi | lestari.putri@edulink.test, rahmat.saputra@edulink.test | dibuat saat aktivasi | Aktivasi di `/aktivasi-tutor` |

## Skenario demo

Semua langkah lewat tampilan, tanpa perintah simulasi. Pakai dua jendela browser (atau satu jendela biasa dan satu jendela samaran) supaya bisa masuk sebagai dua peran sekaligus.

1. **Orang tua memesan.** Masuk sebagai Siti, buka **Cari tutor**, pilih Andi Pratama, lalu **Pesan**. Pilih anak Raka, mapel Bahasa Inggris SMP, slot Kamis 19.00 (sengaja dibiarkan kosong di data demo), tanggal mulai, durasi, dan jumlah pertemuan. Periksa total, lalu konfirmasi.
2. **Tutor menerima.** Masuk sebagai Andi. Lonceng notifikasi menunjukkan permintaan baru, dan Beranda menampilkan hitung mundur batas menjawab. Buka pesanan, lalu **Terima pesanan** (atau **Tolak** dengan alasan).
3. **Orang tua membayar.** Kembali sebagai Siti, buka pesanan itu, pilih cara bayar, lalu **Bayar**. Di halaman gateway tiruan pilih **Berhasil** (pembayaran disimulasikan). Status menjadi Dibayar, jadwal sesi dibuat, dan tombol **Chat tutor via WhatsApp** muncul.
4. **Tutor membagikan tautan meeting.** Sebagai Andi, buka pesanan itu, isi **Tautan meeting pesanan** dengan tautan Google Meet atau Zoom miliknya. Tautan bisa diganti khusus untuk satu sesi. Sebelum diisi, Siti melihat "Tutor belum membagikan tautan meeting".
5. **Jadwal ulang (opsional).** Di detail pesanan, salah satu pihak mengajukan jadwal ulang untuk sesi yang masih lebih dari 24 jam lagi; pihak lain menyetujui atau menolak.
6. **Tutor mencatat sesi.** Masuk sebagai Rina, buka **Sesi**. Ada sesi IPA SD Nadia yang sudah lewat dan belum dicatat. Pilih **Terlaksana** (atau **Siswa tidak hadir**), tulis catatan materi, lalu simpan. Sesi menunggu konfirmasi orang tua, dan bagian Rina untuk sesi itu tampil sebagai **Dana ditahan** di **Pendapatan**.
7. **Orang tua mengonfirmasi atau menyanggah.** Sebagai Siti, buka Beranda: kartu "Sesi menunggu konfirmasi Anda" menampilkan sisa waktunya. Klik **Konfirmasi sesi** (bisa juga langsung dari halaman notifikasi), atau **Sanggah** dengan alasan. Setelah dikonfirmasi, bagian Rina pindah dari dana ditahan ke **saldo tersedia**. Pesanan baru selesai saat semua sesinya dikonfirmasi.
8. **Admin memutuskan sanggahan.** Masuk sebagai admin, buka **Keluhan**. Ada sanggahan Siti atas sesi Rina sebelumnya (jenis "Sanggahan sesi"). Pilih **Sesi sah** atau **Sesi tidak sah** dengan alasan. Kalau tidak sah, biaya sesi itu masuk **Pengembalian dana** untuk Siti dan Rina tidak dibayar untuk sesi itu.
9. **Tutor mengajukan pencairan.** Masuk sebagai Andi, buka **Pendapatan**, ajukan pencairan sebesar saldo tersedia atau kurang. Dana ditahan tidak bisa dicairkan.
10. **Admin menyetujui.** Sebagai admin, buka **Pencairan dana**, setujui pengajuan Andi (atau pengajuan Rina yang sudah menunggu), lalu tandai dibayar setelah transfer.
11. **Ulasan.** Sebagai Siti, buka pesanan yang selesai (mis. Matematika SD dengan Rina), beri nilai dan komentar.
12. **Admin, lainnya.**
   - **Ringkasan:** komisi diakui per sesi yang dikonfirmasi pada periode itu, komisi ditahan, grafik, **Unduh CSV**, dan **Cetak**.
   - **Verifikasi tutor:** setujui Nurul, atau minta perbaikan.
   - **Pesanan:** batalkan pesanan yang sudah dibayar dengan alasan; biaya sesi yang belum dicatat otomatis masuk pengembalian dana.
   - **Kelola tutor** dan **Data pengguna:** ubah mata pelajaran, nonaktifkan atau aktifkan akun.

### Mempercepat konfirmasi otomatis untuk demo

Konfirmasi otomatis normalnya terjadi 48 jam setelah tutor mencatat sesi. Untuk demo, jalankan:

```bash
php artisan edulink:konfirmasi-otomatis --setelah=0
```

Semua sesi yang menunggu konfirmasi langsung dikonfirmasi otomatis, dan kedua pihak menerima notifikasi. Opsi `--setelah` (jumlah jam) hanya boleh diubah dari 48 saat mode demo aktif. Tanpa opsi, perintah ini sama dengan yang dijalankan terjadwal setiap jam.

Alur pendaftaran tutor:

- **Eksternal:** daftar di `/daftar-tutor`, isi lima langkah, kirim, lalu admin menyetujui di **Verifikasi tutor**. Tutor itu kemudian tampil di `/tutor`.
- **Internal:** buka `/aktivasi-tutor` dengan `lestari.putri@edulink.test`, buka tautan "Mode demo", buat kata sandi, lengkapi tarif, kecamatan, dan jadwal, lalu kirim. Admin kemudian menyetujuinya.

## Test

```bash
php artisan test
```

Test memakai SQLite di memori, jadi tidak menyentuh database MySQL. Untuk menjalankan sebagian saja:

```bash
php artisan test --filter=JadwalUlang
php artisan test tests/Feature/Pesanan
```

## Dokumentasi lain

- [`docs/daftar-tabel.md`](docs/daftar-tabel.md): semua tabel dan kolom, ditandai mana yang dari ERD dan mana yang tambahan, beserta alasannya.
- Kebijakan privasi dan syarat ketentuan ada di aplikasi: `/kebijakan-privasi` dan `/syarat-ketentuan`. Keduanya rancangan untuk tugas dan belum ditinjau ahli hukum.
