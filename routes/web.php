<?php

use App\Http\Controllers\Auth\DaftarController;
use App\Http\Controllers\Auth\SesiController;
use App\Http\Controllers\BerandaController;
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
use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

/*
| Sign-in and guardian sign-up. Signed-in accounts are sent to their own home page.
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [SesiController::class, 'create'])->name('login');
    Route::post('/masuk', [SesiController::class, 'store'])->name('login.store');

    Route::get('/daftar', [DaftarController::class, 'create'])->name('register');
    Route::post('/daftar', [DaftarController::class, 'store'])->name('register.store');
});

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

    Route::view('/admin', 'admin.beranda')->middleware('peran:admin')->name('admin.beranda');
});

/*
| Placeholder pages until the real features are built.
*/
Route::view('/daftar-tutor', 'placeholder', [
    'title' => 'Daftar jadi tutor',
    'message' => 'Pendaftaran tutor segera hadir. Kami sedang menyiapkan alur unggah berkas dan pemeriksaannya.',
])->name('tutor.register');
Route::view('/kebijakan-privasi', 'placeholder', ['title' => 'Kebijakan privasi'])->name('privacy');

/*
| Stand-ins for outside services that do not exist yet. Never registered in production.
*/
if (! app()->isProduction()) {
    Route::post('/webhooks/payment', WebhookPembayaranController::class)->name('webhooks.payment');
}
