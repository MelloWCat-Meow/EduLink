<?php

use App\Http\Controllers\Admin\KeluhanController as AdminKeluhanController;
use App\Http\Controllers\Admin\KomisiController;
use App\Http\Controllers\Admin\PencairanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\RingkasanController;
use App\Http\Controllers\Admin\UlasanController as AdminUlasanController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Auth\DaftarController;
use App\Http\Controllers\Auth\SesiController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\BerkasDokumenController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Ortu\AnakController;
use App\Http\Controllers\Ortu\BayarController;
use App\Http\Controllers\Ortu\KeluhanController;
use App\Http\Controllers\Ortu\PembayaranController;
use App\Http\Controllers\Ortu\PesananController;
use App\Http\Controllers\Ortu\PesanTutorController;
use App\Http\Controllers\Ortu\UlasanController;
use App\Http\Controllers\Simulasi\SimulasiPembayaranController;
use App\Http\Controllers\Simulasi\WebhookPembayaranController;
use App\Http\Controllers\Tutor\AktivasiController;
use App\Http\Controllers\Tutor\AreaTutorController;
use App\Http\Controllers\Tutor\DaftarTutorController;
use App\Http\Controllers\Tutor\DokumenController;
use App\Http\Controllers\Tutor\PendaftaranController;
use App\Http\Controllers\Tutor\StatusPendaftaranController;
use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

/*
| Sign-in and guardian sign-up. Signed-in accounts are sent to their own home page.
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [SesiController::class, 'create'])->name('login');
    Route::post('/masuk', [SesiController::class, 'store'])->middleware('throttle:masuk')->name('login.store');

    Route::get('/daftar', [DaftarController::class, 'create'])->name('register');
    Route::post('/daftar', [DaftarController::class, 'store'])->middleware('throttle:pendaftaran')->name('register.store');

    // External tutor sign-up, step 1 (the account). Later steps need the account.
    Route::post('/daftar-tutor', [DaftarTutorController::class, 'store'])->middleware('throttle:pendaftaran')->name('tutor.register.store');

    // Internal tutors (already teaching at the bimbel) activate the record the company holds.
    Route::get('/aktivasi-tutor', [AktivasiController::class, 'create'])->name('tutor.aktivasi');
    Route::post('/aktivasi-tutor', [AktivasiController::class, 'store'])->middleware('throttle:aktivasi')->name('tutor.aktivasi.store');
    Route::get('/aktivasi-tutor/{tutor}/kata-sandi', [AktivasiController::class, 'edit'])->name('tutor.aktivasi.sandi');
    Route::post('/aktivasi-tutor/{tutor}/kata-sandi', [AktivasiController::class, 'update'])->middleware('throttle:aktivasi')->name('tutor.aktivasi.sandi.store');
});

// Visitors get step 1; a signed-in tutor is sent on to where they left off.
Route::get('/daftar-tutor', [DaftarTutorController::class, 'create'])->name('tutor.register');

/*
| Public tutor search. Expired slot holds are released before slots are read.
*/
Route::middleware('lepas-penahanan')->group(function (): void {
    Route::get('/tutor', [TutorController::class, 'index'])->name('tutor.index');
    Route::get('/tutor/{tutor}', [TutorController::class, 'show'])->name('tutor.show');
});

Route::middleware(['auth', 'lepas-penahanan'])->group(function (): void {
    Route::post('/keluar', [SesiController::class, 'destroy'])->name('logout');

    /*
    | Parent dashboard. Policies keep every order, child and complaint to its own account.
    */
    Route::middleware('peran:ortu')->group(function (): void {
        Route::get('/beranda', BerandaController::class)->name('beranda');

        Route::get('/anak', [AnakController::class, 'index'])->name('anak.index');
        Route::post('/anak', [AnakController::class, 'store'])->name('anak.store');
        Route::get('/anak/{siswa}/ubah', [AnakController::class, 'edit'])->name('anak.edit')->can('update', 'siswa');
        Route::put('/anak/{siswa}', [AnakController::class, 'update'])->name('anak.update')->can('update', 'siswa');
        Route::delete('/anak/{siswa}', [AnakController::class, 'destroy'])->name('anak.destroy')->can('delete', 'siswa');

        Route::get('/tutor/{tutor}/pesan', [PesanTutorController::class, 'create'])->name('tutor.pesan');
        Route::post('/tutor/{tutor}/pesan', [PesanTutorController::class, 'store'])->name('tutor.pesan.store');

        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('/pesanan/{pesanan}', [PesananController::class, 'show'])->name('pesanan.show')->can('view', 'pesanan');
        Route::post('/pesanan/{pesanan}/batal', [PesananController::class, 'batal'])->name('pesanan.batal')->can('update', 'pesanan');
        Route::post('/pesanan/{pesanan}/bayar', BayarController::class)->name('pesanan.bayar')->can('update', 'pesanan');
        Route::post('/pesanan/{pesanan}/ulasan', [UlasanController::class, 'store'])->name('pesanan.ulasan')->can('update', 'pesanan');
        Route::post('/pesanan/{pesanan}/keluhan', [KeluhanController::class, 'store'])->name('pesanan.keluhan')->can('update', 'pesanan');

        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::get('/keluhan', [KeluhanController::class, 'index'])->name('keluhan.index');

        if (! app()->isProduction()) {
            Route::get('/simulasi/pembayaran/{pembayaran}', SimulasiPembayaranController::class)->name('simulasi.pembayaran')->can('view', 'pembayaran');
        }
    });

    /*
    | Admin dashboard. Only accounts with the admin role get past the "peran" middleware.
    */
    Route::middleware('peran:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', RingkasanController::class)->name('beranda');

        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{tutor}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{tutor}/setujui', [VerifikasiController::class, 'setujui'])->name('verifikasi.setujui');
        Route::post('/verifikasi/{tutor}/perbaikan', [VerifikasiController::class, 'perbaikan'])->name('verifikasi.perbaikan');
        Route::post('/verifikasi/{tutor}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');

        Route::get('/pesanan', [AdminPesananController::class, 'index'])->name('pesanan.index');
        Route::get('/pesanan/{pesanan}', [AdminPesananController::class, 'show'])->name('pesanan.show');
        Route::post('/pesanan/{pesanan}/batal', [AdminPesananController::class, 'batal'])->name('pesanan.batal');

        Route::get('/keluhan', [AdminKeluhanController::class, 'index'])->name('keluhan.index');
        Route::get('/keluhan/{keluhan}', [AdminKeluhanController::class, 'show'])->name('keluhan.show');
        Route::put('/keluhan/{keluhan}', [AdminKeluhanController::class, 'update'])->name('keluhan.update');

        Route::get('/ulasan', [AdminUlasanController::class, 'index'])->name('ulasan.index');
        Route::put('/ulasan/{ulasan}', [AdminUlasanController::class, 'update'])->name('ulasan.update');

        Route::get('/komisi', [KomisiController::class, 'edit'])->name('komisi.edit');
        Route::put('/komisi', [KomisiController::class, 'update'])->name('komisi.update');

        Route::get('/pencairan', [PencairanController::class, 'index'])->name('pencairan.index');
        Route::post('/pencairan/{pencairan}/setujui', [PencairanController::class, 'setujui'])->name('pencairan.setujui');
        Route::post('/pencairan/{pencairan}/bayar', [PencairanController::class, 'bayar'])->name('pencairan.bayar');

        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    });

    /*
    | Tutor pages. "status-tutor" lets each application status reach only its own pages
    | and sends everyone else to the page that fits them.
    */
    Route::middleware('peran:tutor')->group(function (): void {
        Route::get('/daftar-tutor/status', StatusPendaftaranController::class)->middleware('status-tutor:menunggu,ditolak')->name('tutor.status');
        Route::get('/area-tutor', AreaTutorController::class)->middleware('status-tutor:terverifikasi')->name('tutor.area');

        Route::middleware('status-tutor:draf,perlu_perbaikan')->prefix('daftar-tutor')->name('tutor.pendaftaran.')->group(function (): void {
            Route::put('/data-diri', [PendaftaranController::class, 'dataDiri'])->name('data-diri');
            Route::put('/profil', [PendaftaranController::class, 'profil'])->name('profil');
            Route::post('/dokumen', [DokumenController::class, 'store'])->name('dokumen.store');
            Route::delete('/dokumen/{dokumen}', [DokumenController::class, 'destroy'])->name('dokumen.destroy')->can('delete', 'dokumen');
            Route::put('/jadwal', [PendaftaranController::class, 'jadwal'])->name('jadwal');
            Route::post('/kirim', [PendaftaranController::class, 'kirim'])->name('kirim');
            Route::get('/{langkah}', [PendaftaranController::class, 'show'])->name('langkah');
        });
    });

    // Verification documents live on the private disk; the policy allows the owner and admins.
    Route::get('/berkas-verifikasi/{dokumen}', BerkasDokumenController::class)->name('dokumen.berkas')->can('view', 'dokumen');
});

/*
| Placeholder pages until the real features are built.
*/
Route::view('/kebijakan-privasi', 'placeholder', ['title' => 'Kebijakan privasi'])->name('privacy');

/*
| Stand-ins for outside services that do not exist yet. Never registered in production.
*/
if (! app()->isProduction()) {
    Route::post('/webhooks/payment', WebhookPembayaranController::class)->name('webhooks.payment');
}
