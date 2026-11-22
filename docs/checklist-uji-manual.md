# Checklist uji manual EduLink Tutor v0.9 Beta

Test otomatis (`php artisan test`) memeriksa aturan bisnis dan isi halaman, tetapi tidak melihat tampilan. Daftar ini untuk dicek sendiri di browser sebelum presentasi.

**Persiapan:**
- `php artisan edulink:siapkan-demo`
- `npm run build`
- `php artisan serve`

**Dua ukuran layar:**
- **HP**: 375 px. Di Chrome/Firefox: buka DevTools (F12) → ikon perangkat (Ctrl+Shift+M) → lebar 375. Lebih baik lagi kalau dicoba langsung di HP lewat ngrok.
- **Desktop**: 1440 px, atau layar laptop dengan jendela penuh.

**Yang dicari di setiap halaman:**
- tidak ada geser ke samping (scroll horizontal);
- tidak ada teks yang terpotong atau bertumpuk;
- tombol mudah diketuk di HP;
- tabel panjang bisa digeser di dalam kotaknya sendiri, bukan menggeser seluruh halaman;
- pita "Mode demo" tampil rapi di atas.

Centang kolom **HP** dan **Desktop** setelah dicek.

## Publik (belum masuk)

| Halaman / aksi | HP | Desktop |
|---|---|---|
| `/` landing: hero, lembar cari guru, langkah, tanya jawab, footer (alamat contoh, tautan WhatsApp, label "EduLink Tutor v0.9 Beta") | ☐ | ☐ |
| Ikon tab browser (favicon) tampil; di iPhone "Tambahkan ke Layar Utama" memakai ikon EduLink | ☐ | ☐ |
| Isi lembar cari guru → **Tampilkan tutor** → hasil pencarian | ☐ | ☐ |
| Profil tutor (mis. Andi Pratama): tarif, jadwal, ulasan, tombol **Pesan tutor ini** | ☐ | ☐ |
| `/syarat-ketentuan` dan `/kebijakan-privasi`: daftar isi dan bagian konfirmasi sesi 48 jam | ☐ | ☐ |
| `/masuk`, `/daftar`, lupa kata sandi, tombol tampilkan kata sandi | ☐ | ☐ |
| `/daftar-tutor`: kelima langkah formulir, unggah dokumen | ☐ | ☐ |
| `/aktivasi-tutor` dengan lestari.putri@edulink.test → tautan dari log (atau di layar kalau `EDULINK_TAUTAN_DI_LAYAR=true`) | ☐ | ☐ |
| Halaman 404 (mis. `/tidak-ada`) | ☐ | ☐ |

## Orang tua: siti.aminah@edulink.test / orangtua123

| Halaman / aksi | HP | Desktop |
|---|---|---|
| Beranda: kartu "Sesi menunggu konfirmasi Anda" muncul setelah Rina mencatat sesi (langkah tutor di bawah), dengan sisa waktu | ☐ | ☐ |
| Menu dasbor di HP (menu lipat) bisa dibuka dan ditutup, semua menu terjangkau | ☐ | ☐ |
| Pesan Andi: anak Raka, Bahasa Inggris SMP, slot Kamis 19.00 → **Periksa pesanan** → **Konfirmasi pesanan** | ☐ | ☐ |
| Detail pesanan: linimasa status, hitung mundur batas waktu | ☐ | ☐ |
| **Bayar Rp…** → halaman gateway tiruan → **Pembayaran berhasil** → kembali ke pesanan berstatus Dibayar | ☐ | ☐ |
| Coba juga **Pembayaran gagal** pada pesanan lain, lalu bayar ulang | ☐ | ☐ |
| Detail pesanan dibayar: daftar sesi, "Tutor belum membagikan tautan meeting", tombol **Chat tutor via WhatsApp** | ☐ | ☐ |
| Setelah Andi membagikan tautan: tautan meeting tampil dan bisa diklik | ☐ | ☐ |
| **Konfirmasi sesi** dari beranda dan dari halaman notifikasi | ☐ | ☐ |
| **Sanggah**: alasan kosong ditolak dengan pesan galat; alasan diisi → status "Disanggah, menunggu admin" | ☐ | ☐ |
| Ajukan jadwal ulang untuk sesi lebih dari 24 jam lagi | ☐ | ☐ |
| Ulasan untuk pesanan selesai (Matematika SD dengan Rina) | ☐ | ☐ |
| Keluhan baru dari detail pesanan | ☐ | ☐ |
| Pembayaran: riwayat dan status pengembalian dana | ☐ | ☐ |
| Data anak: tambah dan ubah | ☐ | ☐ |
| Lonceng notifikasi: daftar dan tanda sudah dibaca | ☐ | ☐ |

## Tutor: andi.pratama, rina.marlina, dewi.anggraini@edulink.test / tutor12345

| Halaman / aksi | HP | Desktop |
|---|---|---|
| Andi, beranda: permintaan baru dengan hitung mundur → **Terima pesanan** (dan **Tolak pesanan** dengan alasan di pesanan lain) | ☐ | ☐ |
| Andi, detail pesanan dibayar: isi **Tautan meeting pesanan** (`https://meet.google.com/...`) → **Bagikan tautan**; tautan `http://` ditolak | ☐ | ☐ |
| Andi: ganti tautan khusus satu sesi | ☐ | ☐ |
| Dewi, beranda: "Sesi hari ini dan besok" berisi sesi hari ini dan besok | ☐ | ☐ |
| Rina, **Sesi**: sesi IPA Nadia yang lewat → **Terlaksana** + catatan → **Simpan catatan** | ☐ | ☐ |
| Rina, **Pendapatan**: panel Saldo tersedia dan Dana ditahan beserta penjelasannya; tabel rincian per pesanan | ☐ | ☐ |
| Rina: **Ajukan pencairan** melebihi saldo tersedia → ditolak; sesuai saldo → berhasil | ☐ | ☐ |
| Rina, **Jadwal**: tambah dan hapus slot | ☐ | ☐ |
| Ulasan, Keluhan, Profil (rekening tersamar ••••4417) | ☐ | ☐ |
| nurul.aisyah: halaman "Sedang diperiksa"; intan.permata: formulir perbaikan dengan catatan | ☐ | ☐ |

## Admin: admin@edulink.test / admin12345

| Halaman / aksi | HP | Desktop |
|---|---|---|
| **Ringkasan**: kartu angka termasuk Pendapatan komisi dan Komisi ditahan, grafik, pilih periode | ☐ | ☐ |
| **Unduh CSV** terbuka rapi di spreadsheet (kolom dipisah titik koma) | ☐ | ☐ |
| **Cetak**: pratinjau cetak satu halaman rapi, tanpa menu dan pita mode demo | ☐ | ☐ |
| **Verifikasi tutor**: buka Nurul, lihat dokumen PDF, **Setujui pendaftaran**; coba **Kirim permintaan perbaikan** pada Bayu | ☐ | ☐ |
| **Keluhan**: sanggahan sesi Rina → panel **Putuskan sanggahan sesi** → sah / tidak sah dengan alasan | ☐ | ☐ |
| **Pengembalian dana**: "x dari y sesi dikembalikan", tandai selesai | ☐ | ☐ |
| **Pencairan dana**: rekening lengkap, **Setujui**, **Tandai dibayar** | ☐ | ☐ |
| **Pesanan**: detail, status konfirmasi per sesi, batalkan pesanan dibayar dengan alasan | ☐ | ☐ |
| **Kelola tutor**: ubah mata pelajaran dengan alasan | ☐ | ☐ |
| **Data pengguna**: nonaktifkan lalu aktifkan akun, dengan alasan | ☐ | ☐ |
| **Komisi**: ubah persentase | ☐ | ☐ |
| **Ulasan**: sembunyikan dan tampilkan | ☐ | ☐ |

## Lewat ngrok (kalau dipakai saat presentasi)

| Cek | Hasil |
|---|---|
| Alamat `https://...ngrok-free.app` terbuka dengan CSS (bukan halaman polos) | ☐ |
| Masuk, keluar, dan kirim formulir berjalan (tidak ada galat 419) | ☐ |
| Pembayaran simulasi berhasil kembali ke halaman pesanan | ☐ |
| Dibuka dari HP lain di jaringan berbeda | ☐ |

## Catatan temuan

Tulis halaman, ukuran layar, dan apa yang terlihat salah, lalu kirimkan untuk diperbaiki.

| Halaman | HP/Desktop | Masalah |
|---|---|---|
| | | |
