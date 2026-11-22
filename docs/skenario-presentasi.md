# Skenario presentasi EduLink Tutor v0.9 Beta

Naskah demo sekitar 12–15 menit. Alur utama mengikuti satu les dari awal sampai uang sampai ke tutor; alur kedua menunjukkan pendaftaran dan verifikasi tutor.

## Persiapan (sebelum presentasi dimulai)

1. Di `.env`: `EDULINK_MODE_DEMO=true`. Kalau lewat ngrok, isi juga `APP_URL` dengan alamat ngrok, `EDULINK_TAUTAN_DI_LAYAR=false`, dan `APP_DEBUG=false` (lihat README, bagian "Presentasi lewat ngrok").
2. `npm run build` (bukan `npm run dev`).
3. `php artisan serve` (dan `ngrok http 8000` kalau dipakai).
4. **Pada hari presentasi**, sebelum pukul 22.00: `php artisan edulink:siapkan-demo`, jawab `yes`. Data demo dihitung dari saat ini, jadi ada sesi hari ini, tagihan dengan sisa waktu, dan sesi menunggu konfirmasi.
5. Siapkan tiga jendela yang sesinya terpisah, lalu masuk di masing-masing:

| Jendela | Akun | Kata sandi |
|---|---|---|
| A: browser biasa | siti.aminah@edulink.test (orang tua) | orangtua123 |
| B: jendela samaran browser yang sama | andi.pratama@edulink.test (tutor), nanti ganti ke rina.marlina@edulink.test | tutor12345 |
| C: browser lain (mis. Firefox) | admin@edulink.test | admin12345 |

6. Buka tab landing page (`/`) di jendela A dan keluar dulu dari akun Siti di tab itu, atau buka landing di jendela C sebelum admin masuk.

Kalimat pembuka yang perlu disebut: ini versi Beta. Pembayaran disimulasikan (pita "Mode demo" di atas halaman), tidak ada uang sungguhan yang berpindah, dan sesi online memakai tautan Google Meet atau Zoom milik tutor sendiri.

---

## Alur utama: dari mencari guru sampai pencairan (±10 menit)

### 1. Landing page dan cari tutor (±1 menit)
**Akun:** belum masuk.

- Tunjukkan landing page: masalah yang dipecahkan (orang tua di Kota Jambi mencari guru les SD, SMP, SMA), pilihan online atau tutor datang ke rumah, tanya jawab, footer dengan label versi `v0.9 Beta`.
- Isi "Lembar cari guru" (mis. Bahasa Inggris, SMP), klik **Tampilkan tutor**.
- Di hasil pencarian, buka profil **Andi Pratama**: tarif per jam, mapel, jadwal mingguan, ulasan.

**Jelaskan:** hanya tutor yang lolos verifikasi admin yang tampil di pencarian.

### 2. Orang tua memesan (±1,5 menit)
**Akun:** Siti (jendela A).

- Di profil Andi, klik **Pesan tutor ini**.
- Pilih anak **Raka**, mapel **Bahasa Inggris SMP**, slot **Kamis 19.00** (sengaja dibiarkan kosong di data demo), tanggal mulai, durasi, jumlah pertemuan (mis. 4).
- Klik **Periksa pesanan**, tunjukkan rincian dan total biaya, lalu **Konfirmasi pesanan**.

**Jelaskan:** slot langsung ditahan untuk pesanan ini; tutor punya 24 jam untuk menjawab, kalau tidak, pesanan batal otomatis dan slot dilepas.

### 3. Tutor menerima (±1 menit)
**Akun:** Andi (jendela B).

- Tunjukkan lonceng notifikasi dan Beranda: permintaan baru dengan hitung mundur batas menjawab.
- Buka pesanan, klik **Terima pesanan**.

**Jelaskan:** setelah diterima, orang tua punya 24 jam untuk membayar.

### 4. Orang tua membayar dengan simulasi (±1 menit)
**Akun:** Siti (jendela A).

- Buka **Pesanan saya**, buka pesanan tadi, pilih cara bayar (mis. QRIS), klik **Bayar Rp…**.
- Di halaman gateway tiruan, klik **Pembayaran berhasil**.
- Status menjadi **Dibayar**, jadwal sesi terbentuk, tombol **Chat tutor via WhatsApp** muncul, dan ada tulisan "Tutor belum membagikan tautan meeting".

**Jelaskan:** hasil pembayaran dikirim ke webhook bertanda tangan HMAC, sama seperti payment gateway sungguhan; di Beta, gateway-nya disimulasikan.

Opsional (30 detik): sebagai Andi, buka pesanan itu, isi tautan Google Meet di **Tautan meeting pesanan**, klik **Bagikan tautan**. Siti kini melihat tautannya.

### 5. Tutor mencatat sesi (±1 menit)
**Akun:** ganti jendela B ke **Rina** (rina.marlina@edulink.test). Sesi pesanan baru tadi belum berlangsung, jadi pakai sesi Rina yang sudah lewat.

- Buka **Sesi**. Ada sesi IPA SD **Nadia** yang sudah lewat dan belum dicatat.
- Pilih **Terlaksana**, tulis catatan materi (mis. "Bab gaya dan gerak, latihan 10 soal"), klik **Simpan catatan**.
- Buka **Pendapatan**: bagian Rina untuk sesi itu masuk **Dana ditahan**, belum ke saldo tersedia.

**Jelaskan:** "Siswa tidak hadir" tetap dihitung terlaksana; tutor yang berhalangan tidak mencatat sesi, melainkan mengajukan jadwal ulang.

### 6. Orang tua mengonfirmasi (±1 menit)
**Akun:** Siti (jendela A).

- Muat ulang **Beranda**: kartu **Sesi menunggu konfirmasi Anda** dengan sisa waktu 48 jam.
- Klik **Konfirmasi sesi**. (Tombol yang sama ada di halaman notifikasi.)
- Sebutkan pilihan lainnya: **Sanggah** dengan alasan, yang otomatis membuat keluhan untuk admin.

**Jelaskan:** kalau orang tua diam 48 jam, sesi dikonfirmasi otomatis oleh sistem. Pesanan baru selesai saat semua sesinya dikonfirmasi.

### 7. Saldo tutor bertambah, tutor mengajukan pencairan (±1 menit)
**Akun:** Rina (jendela B).

- Muat ulang **Pendapatan**: bagian sesi tadi pindah dari dana ditahan ke **Saldo tersedia**.
- Tunjukkan rincian per pesanan (jumlah sesi dikonfirmasi, ditahan, tidak sah).
- Isi nominal (sebesar saldo tersedia atau kurang), klik **Ajukan pencairan**.

**Jelaskan rumusnya:**
- nilai per sesi = total ÷ jumlah pertemuan;
- bagian tutor = nilai per sesi − komisi platform;
- dana ditahan tidak bisa dicairkan.

### 8. Admin menyetujui dan melihat laporan (±2 menit)
**Akun:** admin (jendela C).

- Buka **Pencairan dana**: pengajuan Rina (dan Yusuf). Klik **Setujui** untuk Rina, lalu **Tandai dibayar**.
- Buka **Keluhan**: ada sanggahan Siti atas sesi Rina sebelumnya (jenis "Sanggahan sesi"). Tunjukkan panel **Putuskan sanggahan sesi**: sah berarti tutor dibayar, tidak sah berarti biaya sesi dikembalikan ke orang tua. (Boleh diputuskan langsung kalau waktu cukup.)
- Buka **Ringkasan**:
  - pendapatan komisi diakui per sesi yang dikonfirmasi pada periode itu, plus **Komisi ditahan**;
  - grafik.
- Klik **Unduh CSV**, lalu **Cetak**.

**Jelaskan:** komisi platform baru diakui saat sesinya dikonfirmasi, bukan saat pembayaran masuk.

---

## Alur kedua: pendaftaran dan verifikasi tutor (±3 menit)

### 9. Pendaftar tutor
**Akun:** belum masuk (jendela B, setelah keluar dari Rina), atau cukup ceritakan.

- Buka **Daftar jadi tutor** (`/daftar-tutor`). Tunjukkan lima langkah: akun, profil mengajar (mapel, jenjang, tarif, kecamatan), jadwal, dokumen (ijazah, KTP), kirim.
- Kalau waktu sempit, cukup tunjukkan langkah pertama dan katakan "datanya sudah kami siapkan".

### 10. Admin memverifikasi
**Akun:** admin (jendela C).

- Buka **Verifikasi tutor**, buka **Nurul Aisyah** (menunggu verifikasi).
- Tunjukkan dokumen, kesesuaian mata pelajaran, lalu **Setujui pendaftaran**. (Alternatif: **Kirim permintaan perbaikan** dengan catatan per bagian.)
- Buka **Cari tutor** di tab lain: Nurul sekarang tampil.

**Jelaskan:** pengajar bimbel EduLink (tutor internal) tidak mendaftar dari nol; mereka mengaktifkan akun di `/aktivasi-tutor`.

Opsional, kalau ingin menunjukkan aktivasi: di `/aktivasi-tutor` masukkan `lestari.putri@edulink.test`, lalu ambil tautannya dari log di terminal (tautan tidak ditampilkan di layar saat presentasi lewat ngrok):

```bash
grep -oE 'https?://[^"<> ]*/aktivasi-tutor/[^"<> ]*' storage/logs/laravel.log | tail -1 | sed 's/&amp;/\&/g'
```

Siapkan terminal ini sebelumnya. Tautan aktivasi berlaku 60 menit, jadi kalau ingin menyiapkannya lebih dulu, minta tautannya setelah `siapkan-demo` dan kurang dari satu jam sebelum dipakai.

---

## Penutup (±30 detik)
- Yang disimulasikan di Beta: payment gateway, transfer dana (admin mencatat), layanan video (diganti tautan Meet/Zoom dari tutor), notifikasi WhatsApp (diganti tautan wa.me).
- Rencana berikutnya: integrasi payment gateway sungguhan, integrasi dengan sistem EduManage, SSO.

---

## Rencana cadangan

| Masalah | Tindakan |
|---|---|
| Data berantakan, langkah tidak bisa diulang, atau salah klik | `php artisan edulink:siapkan-demo --force`, lalu masuk ulang di ketiga jendela. Butuh ±10 detik. |
| Slot Kamis 19.00 Andi sudah terpakai (demo diulang) | Jalankan `siapkan-demo` lagi, atau pilih slot Andi lain yang masih kosong. |
| Halaman tanpa CSS lewat ngrok | Pastikan `npm run dev` tidak berjalan, jalankan `npm run build`, lalu muat ulang dengan Ctrl+Shift+R. Periksa `APP_URL` sama dengan alamat ngrok yang aktif. |
| Tombol **Bayar** atau halaman gateway tiruan tidak ada / 404 | Mode demo mati: isi `EDULINK_MODE_DEMO=true`, jalankan `php artisan optimize:clear`. |
| Sesi Rina yang belum dicatat tidak ada | Sudah dicatat pada percobaan sebelumnya: jalankan `siapkan-demo` lagi. |
| Ingin menunjukkan konfirmasi otomatis 48 jam | `php artisan edulink:konfirmasi-otomatis --setelah=0`: semua sesi yang menunggu langsung dikonfirmasi sistem. |
| Ngrok putus atau lambat | Presentasikan dari `http://127.0.0.1:8000` di laptop; ubah `APP_URL` kembali dan jalankan `php artisan optimize:clear`. |
| Lupa kata sandi akun demo | Semua tutor `tutor12345`, orang tua `orangtua123`, admin `admin12345`. Daftar lengkap dicetak oleh `siapkan-demo`. |
| Perlu tautan aktivasi atau reset kata sandi | Tautan tidak tampil di layar (`EDULINK_TAUTAN_DI_LAYAR=false`). Ambil dari `storage/logs/laravel.log` dengan perintah di README, bagian "Mengambil tautan dari log email". |
