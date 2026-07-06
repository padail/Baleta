<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CaptainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FishDeliveryInvoiceController;
use App\Http\Controllers\MonthlyClosingController;
use App\Http\Controllers\OwnerExpenseController;
use App\Http\Controllers\ShipController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\PreventDuplicateSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', EnsureUserIsActive::class, PreventDuplicateSubmission::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('ships', ShipController::class);
    Route::resource('captains', CaptainController::class)->except(['show']);
    Route::resource('buyers', BuyerController::class)->except(['show']);

    Route::get('expenses/recap/screenshot', [OwnerExpenseController::class, 'screenshot'])->name('expenses.screenshot');
    Route::get('expenses/recap/print', [OwnerExpenseController::class, 'print'])->name('expenses.print');
    Route::resource('expenses', OwnerExpenseController::class)->parameters(['expenses' => 'expense'])->except(['show']);

    Route::middleware([EnsureRole::class.':'.User::ROLE_OWNER])->group(function () {
        Route::resource('admins', AdminController::class)->parameters(['admins' => 'admin'])->except(['show']);
    });

    Route::resource('invoices', FishDeliveryInvoiceController::class);
    Route::post('invoices/{invoice}/post', [FishDeliveryInvoiceController::class, 'post'])->name('invoices.post');
    Route::post('invoices/{invoice}/cancel', [FishDeliveryInvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('invoices/{invoice}/screenshot', [FishDeliveryInvoiceController::class, 'screenshot'])->name('invoices.screenshot');
    Route::get('invoices/{invoice}/print', [FishDeliveryInvoiceController::class, 'print'])->name('invoices.print');

    Route::resource('monthly-closings', MonthlyClosingController::class)->parameters(['monthly-closings' => 'monthlyClosing'])->except(['destroy']);
    Route::delete('monthly-closings/{monthlyClosing}', [MonthlyClosingController::class, 'destroy'])->name('monthly-closings.destroy');
    Route::get('monthly-closings/{monthlyClosing}/screenshot', [MonthlyClosingController::class, 'screenshot'])->name('monthly-closings.screenshot');
    Route::get('monthly-closings/{monthlyClosing}/print', [MonthlyClosingController::class, 'print'])->name('monthly-closings.print');
});
