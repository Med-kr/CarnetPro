<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlatshareController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettlementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');

if (app()->environment(['local', 'testing'])) {
    Route::get('/mail-test', function (Request $request) {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Mail::raw('Ceci est un email de test CarnetPro.', function ($message) use ($data) {
                $message->to($data['email'])
                    ->subject('CarnetPro - Test email');
            });

            Log::info('Mail test sent successfully.', [
                'recipient_email' => $data['email'],
                'mailer' => config('mail.default'),
            ]);

            return response()->json([
                'message' => 'Email de test envoye avec succes.',
                'mailer' => config('mail.default'),
                'recipient' => $data['email'],
            ]);
        } catch (Throwable $throwable) {
            Log::error('Mail test failed.', [
                'recipient_email' => $data['email'],
                'mailer' => config('mail.default'),
                'error' => $throwable->getMessage(),
            ]);

            return response()->json([
                'message' => 'Echec de l envoi du mail de test.',
                'error' => $throwable->getMessage(),
            ], 500);
        }
    })->name('mail.test');
}

Route::middleware(['auth', 'not-banned'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/flatshares', [FlatshareController::class, 'index'])->name('flatshares.index');
    Route::get('/flatshares/create', [FlatshareController::class, 'create'])->name('flatshares.create');
    Route::post('/flatshares', [FlatshareController::class, 'store'])->name('flatshares.store');
    Route::get('/flatshares/{flatshare}', [FlatshareController::class, 'show'])->name('flatshares.show');
    Route::get('/flatshares/{flatshare}/edit', [FlatshareController::class, 'edit'])->name('flatshares.edit');
    Route::put('/flatshares/{flatshare}', [FlatshareController::class, 'update'])->name('flatshares.update');
    Route::delete('/flatshares/{flatshare}', [FlatshareController::class, 'destroy'])->name('flatshares.destroy');
    Route::post('/flatshares/{flatshare}/cancel', [FlatshareController::class, 'cancel'])->name('flatshares.cancel');

    Route::post('/flatshares/{flatshare}/invitations', [InvitationController::class, 'store'])->name('flatshares.invitations.store');
    Route::delete('/flatshares/{flatshare}/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('flatshares.invitations.destroy');
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}/refuse', [InvitationController::class, 'refuse'])->name('invitations.refuse');

    Route::post('/flatshares/{flatshare}/leave', [MembershipController::class, 'leave'])->name('flatshares.leave');
    Route::delete('/flatshares/{flatshare}/memberships/{membership}', [MembershipController::class, 'destroy'])->name('flatshares.memberships.destroy');

    Route::get('/flatshares/{flatshare}/expenses', [ExpenseController::class, 'index'])->name('flatshares.expenses.index');
    Route::post('/flatshares/{flatshare}/expenses', [ExpenseController::class, 'store'])->name('flatshares.expenses.store');
    Route::delete('/flatshares/{flatshare}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('flatshares.expenses.destroy');

    Route::post('/flatshares/{flatshare}/categories', [CategoryController::class, 'store'])->name('flatshares.categories.store');
    Route::put('/flatshares/{flatshare}/categories/{category}', [CategoryController::class, 'update'])->name('flatshares.categories.update');
    Route::delete('/flatshares/{flatshare}/categories/{category}', [CategoryController::class, 'destroy'])->name('flatshares.categories.destroy');

    Route::get('/flatshares/{flatshare}/settlements', [SettlementController::class, 'show'])->name('flatshares.settlements.show');
    Route::post('/flatshares/{flatshare}/payments', [PaymentController::class, 'store'])->name('flatshares.payments.store');

    Route::middleware('global-admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/admin/users/{user}/ban', [AdminController::class, 'ban'])->name('admin.users.ban');
        Route::post('/admin/users/{user}/unban', [AdminController::class, 'unban'])->name('admin.users.unban');
    });
});

require __DIR__.'/auth.php';
