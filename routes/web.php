<?php

use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin dan User
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // Pratinjau dan unduh PDF (semua user login)
    Route::get('arsip/{arsip}/preview', [ArsipController::class, 'preview'])->name('arsip.preview');
    Route::get('arsip/{arsip}/download', [ArsipController::class, 'download'])->name('arsip.download');

    // Hak akses detail (edit milik sendiri, hapus admin) diatur di ArsipPolicy
    Route::resource('arsip', ArsipController::class);

    // Khusus Admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('akun', UserController::class)
            ->parameters(['akun' => 'user'])
            ->except(['show']);

        Route::resource('kategori', KategoriController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/log', [LogController::class, 'index'])->name('log.index');
    });
});