<?php

use App\Http\Controllers\Auth\DaftarController;
use App\Http\Controllers\Auth\SesiController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\LandingController;
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

Route::middleware('auth')->group(function (): void {
    Route::post('/keluar', [SesiController::class, 'destroy'])->name('logout');

    Route::get('/beranda', BerandaController::class)->middleware('peran:ortu')->name('beranda');
    Route::view('/admin', 'admin.beranda')->middleware('peran:admin')->name('admin.beranda');
});

/*
| Placeholder pages until the real features are built.
*/
Route::view('/tutor', 'placeholder', ['title' => 'Cari tutor', 'showsSearchFilters' => true])->name('tutor.index');
Route::view('/daftar-tutor', 'placeholder', [
    'title' => 'Daftar jadi tutor',
    'message' => 'Pendaftaran tutor segera hadir. Kami sedang menyiapkan alur unggah berkas dan pemeriksaannya.',
])->name('tutor.register');
Route::view('/kebijakan-privasi', 'placeholder', ['title' => 'Kebijakan privasi'])->name('privacy');
