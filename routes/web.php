<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('reimbursements', ReimbursementController::class)->except(['destroy']);
    Route::match(['POST', 'PUT'], '/reimbursements/{reimbursement}/submit', [ReimbursementController::class, 'submit'])
        ->name('reimbursements.submit');
    Route::get('/reimbursements/{reimbursement}/attachment', [ReimbursementController::class, 'attachment'])
        ->name('reimbursements.attachment');

    // Transaksi Kas Kantor (Pemasukan, Pengeluaran, Pembatalan)
    Route::resource('transactions', TransactionController::class)->except(['edit', 'update', 'destroy']);
    Route::get('/transactions/{transaction}/attachment', [TransactionController::class, 'attachment'])
        ->name('transactions.attachment');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])
        ->name('transactions.cancel');

    // Pengelolaan Anggaran (Budget Monitoring & Control)
    Route::resource('budgets', BudgetController::class)->except(['destroy']);
    Route::post('/budgets/{budget}/close', [BudgetController::class, 'close'])
        ->name('budgets.close');

    // Laporan Keuangan & Export (Finance, Director, Admin, Manager)
    Route::middleware('role:finance,director,admin,manager')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
        Route::get('/print', [ReportController::class, 'print'])->name('print');
    });

    // Antrean & Aksi Persetujuan Biaya (Manager, Finance, Director)
    Route::middleware('role:manager,finance,director')->prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{reimbursement}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{reimbursement}/reject', [ApprovalController::class, 'reject'])->name('reject');
        Route::post('/{reimbursement}/return', [ApprovalController::class, 'returnForRevision'])->name('return');
        Route::post('/{reimbursement}/pay', [ApprovalController::class, 'pay'])->name('pay');
    });

    Route::middleware('role:finance')->prefix('finance')->name('finance.')->group(function () {
        Route::view('/workspace', 'finance.workspace')->name('workspace');
    });

    Route::middleware('role:manager,director')->prefix('management')->name('management.')->group(function () {
        Route::view('/approvals', 'management.approvals')->name('approvals');
    });

    Route::middleware('role:director')->prefix('director')->name('director.')->group(function () {
        Route::view('/overview', 'director.overview')->name('overview');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/settings', 'admin.settings')->name('settings');
    });
});
