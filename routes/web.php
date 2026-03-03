<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlatshareController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/flatshares', [FlatshareController::class, 'index'])->name('flatshares.index');
    Route::get('/flatshares/create', [FlatshareController::class, 'create'])->name('flatshares.create');
    Route::post('/flatshares', [FlatshareController::class, 'store'])->name('flatshares.store');
    Route::get('/flatshares/{flatshare}', [FlatshareController::class, 'show'])->name('flatshares.show');
    Route::get('/flatshares/{flatshare}/edit', [FlatshareController::class, 'edit'])->name('flatshares.edit');
    Route::put('/flatshares/{flatshare}', [FlatshareController::class, 'update'])->name('flatshares.update');
    Route::post('/flatshares/{flatshare}/cancel', [FlatshareController::class, 'cancel'])->name('flatshares.cancel');
    Route::delete('/flatshares/{flatshare}', [FlatshareController::class, 'destroy'])->name('flatshares.destroy');
    Route::post('/flatshares/{flatshare}/invitations', [InvitationController::class, 'store'])->name('flatshares.invitations.store');
    Route::delete('/flatshares/{flatshare}/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('flatshares.invitations.destroy');
    Route::post('/flatshares/{flatshare}/categories', [CategoryController::class, 'store'])->name('flatshares.categories.store');
    Route::put('/flatshares/{flatshare}/categories/{category}', [CategoryController::class, 'update'])->name('flatshares.categories.update');
    Route::delete('/flatshares/{flatshare}/categories/{category}', [CategoryController::class, 'destroy'])->name('flatshares.categories.destroy');
    Route::get('/flatshares/{flatshare}/expenses', [ExpenseController::class, 'index'])->name('flatshares.expenses.index');
    Route::post('/flatshares/{flatshare}/expenses', [ExpenseController::class, 'store'])->name('flatshares.expenses.store');
    Route::delete('/flatshares/{flatshare}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('flatshares.expenses.destroy');
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}/refuse', [InvitationController::class, 'refuse'])->name('invitations.refuse');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
