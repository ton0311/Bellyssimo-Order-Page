<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminInventoryController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminOrderNotificationController;
use App\Http\Controllers\AdminPasswordController;
use App\Http\Controllers\BellyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/our-belly', BellyController::class)->name('belly');
Route::get('/about-faqs', AboutController::class)->name('about');
Route::get('/order', [OrderController::class, 'create'])->name('order');
Route::post('/order/confirmation', [OrderController::class, 'confirm'])->name('order.confirm');
Route::get('/order/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');
Route::post('/order/place', [OrderController::class, 'store'])->name('order.place');
Route::get('/order/thank-you', [OrderController::class, 'thankYou'])->name('order.thank-you');
Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])
    ->middleware('admin.auth')
    ->name('admin.logout');

Route::middleware('admin.auth')->group(function (): void {
    Route::redirect('/admin', '/admin/dashboard');
    Route::get('/admin/dashboard', [AdminOrderController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/schedule', [AdminOrderController::class, 'schedule'])->name('admin.schedule');
    Route::get('/admin/password', [AdminPasswordController::class, 'edit'])->name('admin.password.edit');
    Route::patch('/admin/password', [AdminPasswordController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('admin.password.update');
    Route::get('/admin/email-alerts', [AdminOrderNotificationController::class, 'edit'])->name('admin.notifications.edit');
    Route::post('/admin/email-alerts', [AdminOrderNotificationController::class, 'store'])->name('admin.notifications.store');
    Route::delete('/admin/email-alerts', [AdminOrderNotificationController::class, 'destroy'])->name('admin.notifications.destroy');
    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
    Route::post('/admin/orders', [AdminOrderController::class, 'store'])->name('admin.orders.store');
    Route::patch('/admin/orders/{order}', [AdminOrderController::class, 'update'])->name('admin.orders.update');
    Route::patch('/admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::patch('/admin/orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('admin.orders.payment');
    Route::delete('/admin/orders/{order}', [AdminOrderController::class, 'destroy'])->name('admin.orders.destroy');
    Route::delete('/admin/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('admin.customers.destroy');
    Route::post('/admin/inventory', [AdminInventoryController::class, 'store'])->name('admin.inventory.store');
    Route::patch('/admin/inventory/{inventoryItem}', [AdminInventoryController::class, 'update'])->name('admin.inventory.update');
});
Route::post('/generate', [HomeController::class, 'generate'])->name('generate');
