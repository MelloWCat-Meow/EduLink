# Daftar tabel EduLink Tutor

Disusun dari skema database setelah `php artisan migrate:fresh` (21 November 2026). Kolom ditandai **ERD** kalau berasal dari ERD laporan, atau **tambahan** kalau ditambahkan untuk kebutuhan aplikasi, dengan alasan singkatnya. Kolom `created_at` dan `updated_at` (cap waktu baris) ada di hampir semua tabel dan selalu berstatus tambahan; tidak diulang di setiap tabel.

Ringkasan: 15 tabel dari ERD, 9 tabel tambahan aplikasi (dua di antaranya, notifications dan sessions, memakai struktur bawaan Laravel yang disesuaikan), dan 6 tabel bawaan Laravel tanpa data bisnis.

---

## Tabel dari ERD

### akun_pengguna
Akun untuk masuk (orang tua, tutor, admin).

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_akun | varchar(10), PK | ERD | Format AK0001. |
| peran | varchar(20) | ERD | ortu, tutor, admin (siswa dicadangkan). |
| kontak | varchar(50), unik | ERD | Email untuk masuk. |
| kredensial | varchar(255) | ERD | Kata sandi dalam bentuk hash. |
| nama | varchar(100) | tambahan | Nama tampilan untuk menyapa pengguna. |
| no_hp | varchar(20), null | tambahan | Nomor HP orang tua untuk tombol WhatsApp dari tutor, hanya untuk pesanan yang sudah dibayar. Null untuk akun lama. |
| persetujuan_pada | timestamp, null | tambahan | Waktu menyetujui Syarat dan Ketentuan serta Kebijakan Privasi (bukti persetujuan sesuai UU PDP). |
| versi_kebijakan | varchar(10), null | tambahan | Versi dokumen yang disetujui, mis. 2026-11. |
| aktif | boolean | tambahan | Admin dapat menonaktifkan akun orang tua atau tutor. |
| dinonaktifkan_pada | datetime, null | tambahan | Kapan akun dinonaktifkan. |
| alasan_nonaktif | varchar(255), null | tambahan | Alasan yang wajib diisi admin. |
| remember_token | varchar(100), null | tambahan | Fitur "ingat saya" dari Laravel. |

### siswa
Anak yang didaftarkan orang tua.

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_siswa | varchar(10), PK | ERD | Format S0001. |
| id_akun | varchar(10), FK | ERD | Akun orang tua. |
| nama | varchar(100) | ERD | |
| jenjang | varchar(20) | ERD | SD, SMP, SMA. |
| kontak_wali | varchar(50) | ERD | Email wali. |

### tutor

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_tutor | varchar(10), PK | ERD | Format T0001. |
| id_akun | varchar(10), FK, null, unik | ERD | Kosong untuk tutor internal yang belum aktivasi. |
| nama | varchar(100) | ERD | |
| status | varchar(20) | ERD | internal atau eksternal. |
| kontak | varchar(50) | ERD | Email tutor; dipakai untuk aktivasi tutor internal. |
| no_hp | varchar(20), null | tambahan | Kolom kontak menyimpan email, jadi nomor HP perlu kolom sendiri (WhatsApp ke orang tua). |
| status_verifikasi | varchar(20) | tambahan | draf, menunggu, perlu_perbaikan, ditolak, terverifikasi. Alur pendaftaran butuh status di tingkat tutor, bukan hanya per dokumen. |
| catatan_verifikasi | text, null | tambahan | Catatan admin yang dibaca tutor. |
| bagian_perbaikan | json, null | tambahan | Bagian formulir yang diminta diperbaiki beserta catatannya. |
| diajukan_pada | datetime, null | tambahan | Urutan antrean verifikasi (paling lama di atas). |
| rekening_bank | varchar(50), null | tambahan | Rekening pencairan dana tutor. |
| rekening_nomor | text, null | tambahan | Disimpan terenkripsi; tampil tersamar (••••4417) kecuali di halaman pencairan admin. |
| rekening_atas_nama | varchar(100), null | tambahan | Nama pemilik rekening. |

### profil_tutor

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_profil | varchar(10), PK | ERD | Format PR0001. |
| id_tutor | varchar(10), FK, unik | ERD | |
| kompetensi | text | ERD | |
| pengalaman | text | ERD | |
| tarif | decimal(12,2), null | ERD | Tarif per jam. Dibuat boleh kosong karena profil tutor internal dibuat dari data perusahaan sebelum tutor mengisi tarif. |
| lokasi | varchar(50), null | ERD | Kecamatan di Kota Jambi. Boleh kosong dengan alasan yang sama. |
| foto | varchar(255), null | ERD | |
| riwayat_pendidikan | json, null | tambahan | Pendidikan terakhir, institusi, dan jurusan; formulir memintanya terpisah dari kompetensi. |
| melayani_online | boolean | tambahan | Supaya orang tua tidak memesan mode yang tidak dilayani tutor. |
| melayani_kunjungan | boolean | tambahan | Sama seperti di atas, untuk kunjungan rumah. |

### mata_pelajaran

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| kode_mapel | varchar(10), PK | ERD | Mis. MTK-SMP. |
| nama | varchar(50) | ERD | |
| jenjang | varchar(3) | ERD | |

### tutor_mata_pelajaran
Relasi banyak-ke-banyak tutor dan mata pelajaran.

| Kolom | Tipe | Asal |
|---|---|---|
| id_tutor | varchar(10), PK, FK | ERD |
| kode_mapel | varchar(10), PK, FK | ERD |

### dokumen_verifikasi

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_dokumen | varchar(10), PK | ERD | Format DV0001. |
| id_tutor | varchar(10), FK | ERD | |
| jenis | varchar(20) | ERD | ijazah, ktp, sertifikat. |
| berkas | varchar(255) | ERD | Jalur di penyimpanan privat, nama berkas acak. |
| status_verifikasi | varchar(20) | ERD | menunggu, valid, ditolak. |
| alasan_penolakan | varchar(255), null | tambahan | Alasan admin saat dokumen ditolak. |
| diperiksa_pada | datetime, null | tambahan | Kapan dokumen diperiksa. |

### jadwal_ketersediaan

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_jadwal | varchar(10), PK | ERD | Format JD0001. |
| id_tutor | varchar(10), FK | ERD | Unik bersama hari dan jam (tidak ada slot ganda). |
| hari | varchar(10) | ERD | |
| jam | time | ERD | |
| status | varchar(20) | ERD | tersedia, ditahan, terisi, dan **nonaktif** (nilai tambahan: slot yang dihapus tutor tetapi masih dirujuk pesanan lama). |

### pesanan

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_pesanan | varchar(15), PK | ERD | Format ORD-2026-000045. |
| id_siswa | varchar(10), FK | ERD | |
| id_tutor | varchar(10), FK | ERD | |
| kode_mapel | varchar(10), FK | ERD | |
| id_jadwal | varchar(10), FK | ERD | |
| durasi | smallint | ERD | Menit per sesi. |
| mode | varchar(20) | ERD | online atau kunjungan_rumah. |
| status | varchar(30) | ERD | menunggu_konfirmasi_tutor, menunggu_pembayaran, dibayar, selesai, ditolak, batal. |
| tanggal_mulai | date | tambahan | Slot hanya "hari + jam" mingguan; sesi butuh tanggal nyata. |
| jumlah_pertemuan | tinyint | tambahan | Banyaknya sesi mingguan yang dibuat. |
| tarif_per_jam | decimal(12,2) | tambahan | Tarif saat memesan, supaya perubahan tarif tutor tidak mengubah pesanan lama. |
| total_biaya | decimal(12,2) | tambahan | tarif × durasi/60 × jumlah_pertemuan. |
| ditahan_hingga | datetime, null | tambahan | Batas waktu tutor menjawab atau orang tua membayar (24 jam). |

### sesi_pembelajaran

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_sesi | varchar(10), PK | ERD | Format SES0001. |
| id_pesanan | varchar(15), FK | ERD | |
| waktu | datetime | ERD | Berubah kalau jadwal ulang disetujui. |
| kehadiran | varchar(20), null | ERD | hadir atau tidak_hadir. |
| catatan | text, null | ERD | Catatan hasil sesi dari tutor. |
| tautan_meeting | varchar(255), null | tambahan | Tautan ruang meeting untuk sesi online (tiruan). |
| pengingat_dikirim_pada | datetime, null | tambahan | Supaya pengingat H-1 tidak terkirim dua kali. |

### pembayaran

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_pembayaran | varchar(10), PK | ERD | Format PAY0001. |
| id_pesanan | varchar(15), FK | ERD | Satu pesanan bisa punya beberapa percobaan bayar. |
| metode | varchar(20) | ERD | virtual_account, qris, e_wallet. |
| jumlah | decimal(12,2) | ERD | |
| status | varchar(20) | ERD | pending, berhasil, gagal. |
| diproses_pada | datetime, null | tambahan | Kapan gateway mengirim hasil akhir; dasar laporan transaksi per periode. |

### komisi_biaya_layanan

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_komisi | varchar(10), PK | ERD | Format KM0001. |
| id_pesanan | varchar(15), FK, unik | ERD | |
| persentase | decimal(5,2) | ERD | Persentase yang berlaku saat pesanan dibayar. |
| nominal | decimal(12,2) | ERD | Komisi yang benar-benar diambil. Diturunkan ke porsi sesi yang terlaksana kalau pesanan dibatalkan admin setelah dibayar. |
| nominal_sebelum_pengembalian | decimal(12,2), null | tambahan | Nominal awal sebelum diturunkan karena pengembalian dana (jejak audit). |

### rating_ulasan

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_ulasan | varchar(10), PK | ERD | Format UL0001. |
| id_pesanan | varchar(15), FK, unik | ERD | Satu ulasan per pesanan. |
| nilai | tinyint | ERD | 1 sampai 5. |
| komentar | text, null | ERD | |
| ditampilkan | boolean | tambahan | Moderasi admin; ulasan tersembunyi tidak dihitung dalam rating. |
| alasan_moderasi | varchar(255), null | tambahan | Alasan admin menyembunyikan atau menampilkan. |

### keluhan

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_keluhan | varchar(10), PK | ERD | Format KL0001. |
| id_pesanan | varchar(15), FK | ERD | |
| jenis | varchar(30) | ERD | |
| status | varchar(20) | ERD | baru, diproses, selesai. |
| id_akun | varchar(10), FK | tambahan | Pengaju (orang tua atau tutor), supaya tanggapan sampai ke orang yang benar. |
| deskripsi | text | tambahan | Cerita masalahnya. |
| tanggapan_admin | text, null | tambahan | Jawaban admin yang dibaca pengaju. |

### pencairan_dana_tutor

| Kolom | Tipe | Asal | Keterangan |
|---|---|---|---|
| id_pencairan | varchar(10), PK | ERD | Format PC0001. |
| id_tutor | varchar(10), FK | ERD | |
| periode | char(7) | ERD | YYYY-MM. |
| nominal | decimal(12,2) | ERD | Maksimal sebesar saldo tersedia. |
| status | varchar(20) | ERD | menunggu, disetujui, dibayar. |

---

## Tabel tambahan aplikasi

| Tabel | Kolom | Alasan |
|---|---|---|
| **pengaturan** | kunci (PK), nilai | Pengaturan platform yang bisa diubah admin, mis. persentase komisi (default 15%). |
| **riwayat_status_pesanan** | id, id_pesanan, status, catatan, id_akun, dicatat_pada | Linimasa setiap perubahan status pesanan: siapa, kapan, dan alasannya (tolak, batal, jadwal ulang, pengembalian). Karena itu tidak perlu kolom alasan penolakan di tabel pesanan. |
| **riwayat_verifikasi_tutor** | id, id_tutor, status, catatan, bagian (json), id_akun, dicatat_pada | Setiap langkah verifikasi tutor (dikirim, disetujui, minta perbaikan, ditolak) dan perubahan mata pelajaran oleh admin, dengan pelaku dan catatannya. |
| **riwayat_status_akun** | id, id_akun, aktif, alasan, oleh, dicatat_pada | Setiap kali admin menonaktifkan atau mengaktifkan akun, dengan alasan. |
| **permintaan_jadwal_ulang** | id, id_sesi, id_akun (pengaju), waktu_lama, waktu_baru, alasan, status (menunggu/disetujui/ditolak/kedaluwarsa), dijawab_pada, alasan_penolakan | Penjadwalan ulang satu sesi. Pihak lain dalam pesanan menyetujui atau menolak; baris yang disetujui juga menghitung batas dua kali per sesi. |
| **pengembalian_dana** | id_pengembalian (PK, RF0001), id_pesanan (unik), nominal, sesi_terlaksana, status (perlu_diproses/selesai), catatan, diproses_oleh, diproses_pada | Dana yang dikembalikan saat admin membatalkan pesanan yang sudah dibayar: total × sesi belum terlaksana ÷ jumlah pertemuan. sesi_terlaksana disimpan supaya rinciannya tetap bisa dijelaskan. |
| **notifications** | id (uuid), type, notifiable_type, notifiable_id (varchar 10), data, read_at | Notifikasi di dalam aplikasi (struktur bawaan Laravel); notifiable_id berupa teks karena ID akun berbentuk AK0001. |
| **password_reset_tokens** | kontak (PK), token, created_at | Token "Lupa kata sandi" (hash, berlaku 60 menit, sekali pakai), dengan kunci kontak supaya cocok dengan akun_pengguna. |
| **sessions** | id, user_id (varchar 10), ip_address, user_agent, payload, last_activity | Sesi masuk (struktur bawaan Laravel); user_id berupa teks karena ID akun berbentuk AK0001. Dipakai juga untuk memutus sesi lain saat kata sandi diganti atau akun dinonaktifkan. |

## Tabel bawaan Laravel (tidak memuat data bisnis)

cache, cache_locks, jobs, job_batches, failed_jobs, migrations.
