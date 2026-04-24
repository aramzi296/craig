<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WaLoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
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

// Listing Routes (Public with OTP)
Route::get('/listing/create', [ListingController::class, 'create'])->name('listings.create');
Route::post('/listing', [ListingController::class, 'store'])->name('listings.store');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/listing/{id}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');
    
    // Member Listing Actions
    Route::get('/listing/{id}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listing/{id}', [ListingController::class, 'update'])->name('listings.update');
    Route::post('/listing/{id}/toggle', [ListingController::class, 'toggleStatus'])->name('listings.toggle');
    Route::delete('/listing/{id}', [ListingController::class, 'destroy'])->name('listings.destroy');
    Route::delete('/listing/photo/{id}', [ListingController::class, 'deletePhoto'])->name('listings.photos.destroy');

    // Comment Actions
    Route::post('/listing/{id}/comment', [CommentController::class, 'store'])->name('comments.store');

    // Profile Actions
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
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

        // Premium Package Management
        Route::get('/premium-packages', [AdminController::class, 'premiumPackages'])->name('premium_packages');
        Route::get('/premium-packages/create', [AdminController::class, 'createPremiumPackage'])->name('premium_packages.create');
        Route::post('/premium-packages', [AdminController::class, 'storePremiumPackage'])->name('premium_packages.store');
        Route::get('/premium-packages/{id}/edit', [AdminController::class, 'editPremiumPackage'])->name('premium_packages.edit');
        Route::put('/premium-packages/{id}', [AdminController::class, 'updatePremiumPackage'])->name('premium_packages.update');
        Route::delete('/premium-packages/{id}', [AdminController::class, 'destroyPremiumPackage'])->name('premium_packages.destroy');

        // Premium Requests
        Route::get('/premium-requests', [AdminController::class, 'premiumRequests'])->name('premium_requests');
        Route::post('/premium-requests/{id}/approve', [AdminController::class, 'approvePremiumRequest'])->name('premium_requests.approve');
        Route::post('/premium-requests/{id}/reject', [AdminController::class, 'rejectPremiumRequest'])->name('premium_requests.reject');
        Route::post('/premium-requests/{id}/reset', [AdminController::class, 'resetPremiumRequest'])->name('premium_requests.reset');

        // User Verification
        Route::post('/users/{id}/toggle-verification', [AdminController::class, 'toggleUserVerification'])->name('users.toggle-verification');

        // Settings Management
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('/settings/create', [AdminController::class, 'createSetting'])->name('settings.create');
        Route::post('/settings', [AdminController::class, 'storeSetting'])->name('settings.store');
        Route::get('/settings/{id}/edit', [AdminController::class, 'editSetting'])->name('settings.edit');
        Route::put('/settings/{id}', [AdminController::class, 'updateSetting'])->name('settings.update');
        Route::delete('/settings/{id}', [AdminController::class, 'destroySetting'])->name('settings.destroy');

        // WhatsApp Messaging
        Route::get('/whatsapp', [AdminController::class, 'whatsappForm'])->name('whatsapp');
        Route::post('/send-wa', [AdminController::class, 'sendWaMessage'])->name('send_wa');
    });

    // Member Premium Upgrade
    Route::get('/dashboard/premium/upgrade/{listing_id}', [DashboardController::class, 'premiumUpgrade'])->name('dashboard.premium.upgrade');
    Route::post('/dashboard/premium/process', [DashboardController::class, 'processPremiumRequest'])->name('dashboard.premium.process');
    Route::get('/dashboard/premium/thank-you', function() {
        return view('listings.premium_thankyou');
    })->name('dashboard.premium.thankyou');
});



// Static pages routes
Route::get('/baca-saya', [HomeController::class, 'bacaSaya'])->name('baca-saya');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/terms-and-conditions', 'terms-and-conditions')->name('terms.and.conditions');

Route::view('/maintenance', 'maintenance')->name('maintenance');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');
Route::view('/disclaimer', 'disclaimer')->name('disclaimer');

Route::get('/test-tailwind', function () {
    return view('test-tailwind');
});

// Wildcard Routes (Must be at the bottom)
Route::get('/listing/{slug}', [HomeController::class, 'show'])->name('listings.show');
