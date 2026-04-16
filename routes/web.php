<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WaLoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/kategori', [HomeController::class, 'categories'])->name('categories.index');

// ─── WhatsApp Webhook (CSRF exempt via bootstrap/app.php) ────────────────────
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle'])->name('webhook.whatsapp');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // ─── WhatsApp OTP Login ────────────────────────────────────────────────
    // GET  /wa-login → form OTP1 + OTP2
    // POST /wa-login → verifikasi dan login
    Route::get('/wa-login', [WaLoginController::class, 'index'])->name('wa-login');
    Route::post('/wa-login', [WaLoginController::class, 'verify'])->name('wa-login.verify');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/listing/{id}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');
    
    // Member Listing Actions
    Route::get('/listing/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listing', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/listing/{id}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listing/{id}', [ListingController::class, 'update'])->name('listings.update');
    Route::post('/listing/{id}/toggle', [ListingController::class, 'toggleStatus'])->name('listings.toggle');
    Route::delete('/listing/{id}', [ListingController::class, 'destroy'])->name('listings.destroy');
    
    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Category Management
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::get('/categories/create', [AdminController::class, 'createCategory'])->name('categories.create');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::get('/categories/{id}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

        // Listing Management
        Route::get('/listings', [AdminController::class, 'listings'])->name('listings');
        Route::get('/listings/create', [AdminController::class, 'createListing'])->name('listings.create');
        Route::post('/listings', [AdminController::class, 'storeListing'])->name('listings.store');
        Route::get('/listings/{id}/edit', [AdminController::class, 'editListing'])->name('listings.edit');
        Route::put('/listings/{id}', [AdminController::class, 'updateListing'])->name('listings.update');
        Route::delete('/listings/{id}', [AdminController::class, 'destroyListing'])->name('listings.destroy');
        Route::post('/listings/{id}/toggle', [AdminController::class, 'toggleListingStatus'])->name('listings.toggle');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{id}/toggle-admin', [AdminController::class, 'toggleAdminStatus'])->name('users.toggle-admin');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        // Listing Type Management
        Route::get('/listing-types', [AdminController::class, 'listingTypes'])->name('listing_types');
        Route::get('/listing-types/create', [AdminController::class, 'createListingType'])->name('listing_types.create');
        Route::post('/listing-types', [AdminController::class, 'storeListingType'])->name('listing_types.store');
        Route::get('/listing-types/{id}/edit', [AdminController::class, 'editListingType'])->name('listing_types.edit');
        Route::put('/listing-types/{id}', [AdminController::class, 'updateListingType'])->name('listing_types.update');
        Route::delete('/listing-types/{id}', [AdminController::class, 'destroyListingType'])->name('listing_types.destroy');
    });
});

// Static pages routes
Route::view('/about', 'about')->name('about');
Route::view('/terms-and-conditions', 'terms-and-conditions')->name('terms.and.conditions');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');

// Wildcard Routes (Must be at the bottom)
Route::get('/listing/{slug}', [HomeController::class, 'show'])->name('listings.show');
