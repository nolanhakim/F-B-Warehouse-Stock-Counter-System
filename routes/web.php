<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlertsController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\OpnameController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WasteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

if (!function_exists('page_route')) {
    function page_route($slug, $label, $roles = ['staff', 'admin', 'super'])
    {
        Route::get('/'.$slug, function () use ($slug, $label, $roles) {
            $role = session('role', 'staff');
            abort_unless(in_array($role, $roles), 403);
            return view("{$role}.{$slug}", ['active' => $slug, 'pageTitle' => $label]);
        })->name($slug);
    }
}

if (!function_exists('role_name')) {
    function role_name($key)
    {
        return ['admin' => 'Admin Gudang', 'staff' => 'Karyawan', 'super' => 'Super Admin'][$key] ?? 'Karyawan';
    }
}

// ===== AUTH =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'doLogin'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'doRegister'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

// ===== DASHBOARD per role =====
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ===== Halaman utama (folder per role) =====
Route::get('/master', [ItemController::class, 'index'])->name('master');
Route::post('/master', [ItemController::class, 'store'])->name('items.store');
Route::put('/master/{item}', [ItemController::class, 'update'])->name('items.update');
Route::delete('/master/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
Route::get('/mutasi', [MutationController::class, 'index'])->name('mutasi');
Route::post('/mutasi', [MutationController::class, 'store'])->name('mutasi.store');
Route::get('/mutasi/export', [MutationController::class, 'export'])->name('mutasi.export');
Route::get('/opname', [OpnameController::class, 'index'])->name('opname');
Route::get('/opname/data', [OpnameController::class, 'data'])->name('opname.data');
Route::post('/opname/start', [OpnameController::class, 'start'])->name('opname.start');
Route::post('/opname/draft', [OpnameController::class, 'draft'])->name('opname.draft');
Route::post('/opname/finish', [OpnameController::class, 'finish'])->name('opname.finish');
Route::post('/opname/cancel', [OpnameController::class, 'cancel'])->name('opname.cancel');
Route::get('/kartu-stok', [KartuStokController::class, 'index'])->name('kartu-stok');
Route::get('/kartu-stok/export', [KartuStokController::class, 'export'])->name('kartu-stok.export');
Route::get('/waste', [WasteController::class, 'index'])->name('waste');
Route::get('/waste/export', [WasteController::class, 'export'])->name('waste.export');
Route::post('/waste/{mutation}/approve', [WasteController::class, 'approve'])->name('waste.approve');
Route::post('/waste/{mutation}/reject', [WasteController::class, 'reject'])->name('waste.reject');
Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');
page_route('cari', 'Cari Stok / Rak');

Route::get('/logs', [LogController::class, 'index'])->name('logs.dynamic');

Route::get('/users', [UserController::class, 'index'])->name('users.dynamic');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');