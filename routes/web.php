<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

/*
| Placeholder pages until the real features are built.
*/
Route::view('/tutor', 'placeholder', ['title' => 'Cari tutor', 'showsSearchFilters' => true])->name('tutor.index');
Route::view('/masuk', 'placeholder', ['title' => 'Masuk'])->name('login');
Route::view('/daftar', 'placeholder', ['title' => 'Daftar akun wali'])->name('register');
Route::view('/daftar-tutor', 'placeholder', ['title' => 'Daftar jadi tutor'])->name('tutor.register');
Route::view('/kebijakan-privasi', 'placeholder', ['title' => 'Kebijakan privasi'])->name('privacy');
