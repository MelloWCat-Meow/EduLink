# Status proyek EduLink Tutor v0.9 Beta

Kondisi per 22 November 2026, branch `feature/finishing_demo` (commit `824c3b1`). Dokumen ini merangkum sejauh mana versi Beta memenuhi rancangan sistem, untuk dosen dan tim.

## 1. Gambaran singkat

EduLink Tutor adalah aplikasi web yang mempertemukan orang tua di Kota Jambi dengan guru les privat SD, SMP, dan SMA, secara online atau tutor datang ke rumah. Orang tua mencari dan memesan tutor, membayar lewat platform, lalu mengonfirmasi setiap sesi. Tutor mendaftar dan diverifikasi, mencatat sesi, dan mencairkan pendapatannya. Admin memverifikasi tutor, menangani keluhan, dan melihat laporan.

**Stack** (dari `php artisan about`):
- Laravel 13.43, PHP 8.5.12, Composer 2.11, MySQL;
- Blade dan Tailwind CSS v4 lewat Vite;
- session, cache, dan queue di database; mail driver `log`;
- zona waktu Asia/Jakarta.

**Angka proyek** (tanpa `vendor` dan `node_modules`):

| Ukuran | Jumlah |
|---|---|
| Route aplikasi | 113 (33 admin, 40 bernama `tutor.*` termasuk pencarian dan pendaftaran tutor, 40 lainnya untuk publik, orang tua, autentikasi, dan webhook) |
| Model Eloquent | 20 |
| Migration | 25 |
| Tabel database | 30 (15 dari ERD awal, 9 tambahan aplikasi, 6 bawaan Laravel) |
| Kelas layanan (`app/Services`) | 21 |
| Controller | 47 |
| Enum status | 20 |
| Perintah Artisan | 7 (4 terjadwal) |
| View Blade | 129 |
| Test otomatis | 401 test, 2.129 assertion |
| Baris kode | ±10.700 PHP aplikasi (`app`), ±6.000 Blade, ±770 CSS/JS, ±2.350 database (migration, seeder, factory), ±5.800 test |

## 2. Pemetaan ke rancangan sistem

Keterangan: ✅ selesai · 🟡 sebagian atau disimulasikan · ⬜ belum.

### Modul aplikasi

| Modul | Status | Implementasi | Catatan |
|---|---|---|---|
| MD01 Registrasi dan Autentikasi | ✅ | `/daftar`, `/masuk`, lupa dan ganti kata sandi; middleware `peran` (ortu, tutor, admin) dan policy | Login email + kata sandi, dibatasi per IP. SSO tidak ada (lihat bagian komponen). |
| MD02 Registrasi dan Verifikasi Tutor | ✅ | `/daftar-tutor` (5 langkah), `/aktivasi-tutor` untuk tutor internal, admin **Verifikasi tutor** | Unggah ijazah/KTP/sertifikat ke disk privat; admin menyetujui, meminta perbaikan per bagian, atau menolak; riwayat verifikasi tercatat. |
| MD03 Pengelolaan Profil Tutor | ✅ | Area tutor **Profil** | Kompetensi, pengalaman, pendidikan, tarif, kecamatan, foto, mode layanan. Mata pelajaran hanya diubah admin (sudah diverifikasi). |
| MD04 Jadwal dan Ketersediaan | ✅ | Area tutor **Jadwal**; `AlurPesanan` | Slot mingguan; tersedia → ditahan → terisi, dilepas saat ditolak/batal/selesai/kedaluwarsa 24 jam. |
| MD05 Pencarian Tutor | ✅ | `/tutor` | Filter mapel, jenjang, kecamatan, rentang tarif, rating minimal; urut menurut rating. Hanya tutor terverifikasi dengan profil lengkap. |
| MD06 Pemesanan | ✅ | `/tutor/{tutor}/pesan`, **Pesanan saya**, area tutor **Pesanan** | Periksa lalu konfirmasi; tutor menerima/menolak dalam 24 jam; pembatalan oleh orang tua, tutor, admin, atau otomatis. |
| MD07 Pembayaran | 🟡 | `PemrosesPembayaran`, `POST /webhooks/payment`, menu **Pembayaran** | Inisiasi, status lewat webhook bertanda tangan HMAC (idempoten, pembayaran terlambat ditolak), riwayat. Gateway-nya disimulasikan. |
| MD08 Komisi dan Biaya Layanan | ✅ | Admin **Komisi**, tabel `komisi_biaya_layanan` | Persentase diatur admin, dikunci saat pesanan dibayar; diakui per sesi saat sesi dikonfirmasi. |
| MD09 Pencairan Dana Tutor | 🟡 | Tutor **Pendapatan**, admin **Pencairan dana** | Saldo tersedia dan dana ditahan per sesi; pengajuan dibatasi saldo; admin menyetujui lalu menandai dibayar. Transfer bank dilakukan dan dicatat manual. |
| MD10 Pelaksanaan Sesi | 🟡 | Tutor **Sesi**, detail pesanan, `KonfirmasiSesi` | Catat "Terlaksana"/"Siswa tidak hadir", konfirmasi atau sanggah orang tua, konfirmasi otomatis 48 jam, jadwal ulang. Sesi online memakai tautan Meet/Zoom dari tutor, bukan layanan video sendiri. |
| MD11 Rating dan Ulasan | ✅ | Detail pesanan selesai, tutor **Ulasan**, admin **Ulasan** | Satu ulasan per pesanan selesai; rata-rata hanya dari ulasan yang ditampilkan; moderasi dengan alasan. |
| MD12 Penanganan Keluhan | ✅ | **Keluhan** (ortu, tutor, admin) | Baru → diproses → selesai dengan tanggapan; sanggahan sesi otomatis menjadi keluhan yang diputuskan admin. |
| MD13 Notifikasi | 🟡 | Lonceng notifikasi, `Pemberitahu`, email | Notifikasi dalam aplikasi + email untuk kejadian penting (email ke log). Push notification tidak ada; WhatsApp diganti tombol `wa.me`. Pengingat H-1 terjadwal. |
| MD14 Integrasi EduManage | ⬜ | – | Sengaja tidak diimplementasikan di Beta. Tutor internal diisi dari seeder dan mengaktifkan akun sendiri. |
| MD15 Pelaporan dan Analitik | ✅ | Admin **Ringkasan** | Transaksi, pendapatan komisi diakui, komisi ditahan, pesanan per status, grafik harian, ekspor CSV, halaman cetak/PDF. Belum ada analitik lintas aplikasi. |
| MD16 Administrasi dan Monitoring | ✅ | Admin **Pesanan**, **Ulasan**, **Komisi**, **Data pengguna**, **Kelola tutor**, **Pengembalian dana** | Monitoring pesanan dengan linimasa, moderasi ulasan, pengaturan komisi, nonaktif akun dengan alasan, ubah mapel tutor. Monitoring server tidak ada. |

### Proses bisnis target

| Proses | Status | Catatan |
|---|---|---|
| T1 Registrasi dan verifikasi tutor | ✅ | Eksternal (formulir 5 langkah → verifikasi) dan internal (aktivasi → lengkapi → verifikasi). |
| T2 Pencarian dan pemesanan tutor | ✅ | Termasuk penahanan slot dan batas waktu 24 jam di setiap langkah. |
| T3 Pembayaran dan penyelesaian komisi | 🟡 | Alur lengkap dan uang dihitung per sesi; gateway dan transfer disimulasikan. |
| T4 Pelaksanaan pembelajaran | 🟡 | Catat, konfirmasi, sanggah, jadwal ulang lengkap; video diganti tautan dari tutor. |
| T5 Rating, ulasan, dan keluhan | ✅ | Termasuk sanggahan sesi dan putusan admin (sah atau tidak sah dengan pengembalian dana). |
| T6 Pelaporan terpadu | 🟡 | Laporan dalam aplikasi Tutor; belum terpadu dengan EduManage. |

### Komponen arsitektur yang disimulasikan atau diganti

| Komponen target | Pengganti di Beta |
|---|---|
| Payment gateway | Halaman gateway tiruan (hanya di mode demo) yang mengirim hasil ke webhook bertanda tangan HMAC, dengan kontrak yang sama seperti gateway sungguhan. |
| Layanan video pembelajaran | Tautan Google Meet/Zoom yang dibagikan tutor per pesanan, bisa diganti per sesi; pengingat H-1 menegur tutor yang belum mengisi. |
| Notifikasi WhatsApp | Tombol tautan `wa.me` antara orang tua dan tutor untuk pesanan yang dibayar; notifikasi lain lewat aplikasi dan email. |
| Transfer dana | Admin mencatat pencairan (disetujui → dibayar) dan pengembalian (perlu diproses → selesai) setelah transfer manual. |
| SSO / Identity Provider | Login lokal per aplikasi dengan peran dan policy Laravel. |
| API Gateway | Tidak ada; satu-satunya endpoint untuk sistem luar adalah webhook pembayaran. |
| Integrasi EduManage | Tidak ada; data pengajar internal dari seeder. |
| Pemisahan dev/staging/production | Satu environment lokal; mode demo (`EDULINK_MODE_DEMO`) memisahkan fitur simulasi dari perilaku produksi, dan test berjalan di SQLite in-memory. |

## 3. Fitur tambahan di luar rancangan awal

- Konfirmasi sesi oleh orang tua (48 jam, otomatis kalau diam), sanggahan, dan putusan admin.
- Dana ditahan per sesi dan pengakuan komisi per sesi pada periode konfirmasi.
- Pengembalian dana per sesi (pembatalan admin dan sesi tidak sah).
- Jadwal ulang sesi (maks. 2 kali, persetujuan pihak lain, kedaluwarsa otomatis).
- Penahanan slot dengan batas waktu 24 jam dan pelepasan otomatis.
- Nonaktif akun dengan alasan, termasuk pembatalan otomatis pesanan yang masih ditahan.
- Lupa kata sandi dan ganti kata sandi; sesi lain berakhir saat kata sandi berubah.
- Kebijakan Privasi dan Syarat & Ketentuan, dengan bukti persetujuan dan versinya.
- Rekening pencairan terenkripsi dan ditampilkan tersamar.
- Ekspor CSV dan halaman cetak laporan.
- Linimasa status pesanan, verifikasi tutor, dan status akun.
- Mode demo, perintah simulasi, dan `edulink:siapkan-demo` untuk presentasi.

## 4. Kualitas dan pengujian

**Test per area** (termasuk variasi data provider):

| Area | Test |
|---|---|
| Pesanan, pembayaran, sesi, saldo (`Feature/Pesanan`) | 80 |
| Tutor: pendaftaran, verifikasi, dasbor (`Feature/Tutor`) | 66 |
| Orang tua (`Feature/Ortu`) | 51 |
| Autentikasi dan kata sandi (`Feature/Auth`) | 45 |
| Admin (`Feature/Admin`) | 39 |
| Data demo dan `siapkan-demo` | 24 |
| Notifikasi | 21 |
| Mode demo dan tautan di layar | 25 |
| Pesan validasi berbahasa Indonesia | 10 |
| Nomor WhatsApp (unit) | 13 |
| Landing, footer, halaman galat, persetujuan, contoh | 27 |
| **Total** | **401** |

**Hasil verifikasi teknis** (22 November 2026):

| Pemeriksaan | Hasil |
|---|---|
| `php artisan test` | 401 lolos, 0 gagal (2.129 assertion) |
| `vendor/bin/pint --test` | Bersih |
| `npm run build` | Berhasil |
| `migrate:fresh --seed` | Berhasil |
| `migrate:rollback` sampai habis, lalu `migrate` | Berhasil, juga dengan data demo terisi. (`down()` migration `2026_11_20_140001` kini mengisi `tarif`/`lokasi` yang kosong sebelum kolomnya kembali `NOT NULL`.) |
| Route | 113 route aplikasi; semua controller, method, dan view yang dirujuk ada |
| Scheduler | 4 perintah terdaftar: lepas-penahanan (setiap menit), jadwal-ulang-kedaluwarsa (setiap menit), konfirmasi-otomatis (setiap jam), pengingat-sesi (07.00) |

**Belum diuji:**
- Tampilan di browser sungguhan (HP dan desktop) belum diperiksa. Daftar pemeriksaan manualnya ada di [checklist-uji-manual.md](checklist-uji-manual.md).
- Tidak ada test browser (Dusk).
- Aksesibilitas baru diperiksa secara statis: semua input punya label dan gambar punya `alt`.
- Diagram Mermaid di [arsitektur.md](arsitektur.md) belum dilihat hasil rendernya.
- Pengiriman email sungguhan tidak diuji (mailer `log`).
- Belum ada uji beban.

## 5. Keterbatasan Beta dan rencana berikutnya

Diurutkan menurut prioritas:

1. **Integrasi payment gateway sungguhan** (mis. Midtrans/Xendit) di belakang kontrak webhook yang sudah ada, plus mode sandbox.
2. **Penguatan keamanan untuk akses publik:**
   - tautan reset kata sandi dan aktivasi sudah hanya tampil di layar kalau `EDULINK_TAUTAN_DI_LAYAR=true` (bawaan mati), dan tautan reset admin tidak pernah ditampilkan; untuk produksi, kirim lewat email sungguhan;
   - `APP_DEBUG=false` di setiap lingkungan publik;
   - kata sandi admin yang kuat (akun demo masih memakai kata sandi sederhana).
3. **Pengiriman email sungguhan** (SMTP/layanan email) dan, bila anggaran ada, WhatsApp Business API untuk pengingat.
4. **Integrasi EduManage** untuk data pengajar internal dan siswa, lalu SSO antar-aplikasi EduLink.
5. **Lingkungan staging dan production terpisah,** deploy otomatis, backup database, dan monitoring error.
6. **Test browser (Dusk)** untuk alur utama, serta pemeriksaan aksesibilitas dengan pembaca layar.
7. **Analitik lanjutan:** retensi orang tua, tingkat konfirmasi sesi, waktu respons tutor.

## 6. Riwayat pengembangan

| Tanggal | Tahap | Commit penting |
|---|---|---|
| 18 Nov 2026 | Inisialisasi proyek Laravel dan Vite | `213d602`, `ad62d34`, `10b817c` |
| 19–20 Nov 2026 | Landing page, lalu gaya "buku tulis" | `3fc8ec3`, `b815428` (PR #2) |
| 20 Nov 2026 | Halaman autentikasi | `625999a`, `6ab3ea1` |
| 20 Nov 2026 | Data marketplace, alur status pesanan, dasbor orang tua dan admin | `ded6fbd`, `4e912cb`, `7dbd60e` |
| 20–21 Nov 2026 | Pendaftaran dan verifikasi tutor, aktivasi internal, dasbor tiap peran | `423e672`, `4b7a652`, `530600a` (PR #3) |
| 21 Nov 2026 | Fitur krusial: pembayaran, sesi, jadwal ulang, pengembalian, notifikasi, WhatsApp, laporan; tampilkan kata sandi | `1d64b82` (PR #4), `d24c74d` (PR #5) |
| 22 Nov 2026 | Konfirmasi sesi, dana ditahan, tautan meeting dari tutor | `df09221` |
| 22 Nov 2026 | Perbaikan seeder, ngrok, favicon; penggabungan kembali fitur konfirmasi sesi | `a1c3cd8`, `20b79b5`, `a30e3f8`, `7d1aec9` |
| 22 Nov 2026 | Finishing Beta: mode demo, `siapkan-demo`, dokumen arsitektur dan presentasi | `5e0a2be`, `824c3b1` (PR #7 ke `dev`) |

Catatan repositori: `origin/dev` sudah memuat semua isi branch aktif. `origin/main` masih di commit 18 November (hanya kerangka Laravel); belum ada PR dari `dev` ke `main`.
