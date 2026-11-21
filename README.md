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
| `edulink:pengingat-sesi` | setiap hari 07.00 | Mengirim pengingat H-1 ke orang tua dan tutor, sekali per sesi. |

Pelepasan slot kedaluwarsa juga terjadi otomatis setiap kali halaman membaca pesanan atau jadwal, jadi demo tetap benar walaupun `schedule:work` tidak berjalan.

Email (tautan aktivasi, reset kata sandi, notifikasi penting) memakai mailer `log` dan tercatat di `storage/logs/laravel.log`.

## Mode demo

Payment gateway dan layanan video meeting belum tersambung, jadi keduanya disimulasikan. Mode demo diatur lewat `EDULINK_MODE_DEMO` di `.env`:

- Kosong: aktif kalau `APP_ENV` bernilai `local` atau `testing`, mati di environment lain.
- `true`: aktif di mana saja, termasuk hosting dengan `APP_ENV=production` untuk presentasi.
- `false`: mati.

Selama mode demo aktif:

- Tombol **Bayar** membuka halaman gateway tiruan dengan pilihan Berhasil atau Gagal. Hasilnya dikirim ke `POST /webhooks/payment` dengan tanda tangan HMAC.
- Tautan aktivasi tutor internal dan tautan reset kata sandi juga ditampilkan di layar dengan label "Mode demo", karena tidak ada kotak masuk sungguhan.
- Perintah `edulink:simulasi-tutor` dan `edulink:simulasi-sesi` tersedia untuk demo cepat.
- Pita "Mode demo: pembayaran disimulasikan" tampil di atas setiap halaman.

## Akun demo

| Peran | Email | Kata sandi | Keadaan |
|---|---|---|---|
| Admin | admin@edulink.test | admin12345 | Admin Dewi Lestari |
| Orang tua | siti.aminah@edulink.test | orangtua123 | Anak: Nadia (SD) dan Raka (SMP); punya pesanan di semua status |
| Orang tua | budi.santoso@edulink.test | orangtua123 | Anak: Dimas (SMA) |
| Tutor terverifikasi | rina.marlina@edulink.test | tutor12345 | Ada permintaan baru, sesi lewat yang belum dicatat, saldo, dan pencairan menunggu |
| Tutor terverifikasi | andi.pratama@edulink.test | tutor12345 | Pesanan di semua status, termasuk menunggu pembayaran |
| Tutor terverifikasi | dewi.anggraini@edulink.test | tutor12345 | Ada sesi besok (untuk pengingat H-1) |
| Tutor menunggu verifikasi | nurul.aisyah@edulink.test, bayu.firmansyah@edulink.test | tutor12345 | Halaman "Sedang diperiksa" |
| Tutor perlu perbaikan | intan.permata@edulink.test | tutor12345 | KTP ditolak dan profil diberi catatan |
| Tutor draf | galih.prakoso@edulink.test | tutor12345 | Pendaftaran belum dikirim |
| Tutor internal belum aktivasi | lestari.putri@edulink.test, rahmat.saputra@edulink.test | dibuat saat aktivasi | Aktivasi di `/aktivasi-tutor` |

## Skenario demo

Semua langkah lewat tampilan, tanpa perintah simulasi. Pakai dua jendela browser (atau satu jendela biasa dan satu jendela samaran) supaya bisa masuk sebagai dua peran sekaligus.

1. **Orang tua memesan.** Masuk sebagai Siti, buka **Cari tutor**, pilih Andi Pratama, lalu **Pesan**. Pilih anak Raka, mapel Bahasa Inggris SMP, slot Kamis 19.00 (sengaja dibiarkan kosong di data demo), tanggal mulai, durasi, dan jumlah pertemuan. Periksa total, lalu konfirmasi.
2. **Tutor menerima.** Masuk sebagai Andi. Lonceng notifikasi menunjukkan permintaan baru, dan Beranda menampilkan hitung mundur batas menjawab. Buka pesanan, lalu **Terima pesanan** (atau **Tolak** dengan alasan).
3. **Orang tua membayar.** Kembali sebagai Siti, buka pesanan itu, pilih cara bayar, lalu **Bayar**. Di halaman gateway tiruan pilih **Berhasil**. Status menjadi Dibayar, jadwal sesi dibuat, dan tombol **Chat tutor via WhatsApp** muncul.
4. **Jadwal ulang (opsional).** Di detail pesanan, salah satu pihak mengajukan jadwal ulang untuk sesi yang masih lebih dari 24 jam lagi; pihak lain menyetujui atau menolak.
5. **Tutor mencatat sesi.** Masuk sebagai Rina, buka **Sesi**. Ada sesi yang sudah lewat dan belum dicatat. Pilih Hadir, tulis catatan, lalu simpan. Pesanan otomatis selesai setelah semua sesinya tercatat.
6. **Ulasan.** Sebagai Siti, buka pesanan yang selesai (mis. Matematika SD dengan Rina), beri nilai dan komentar. Rina menerima notifikasi ulasan baru.
7. **Admin.**
   - **Ringkasan:** angka per periode, grafik, **Unduh CSV**, dan **Cetak**.
   - **Verifikasi tutor:** setujui Nurul, atau minta perbaikan.
   - **Pesanan:** batalkan pesanan yang sudah dibayar dengan alasan; pengembalian dana tercatat otomatis.
   - **Pengembalian dana:** tandai selesai.
   - **Pencairan dana:** setujui pengajuan Rina.
   - **Kelola tutor:** ubah mata pelajaran dengan alasan.
   - **Data pengguna:** nonaktifkan atau aktifkan akun.

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
