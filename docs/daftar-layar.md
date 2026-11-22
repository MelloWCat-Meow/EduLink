# Daftar layar EduLink Tutor v0.9 Beta

Daftar tampilan aplikasi untuk laporan dan untuk capture ke Figma (layer yang bisa diedit). Dipakai juga sebagai catatan progres capture: kalau capture terputus, lanjutkan dari baris yang statusnya belum.

- **File Figma:** https://www.figma.com/design/MoyHIHQt2rvNSxVPxQRRYD
- **Halaman Figma:** Orang Tua (`0:1`), Tutor (`47:2`), Admin (`47:3`). Halaman error dan halaman indeks tidak dibuat.
- **Data:** `php artisan edulink:siapkan-demo --force`, dijalankan sebelum capture. ID pesanan dicari saat capture menurut keadaannya, karena nomor bisa berubah setiap data disiapkan ulang.
- **Lebar:** Desktop 1440 px, Mobile 375 px. Nama frame: `<kode> <nama> — Desktop/Mobile`.
- **Kode:** PB publik, AU autentikasi, OT orang tua, TP pendaftaran tutor, TD dasbor tutor, AD admin.
- **Status:** `belum`, `desktop` (baru versi desktop), `mobile` (baru versi mobile), `selesai` (keduanya), `gagal` (dengan alasan), `dilewati` (dengan alasan). Node Figma dicatat bila diketahui.

Akun demo: orang tua `orangtua123`, tutor `tutor12345`, admin `admin12345`.

## Halaman Figma "Orang Tua" (`0:1`)

Urutan mengikuti alur: datang → mencari → masuk → memesan → membayar → mengikuti pesanan.

| Kode | Nama | URL | Akun | Keadaan yang di-capture | Prioritas | Status |
|---|---|---|---|---|---|---|
| PB-01 | Landing page | `/` | – | Halaman penuh setelah animasi masuk selesai | 1 | selesai (uji coba: Desktop `46:2`, Mobile `45:2`) |
| PB-02 | Hasil pencarian tutor | `/tutor?mapel=Bahasa+Inggris&jenjang=SMP` | – | Dengan filter mapel dan jenjang | 1 | selesai (Desktop `49:2`, Mobile `60:2`) |
| PB-03 | Profil tutor | `/tutor/{Andi Pratama}` | – | Tarif, jadwal, ulasan, tombol pesan | 1 | selesai (Desktop `61:2`, Mobile `62:2`) |
| AU-01 | Masuk | `/masuk` | – | Form kosong | 1 | selesai (Desktop `63:2`, Mobile `64:2`) |
| AU-02 | Daftar akun orang tua | `/daftar` | – | Form kosong | 1 | selesai (Desktop `65:2`, Mobile `66:2`) |
| AU-03 | Lupa kata sandi | `/lupa-kata-sandi` | – | Form kosong | 1 | selesai (Desktop `67:2`, Mobile `68:2`) |
| OT-01 | Beranda orang tua | `/beranda` | budi.santoso | Kartu "Sesi menunggu konfirmasi Anda" dengan sisa waktu | 1 | selesai (Desktop `69:2`, Mobile `70:2`) |
| OT-02 | Data anak | `/anak` | siti.aminah | Dua anak | 1 | selesai (Desktop `71:2`, Mobile `72:2`) |
| OT-03 | Form pesan tutor | `/tutor/{Andi}/pesan` | siti.aminah | Form kosong | 1 | selesai (Desktop `73:2`, Mobile `74:2`) |
| OT-04 | Konfirmasi pesanan | `POST /tutor/{Andi}/pesan` | siti.aminah | Setelah Periksa pesanan (Raka, Bahasa Inggris SMP, Kamis 19.00) | 1 | selesai (Desktop `75:2`, Mobile `76:2`) |
| OT-05 | Daftar pesanan | `/pesanan` | siti.aminah | Pesanan di semua status | 1 | selesai (Desktop `77:2`, Mobile `78:2`) |
| OT-06 | Simulasi pembayaran | `/simulasi/pembayaran/{id}` | siti.aminah | Setelah klik Bayar pada pesanan menunggu pembayaran | 1 | selesai (Desktop `79:2`, Mobile `80:2`) |
| OT-07 | Detail pesanan dibayar | `/pesanan/{Budi–Andi, sesi kemarin}` | budi.santoso | Sesi, tautan meeting, WhatsApp, kartu konfirmasi sesi | 1 | selesai (Desktop `81:2`, Mobile `82:2`) |
| OT-08 | Detail pesanan selesai | `/pesanan/{selesai, belum diulas}` | siti.aminah | Form ulasan | 2 | selesai (Desktop `83:2`, Mobile `84:2`) |
| OT-09 | Pembayaran | `/pembayaran` | siti.aminah | Riwayat pembayaran dan pengembalian | 2 | selesai (Desktop `85:2`, Mobile `86:2`) |
| OT-10 | Keluhan orang tua | `/keluhan` | siti.aminah | Daftar keluhan | 2 | selesai (Desktop `87:2`, Mobile `88:2`) |
| OT-11 | Notifikasi | `/notifikasi` | siti.aminah | Daftar notifikasi | 2 | selesai (Desktop `89:2`, Mobile `90:2`) |
| OT-12 | Ubah data anak | `/anak/{Raka}/ubah` | siti.aminah | Form terisi | 2 | selesai (Desktop `91:2`, Mobile `92:2`) |
| OT-13 | Akun saya (orang tua) | `/akun` | siti.aminah | Data akun dan ganti kata sandi | 2 | selesai (Desktop `93:2`, Mobile `94:2`) |
| AU-04 | Atur ulang kata sandi | `/lupa-kata-sandi/{token}` | – | Tautan diambil dari log email | 2 | selesai (Desktop `95:2`, Mobile `96:2`) |
| PB-04 | Kebijakan privasi | `/kebijakan-privasi` | – | Dokumen penuh | 2 | selesai (Desktop `97:2`, Mobile `99:2`) |
| PB-05 | Syarat dan ketentuan | `/syarat-ketentuan` | – | Dokumen penuh | 2 | selesai (Desktop `100:2`, Mobile `101:2`) |

Keadaan tambahan (desktop saja):

| Kode | Nama | URL | Akun | Keadaan yang di-capture | Status |
|---|---|---|---|---|---|
| AU-05 | Masuk dengan pesan validasi | `/masuk` | – | Form dikirim kosong | desktop (Desktop `102:2`) |
| AU-06 | Daftar dengan pesan validasi | `/daftar` | – | Form dikirim kosong | desktop (Desktop `103:2`) |
| OT-14 | Pesanan menunggu konfirmasi tutor | `/pesanan/{menunggu konfirmasi tutor}` | siti.aminah | Hitung mundur 24 jam | desktop (Desktop `104:2`) |
| OT-15 | Daftar keluhan kosong | `/keluhan` | budi.santoso | Keadaan kosong | desktop (Desktop `105:2`) |

## Halaman Figma "Tutor" (`47:2`)

Urutan: mendaftar → menunggu verifikasi → (tutor internal: aktivasi) → bekerja di dasbor.

| Kode | Nama | URL | Akun | Keadaan yang di-capture | Prioritas | Status |
|---|---|---|---|---|---|---|
| TP-01 | Pendaftaran tutor: buat akun | `/daftar-tutor` | – | Langkah awal | 1 | selesai (Desktop `106:2`, Mobile `107:2`) |
| TP-02 | Pendaftaran tutor: data diri | `/daftar-tutor/data-diri` | galih.prakoso | Draf | 1 | selesai (Desktop `108:2`, Mobile `109:2`) |
| TP-03 | Pendaftaran tutor: profil mengajar | `/daftar-tutor/profil` | galih.prakoso | Draf | 1 | selesai (Desktop `110:2`, Mobile `111:2`) |
| TP-04 | Pendaftaran tutor: dokumen | `/daftar-tutor/dokumen` | galih.prakoso | Draf | 1 | selesai (Desktop `112:2`, Mobile `113:2`) |
| TP-05 | Pendaftaran tutor: jadwal | `/daftar-tutor/jadwal` | galih.prakoso | Draf | 1 | selesai (Desktop `114:2`, Mobile `115:2`) |
| TP-06 | Pendaftaran tutor: kirim | `/daftar-tutor/kirim` | galih.prakoso | Ringkasan sebelum dikirim | 1 | selesai (Desktop `116:2`, Mobile `117:2`) |
| TP-07 | Status pendaftaran menunggu | `/daftar-tutor/status` | nurul.aisyah | "Sedang diperiksa" | 1 | selesai (Desktop `118:2`, Mobile `119:2`) |
| TP-08 | Aktivasi tutor internal | `/aktivasi-tutor` | – | Form email | 2 | selesai (Desktop `120:2`, Mobile `121:2`) |
| TP-09 | Aktivasi: buat kata sandi | `/aktivasi-tutor/{tutor}/kata-sandi?...` | lestari.putri | Tautan bertanda tangan dari log email | 2 | selesai (Desktop `122:2`, Mobile `123:2`) |
| TD-01 | Beranda tutor | `/area-tutor` | andi.pratama | Permintaan baru dengan hitung mundur | 1 | selesai (Desktop `124:2`, Mobile `125:2`) |
| TD-02 | Detail pesanan masuk | `/area-tutor/pesanan/{menunggu konfirmasi tutor}` | andi.pratama | Tombol Terima/Tolak, hitung mundur | 1 | selesai (Desktop `126:2`, Mobile `127:2`) |
| TD-03 | Daftar pesanan tutor | `/area-tutor/pesanan` | andi.pratama | Semua status | 2 | selesai (Desktop `128:2`, Mobile `129:2`) |
| TD-04 | Detail pesanan dibayar (tutor) | `/area-tutor/pesanan/{Dewi, sesi besok}` | dewi.anggraini | Tautan meeting pesanan dan per sesi | 2 | selesai (Desktop `130:2`, Mobile `131:2`) |
| TD-05 | Sesi tutor | `/area-tutor/sesi` | rina.marlina | Sesi lewat yang belum dicatat | 1 | selesai (Desktop `132:2`, Mobile `133:2`) |
| TD-06 | Pendapatan tutor | `/area-tutor/pendapatan` | rina.marlina | Saldo tersedia, dana ditahan, rincian | 1 | selesai (Desktop `134:2`, Mobile `135:2`) |
| TD-07 | Jadwal tutor | `/area-tutor/jadwal` | andi.pratama | Slot mingguan | 2 | selesai (Desktop `136:2`, Mobile `137:2`) |
| TD-08 | Ulasan tutor | `/area-tutor/ulasan` | rina.marlina | Daftar ulasan | 2 | selesai (Desktop `138:2`, Mobile `139:2`) |
| TD-09 | Keluhan tutor | `/area-tutor/keluhan` | rina.marlina | Daftar keluhan | 2 | selesai (Desktop `140:2`, Mobile `141:2`) |
| TD-10 | Notifikasi tutor | `/notifikasi` | rina.marlina | Daftar notifikasi | 2 | selesai (Desktop `142:2`, Mobile `143:2`) |
| TD-11 | Profil tutor (area tutor) | `/area-tutor/profil` | rina.marlina | Profil dan rekening | 2 | selesai (Desktop `144:2`, Mobile `145:2`) |

Keadaan tambahan (desktop saja):

| Kode | Nama | URL | Akun | Keadaan yang di-capture | Status |
|---|---|---|---|---|---|
| TP-10 | Pendaftaran dengan catatan perbaikan | `/daftar-tutor/profil` | intan.permata | Catatan perbaikan dari admin | desktop (Desktop `146:2`) |

## Halaman Figma "Admin" (`47:3`)

Urutan: ringkasan → verifikasi tutor → pesanan → keluhan → uang → data → pengaturan.

| Kode | Nama | URL | Akun | Keadaan yang di-capture | Prioritas | Status |
|---|---|---|---|---|---|---|
| AD-01 | Ringkasan admin | `/admin` | admin | Periode berjalan, grafik | 1 | selesai (Desktop `147:2`, Mobile `148:2`) |
| AD-02 | Laporan cetak | `/admin/laporan/cetak` | admin | Halaman cetak periode berjalan | 2 | selesai (Desktop `149:2`, Mobile `150:2`) |
| AD-03 | Antrean verifikasi | `/admin/verifikasi` | admin | Antrean | 2 | selesai (Desktop `151:2`, Mobile `152:2`) |
| AD-04 | Detail verifikasi tutor | `/admin/verifikasi/{Nurul}` | admin | Dokumen dan profil menunggu pemeriksaan | 1 | selesai (Desktop `153:2`, Mobile `154:2`) |
| AD-05 | Monitoring pesanan | `/admin/pesanan` | admin | Daftar semua pesanan | 1 | selesai (Desktop `155:2`, Mobile `156:2`) |
| AD-06 | Detail pesanan admin | `/admin/pesanan/{dibayar}` | admin | Linimasa, status sesi | 2 | selesai (Desktop `157:2`, Mobile `158:2`) |
| AD-07 | Keluhan admin | `/admin/keluhan` | admin | Daftar termasuk sanggahan sesi | 1 | selesai (Desktop `159:2`, Mobile `160:2`) |
| AD-08 | Detail sanggahan sesi | `/admin/keluhan/{sanggahan Rina}` | admin | Panel "Putuskan sanggahan sesi" | 1 | selesai (Desktop `161:2`, Mobile `162:2`) |
| AD-09 | Pencairan dana | `/admin/pencairan` | admin | Pengajuan Yusuf menunggu | 1 | selesai (Desktop `163:2`, Mobile `164:2`) |
| AD-10 | Pengembalian dana | `/admin/pengembalian` | admin | Daftar pengembalian | 2 | selesai (Desktop `165:2`, Mobile `166:2`) |
| AD-11 | Kelola tutor | `/admin/tutor` | admin | Daftar tutor | 2 | selesai (Desktop `167:2`, Mobile `168:2`) |
| AD-12 | Detail tutor admin | `/admin/tutor/{Rina}` | admin | Ubah mata pelajaran | 2 | selesai (Desktop `169:2`, Mobile `170:2`) |
| AD-13 | Data pengguna | `/admin/pengguna` | admin | Daftar akun | 2 | selesai (Desktop `171:2`, Mobile `172:2`) |
| AD-14 | Detail pengguna | `/admin/pengguna/{Siti}` | admin | Nonaktif/aktifkan | 2 | selesai (Desktop `173:2`, Mobile `174:2`) |
| AD-15 | Moderasi ulasan | `/admin/ulasan` | admin | Daftar ulasan | 2 | selesai (Desktop `175:2`, Mobile `176:2`) |
| AD-16 | Komisi | `/admin/komisi` | admin | Persentase | 2 | selesai (Desktop `177:2`, Mobile `178:2`) |
| AD-17 | Akun saya (admin) | `/admin/akun` | admin | Data akun | 2 | selesai (Desktop `179:2`, Mobile `180:2`) |

## Tidak di-capture

- Halaman error (403, 404, 419, 429, 500, 503) dan halaman indeks, sesuai keputusan pembagian halaman Figma.

## Catatan capture

- **Cara:** `generate_figma_design` dengan `nodeId` halaman tujuan. Halaman dibuka di Chromium (nix) lewat Playwright dengan sesi akun demo. Semua animasi dibiarkan selesai, lalu `capture.js` Figma disisipkan di browser. Tidak ada file proyek yang diubah.
- **Uji coba (Tahap 2), 22 November 2026:** PB-01 Desktop (`46:2`), PB-01 Mobile (`45:2`), dan OT-01 Desktop (`44:2`) terkirim tanpa klik manual, masing-masing ±35 detik, ke halaman yang kini bernama Orang Tua.
- **Kuota:** baca Figma MCP (paket Starter, seat View, 20 panggilan per bulan) sudah habis. `generate_figma_design` tidak terkena kuota. Penataan, penggantian nama, dan pemeriksaan frame (`use_figma`, `get_metadata`, `get_screenshot`) tidak dipakai.
- **Hasil, 22 November 2026:** 123 capture terkirim (HTTP 200), tidak ada yang gagal atau dilewati. Orang Tua 48 frame (termasuk 2 frame uji coba PB-01), Tutor 41, Admin 34. Selain itu, di halaman Orang Tua masih ada frame uji coba lama `44:2` (OT-01 Desktop, duplikat `69:2`) dan dua frame landing awal `6:2` dan `7:2`. Ketiganya perlu dihapus manual.
- **Perlu dirapikan manual di Figma:** frame tersusun dalam satu baris horizontal dan namanya bisa mengikuti judul halaman. Efek dekoratif (stabilo, kertas bergaris, lingkaran coretan) bisa tergambar sebagai gambar/vektor dan tidak persis seperti di browser. Angka berfont "Angka EduLink" dan `font-stretch` bisa jatuh ke font pengganti.
