<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportRegistrationController;
use App\Http\Controllers\BrandPortalController;
use App\Http\Controllers\AdminToolsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing.index');

// 1. Rutas para el Registro Rápido de Pasaporte de Eventos
Route::get('/registro-pasaporte', [PassportRegistrationController::class, 'showForm'])->name('passport.register');
Route::post('/registro-pasaporte', [PassportRegistrationController::class, 'register'])->name('passport.register.post');
Route::get('/registro-exitoso', [PassportRegistrationController::class, 'success'])->name('passport.success');

// 2. Rutas para el Portal de Marcas (Stand Scanner)
Route::prefix('portal-marcas')->group(function () {
    Route::get('/login', [BrandPortalController::class, 'showLoginForm'])->name('brand.login');
    Route::post('/login', [BrandPortalController::class, 'login'])->name('brand.login.post');
    
    Route::middleware('auth')->group(function () {
        Route::get('/', [BrandPortalController::class, 'dashboard'])->name('brand.dashboard');
        Route::post('/verificar', [BrandPortalController::class, 'verifyCode'])->name('brand.verify');
        Route::post('/canjear', [BrandPortalController::class, 'redeemCoupon'])->name('brand.redeem');
        Route::post('/logout', [BrandPortalController::class, 'logout'])->name('brand.logout');
    });
});

// 3. Rutas Administrativas de Agilidad Operativa (Cargas Masivas y Lotes)
Route::prefix('admin-tools')->middleware('auth')->group(function () {
    Route::get('/', [AdminToolsController::class, 'showDashboard'])->name('admin.tools.dashboard');
    Route::post('/import-brands', [AdminToolsController::class, 'importBrands'])->name('admin.tools.import-brands');
    Route::post('/import-attendees', [AdminToolsController::class, 'importAttendees'])->name('admin.tools.import-attendees');
    Route::post('/batch-coupons', [AdminToolsController::class, 'batchCreateCoupons'])->name('admin.tools.batch-coupons');
});

// 4. Rutas del Panel Administrativo de Laravel Voyager
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
