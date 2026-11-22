# Arsitektur EduLink Tutor v0.9 Beta

Dokumen ini menggambarkan arsitektur aplikasi seperti yang dibangun di versi Beta: struktur data, status utama, komponen, dan perbedaannya dengan rancangan arsitektur target. Diagram memakai Mermaid sehingga tampil langsung di GitHub. Rincian setiap kolom ada di [daftar-tabel.md](daftar-tabel.md).

Aplikasi berupa satu aplikasi web Laravel (monolit modular) dengan database MySQL. Logika bisnis ada di kelas layanan (`app/Services`), status di enum (`app/Enums`), dan tugas berkala dijalankan oleh scheduler Laravel.

## 1. ERD

Kunci utama berupa string berawalan (mis. `AK0001`, `ORD-2026-000045`, `SES0001`). Kolom bertanda `tambahan` tidak ada di ERD awal; alasan setiap tambahan ada di daftar-tabel.md. Agar diagram tetap terbaca, hanya kunci dan kolom penting yang ditampilkan.

```mermaid
erDiagram
    akun_pengguna ||--o{ siswa : "wali dari"
    akun_pengguna |o--o| tutor : "masuk sebagai"
    tutor ||--|| profil_tutor : "punya"
    tutor ||--o{ tutor_mata_pelajaran : "mengajar"
    mata_pelajaran ||--o{ tutor_mata_pelajaran : "diajar oleh"
    tutor ||--o{ dokumen_verifikasi : "mengunggah"
    tutor ||--o{ jadwal_ketersediaan : "menyediakan"
    siswa ||--o{ pesanan : "dipesankan"
    tutor ||--o{ pesanan : "menerima"
    mata_pelajaran ||--o{ pesanan : "untuk"
    jadwal_ketersediaan ||--o{ pesanan : "memakai slot"
    pesanan ||--o{ sesi_pembelajaran : "terdiri dari"
    pesanan ||--o{ pembayaran : "dibayar lewat"
    pesanan ||--o| komisi_biaya_layanan : "dikenai"
    pesanan ||--o| rating_ulasan : "diulas"
    pesanan ||--o{ keluhan : "dikeluhkan"
    akun_pengguna ||--o{ keluhan : "mengajukan"
    sesi_pembelajaran |o--o{ keluhan : "disanggah lewat"
    tutor ||--o{ pencairan_dana_tutor : "mengajukan"
    pesanan ||--o{ pengembalian_dana : "dikembalikan lewat"
    pengembalian_dana |o--o{ sesi_pembelajaran : "membayar kembali"
    akun_pengguna |o--o{ pengembalian_dana : "memproses"
    sesi_pembelajaran ||--o{ permintaan_jadwal_ulang : "dijadwalkan ulang"
    akun_pengguna ||--o{ permintaan_jadwal_ulang : "mengajukan"
    pesanan ||--o{ riwayat_status_pesanan : "linimasa"
    tutor ||--o{ riwayat_verifikasi_tutor : "linimasa"
    akun_pengguna ||--o{ riwayat_status_akun : "linimasa"

    akun_pengguna {
        string id_akun PK
        string peran
        string kontak UK
        string kredensial
        string nama "tambahan"
        string no_hp "tambahan"
        boolean aktif "tambahan"
    }
    siswa {
        string id_siswa PK
        string id_akun FK
        string nama
        string jenjang
        string kontak_wali
    }
    tutor {
        string id_tutor PK
        string id_akun FK "null, unik"
        string nama
        string status "internal atau eksternal"
        string kontak
        string status_verifikasi "tambahan"
        string rekening_nomor "tambahan, terenkripsi"
    }
    profil_tutor {
        string id_profil PK
        string id_tutor FK "unik"
        text kompetensi
        text pengalaman
        decimal tarif
        string lokasi
        string foto
        boolean melayani_online "tambahan"
        boolean melayani_kunjungan "tambahan"
    }
    mata_pelajaran {
        string kode_mapel PK
        string nama
        string jenjang
    }
    tutor_mata_pelajaran {
        string id_tutor PK, FK
        string kode_mapel PK, FK
    }
    dokumen_verifikasi {
        string id_dokumen PK
        string id_tutor FK
        string jenis
        string berkas
        string status_verifikasi
    }
    jadwal_ketersediaan {
        string id_jadwal PK
        string id_tutor FK "unik bersama hari, jam"
        string hari
        time jam
        string status
    }
    pesanan {
        string id_pesanan PK
        string id_siswa FK
        string id_tutor FK
        string kode_mapel FK
        string id_jadwal FK
        int durasi
        string mode
        string status
        date tanggal_mulai "tambahan"
        int jumlah_pertemuan "tambahan"
        decimal total_biaya "tambahan"
        datetime ditahan_hingga "tambahan"
        string tautan_meeting "tambahan"
    }
    sesi_pembelajaran {
        string id_sesi PK
        string id_pesanan FK
        datetime waktu
        string kehadiran
        text catatan
        string tautan_meeting "tambahan"
        string status_konfirmasi "tambahan"
        datetime dicatat_pada "tambahan"
        datetime dikonfirmasi_pada "tambahan"
        string dikonfirmasi_oleh "tambahan"
        string id_pengembalian FK "tambahan"
    }
    pembayaran {
        string id_pembayaran PK
        string id_pesanan FK
        string metode
        decimal jumlah
        string status
    }
    komisi_biaya_layanan {
        string id_komisi PK
        string id_pesanan FK "unik"
        decimal persentase
        decimal nominal
    }
    rating_ulasan {
        string id_ulasan PK
        string id_pesanan FK "unik"
        int nilai
        text komentar
        boolean ditampilkan "tambahan"
    }
    keluhan {
        string id_keluhan PK
        string id_pesanan FK
        string jenis
        string status
        string id_akun FK "tambahan"
        string id_sesi FK "tambahan"
    }
    pencairan_dana_tutor {
        string id_pencairan PK
        string id_tutor FK
        string periode
        decimal nominal
        string status
    }
    pengembalian_dana {
        string id_pengembalian PK
        string id_pesanan FK
        decimal nominal
        string status
        string diproses_oleh FK
    }
    permintaan_jadwal_ulang {
        int id PK
        string id_sesi FK
        string id_akun FK
        datetime waktu_baru
        string status
    }
    riwayat_status_pesanan {
        int id PK
        string id_pesanan FK
        string status
        string id_akun FK
    }
    riwayat_verifikasi_tutor {
        int id PK
        string id_tutor FK
        string status
        string id_akun FK
    }
    riwayat_status_akun {
        int id PK
        string id_akun FK
        boolean aktif
        string oleh FK
    }
    pengaturan {
        string kunci PK
        string nilai
    }
```

### Asal tabel

| Asal | Tabel |
|---|---|
| ERD awal (15 tabel) | akun_pengguna, siswa, tutor, profil_tutor, mata_pelajaran, tutor_mata_pelajaran, dokumen_verifikasi, jadwal_ketersediaan, pesanan, sesi_pembelajaran, pembayaran, komisi_biaya_layanan, rating_ulasan, keluhan, pencairan_dana_tutor |
| Tambahan aplikasi | pengaturan, riwayat_status_pesanan, riwayat_verifikasi_tutor, riwayat_status_akun, permintaan_jadwal_ulang, pengembalian_dana, notifications, password_reset_tokens, sessions |
| Bawaan Laravel (bukan data bisnis) | cache, cache_locks, jobs, job_batches, failed_jobs, migrations |

Catatan:
- `notifications` memakai relasi polimorfik bawaan Laravel (`notifiable_id` berisi `id_akun`), tanpa foreign key, sehingga tidak digambar di ERD.
- Tabel ERD awal juga mendapat kolom tambahan (ditandai `tambahan` di diagram).

## 2. Status pesanan

```mermaid
stateDiagram-v2
    direction LR
    [*] --> menunggu_konfirmasi_tutor : orang tua memesan<br/>(slot ditahan 24 jam)
    menunggu_konfirmasi_tutor --> menunggu_pembayaran : tutor menerima<br/>(24 jam untuk membayar)
    menunggu_konfirmasi_tutor --> ditolak : tutor menolak<br/>(slot dilepas)
    menunggu_konfirmasi_tutor --> batal : orang tua atau admin membatalkan,<br/>kedaluwarsa 24 jam,<br/>atau akun dinonaktifkan
    menunggu_pembayaran --> dibayar : webhook pembayaran berhasil<br/>(slot terisi, sesi dibuat, komisi dicatat)
    menunggu_pembayaran --> batal : orang tua, tutor, atau admin membatalkan,<br/>kedaluwarsa 24 jam,<br/>atau akun dinonaktifkan
    dibayar --> selesai : semua sesi tuntas<br/>(dikonfirmasi, otomatis, atau tidak sah)
    dibayar --> batal : admin membatalkan
    ditolak --> [*]
    selesai --> [*]
    batal --> [*]

    note right of batal
        Dibatalkan admin setelah dibayar:
        biaya sesi yang belum dicatat masuk
        pengembalian_dana (perlu_diproses -> selesai).
        Sesi yang sudah dicatat tetap
        melalui konfirmasi.
    end note
```

Slot jadwal mengikuti status pesanan: `tersedia` → `ditahan` (pesanan dibuat) → `terisi` (dibayar) → kembali `tersedia` saat pesanan ditolak, batal, atau selesai. Pembayaran sendiri berstatus `pending` → `berhasil` atau `gagal`; orang tua boleh mencoba lagi setelah gagal selama batas waktu belum lewat.

## 3. Status konfirmasi sesi

```mermaid
stateDiagram-v2
    direction LR
    [*] --> belum_dicatat : pesanan dibayar,<br/>sesi mingguan dibuat
    belum_dicatat --> belum_dicatat : jadwal ulang disetujui<br/>(maks. 2 kali per sesi)
    belum_dicatat --> menunggu_konfirmasi : tutor mencatat Terlaksana<br/>atau Siswa tidak hadir
    belum_dicatat --> dikembalikan : admin membatalkan pesanan
    menunggu_konfirmasi --> dikonfirmasi : orang tua mengonfirmasi
    menunggu_konfirmasi --> dikonfirmasi_otomatis : 48 jam tanpa jawaban<br/>(perintah konfirmasi-otomatis)
    menunggu_konfirmasi --> disanggah : orang tua menyanggah<br/>(alasan wajib, keluhan dibuat)
    disanggah --> dikonfirmasi : admin menyatakan sah
    disanggah --> tidak_sah : admin menyatakan tidak sah<br/>(biaya sesi dikembalikan)
    dikonfirmasi --> [*]
    dikonfirmasi_otomatis --> [*]
    tidak_sah --> [*]
    dikembalikan --> [*]
```

- `belum_dicatat` dan `dikembalikan` bukan nilai kolom: `status_konfirmasi` masih kosong, dan sesi yang dikembalikan ditandai dengan `id_pengembalian`.
- Uang tutor per sesi: bagian tutor (nilai sesi − komisi) menjadi **saldo tersedia** saat `dikonfirmasi` atau `dikonfirmasi_otomatis`, dan berstatus **dana ditahan** sebelum itu. Sesi `tidak_sah` dan yang dikembalikan tidak menjadi hak tutor.

## 4. Status verifikasi tutor

```mermaid
stateDiagram-v2
    direction LR
    [*] --> draf : daftar di /daftar-tutor (eksternal)<br/>atau aktivasi akun (internal)
    draf --> menunggu : tutor mengirim formulir<br/>(profil, mapel, jadwal, dokumen)
    menunggu --> terverifikasi : admin menyetujui<br/>(dokumen valid, tampil di pencarian)
    menunggu --> perlu_perbaikan : admin meminta perbaikan<br/>(bagian dan dokumen ditandai)
    menunggu --> ditolak : admin menolak dengan alasan
    perlu_perbaikan --> menunggu : tutor memperbaiki dan mengirim ulang
    terverifikasi --> [*]
    ditolak --> [*]
```

Setiap langkah dicatat di `riwayat_verifikasi_tutor`. Setiap dokumen punya status sendiri (`menunggu`, `valid`, `ditolak`). Mata pelajaran tutor terverifikasi hanya bisa diubah admin, dengan alasan.

## 5. Diagram komponen

```mermaid
flowchart LR
    subgraph Pengguna
        ortu([Orang tua])
        tutor([Tutor])
        admin([Admin])
        tamu([Pengunjung])
    end

    subgraph App[Aplikasi web EduLink Tutor - Laravel]
        direction TB
        cari[Pencarian tutor]
        daftar[Pendaftaran tutor dan aktivasi internal]
        verif[Verifikasi tutor]
        pesan[Pemesanan dan penahanan slot]
        bayar[Pembayaran simulasi + webhook HMAC]
        sesi[Sesi - tautan meeting, catat, konfirmasi, jadwal ulang]
        ulas[Ulasan dan moderasi]
        keluh[Keluhan dan sanggahan sesi]
        uang[Saldo, dana ditahan, pencairan, pengembalian]
        notif[Notifikasi dalam aplikasi + email]
        lapor[Laporan - ringkasan, CSV, cetak]
        akun[Akun, peran, persetujuan kebijakan]
    end

    subgraph Terjadwal[Perintah terjadwal edulink lewat scheduler Laravel]
        lepas[lepas-penahanan<br/>setiap menit]
        ulang[jadwal-ulang-kedaluwarsa<br/>setiap menit]
        konf[konfirmasi-otomatis<br/>setiap jam]
        ingat[pengingat-sesi<br/>setiap hari 07.00]
    end

    db[(MySQL)]
    storage[(Penyimpanan privat<br/>dokumen tutor)]

    subgraph Luar[Layanan luar yang disimulasikan atau diganti di Beta]
        gateway[[Payment gateway<br/>→ halaman gateway tiruan]]
        transfer[[Transfer dana bank<br/>→ admin mencatat manual]]
        video[[Layanan video meeting<br/>→ tautan Meet/Zoom dari tutor]]
        wa[[Notifikasi WhatsApp<br/>→ tautan wa.me]]
        mail[[Email<br/>→ mailer log]]
    end

    tamu --> cari & daftar
    ortu --> cari & pesan & bayar & sesi & ulas & keluh
    tutor --> daftar & pesan & sesi & uang & keluh
    admin --> verif & keluh & uang & lapor & ulas & akun

    bayar <--> gateway
    uang -.-> transfer
    sesi -.-> video
    sesi -.-> wa
    notif -.-> mail

    App --> db
    daftar & verif --> storage
    lepas & ulang & konf & ingat --> db
    konf & ingat --> notif
```

Aturan alur yang dipusatkan di satu tempat:

| Kelas layanan | Tanggung jawab |
|---|---|
| `AlurPesanan` | Semua perubahan status pesanan (dengan kunci baris), penahanan dan pelepasan slot, pembuatan sesi, linimasa |
| `PemrosesPembayaran` | Memulai pembayaran dan memproses webhook (tanda tangan, idempoten, pembayaran terlambat) |
| `KonfirmasiSesi` | Konfirmasi, sanggahan, konfirmasi otomatis 48 jam, putusan admin |
| `PembagianSesi`, `SaldoTutor`, `LaporanRingkasan` | Satu rumus uang: nilai, komisi, dan bagian tutor per sesi; saldo tersedia, dana ditahan, komisi diakui |
| `VerifikasiTutor`, `PendaftaranTutor`, `AktivasiTutor` | Alur pendaftaran dan keputusan admin |
| `Pemberitahu` | Semua notifikasi, dikirim setelah transaksi database berhasil |

## 6. Perbedaan dengan rancangan arsitektur target

Rancangan target menempatkan EduLink Tutor sebagai salah satu layanan dalam ekosistem EduLink. Versi Beta sengaja dibangun sebagai satu aplikasi mandiri supaya alur bisnis inti bisa diuji dan didemokan lebih dulu.

| Komponen target | Di versi Beta | Alasan |
|---|---|---|
| Integrasi EduManage (data siswa, pengajar internal, akademik) | Tidak diimplementasikan. Pengajar internal di-seed sebagai data awal dan mengaktifkan akun sendiri di `/aktivasi-tutor`. | EduManage belum menyediakan antarmuka yang bisa dipakai; dengan data awal, alur aktivasi tetap bisa ditunjukkan. |
| SSO antar-aplikasi EduLink | Tidak diimplementasikan. Login email dan kata sandi per aplikasi, dengan peran ortu, tutor, admin. | Butuh penyedia identitas bersama yang belum ada; login lokal cukup untuk menguji hak akses per peran. |
| API Gateway | Tidak diimplementasikan. Aplikasi melayani halaman web langsung; satu-satunya endpoint untuk sistem luar adalah webhook pembayaran. | Belum ada layanan lain yang memanggil EduLink Tutor, sehingga gateway belum memberi nilai. |
| Payment gateway | Disimulasikan: halaman gateway tiruan mengirim hasil ke webhook bertanda tangan HMAC, hanya saat mode demo aktif. | Integrasi sungguhan butuh akun merchant dan uang nyata. Kontrak webhook-nya sudah sama, jadi penggantiannya terbatas pada pengirim notifikasi. |
| Transfer dana (pencairan tutor, pengembalian orang tua) | Admin mencatat bahwa transfer sudah dilakukan. | Tidak ada integrasi bank; pencatatan tetap menjaga saldo dan jejak audit. |
| Layanan video meeting | Diganti tautan Google Meet/Zoom yang dibagikan tutor per pesanan atau per sesi. | Membangun atau menyewa layanan video di luar cakupan Beta; tutor sudah terbiasa dengan layanan yang ada. |
| Notifikasi WhatsApp | Diganti tombol tautan `wa.me` untuk pesanan yang sudah dibayar; notifikasi lain lewat aplikasi dan email. | WhatsApp Business API berbayar dan butuh verifikasi bisnis. |
