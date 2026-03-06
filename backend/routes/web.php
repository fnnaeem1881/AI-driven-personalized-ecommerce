<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlashDealController;

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/category/{slug}', [ProductController::class, 'byCategory'])->name('category.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/flash-deals', [FlashDealController::class, 'index'])->name('flash-deals.index');

// ─── Cart (public) ────────────────────────────────────────────────────────────
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Protected Routes ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // Account
    Route::get('/account', [AccountController::class, 'dashboard'])->name('account.dashboard');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

// ─── Support Pages ────────────────────────────────────────────────────────────
Route::get('/help-center', fn() => view('support.help'))->name('support.help');
Route::get('/shipping-info', fn() => view('support.shipping'))->name('support.shipping');
Route::get('/returns-policy', fn() => view('support.returns'))->name('support.returns');
Route::get('/track-order', fn() => view('support.track'))->name('support.track');
Route::get('/contact', fn() => view('support.contact'))->name('support.contact');

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::post('upload-image', [\App\Http\Controllers\Admin\ImageUploadController::class, 'store'])->name('upload-image');
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // Products CRUD
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);

    // Categories CRUD
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    // Orders
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/payment', [\App\Http\Controllers\Admin\OrderController::class, 'updatePayment'])->name('orders.payment');

    // Users
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.role');
    Route::patch('users/{user}/spatie-role', [\App\Http\Controllers\Admin\UserController::class, 'assignSpatieRole'])->name('users.spatie-role');

    // AI Services Health Dashboard + Retrain
    Route::get('ai-health', [\App\Http\Controllers\Admin\AdminController::class, 'aiHealth'])->name('ai-health');
    Route::post('ai-retrain', [\App\Http\Controllers\Admin\AdminController::class, 'aiRetrain'])->name('ai-retrain');

    // Admin Profile
    Route::get('profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile');
    Route::post('profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
    Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Roles
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)
         ->except(['show']);

    // Hero Slides
    Route::resource('slides', \App\Http\Controllers\Admin\HeroSlideController::class)->except(['show']);

    // Flash Deals
    Route::resource('flash-deals', \App\Http\Controllers\Admin\FlashDealController::class)->except(['show']);

    // Permissions
    Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
    Route::get('permissions/create', [\App\Http\Controllers\Admin\PermissionController::class, 'create'])->name('permissions.create');
    Route::post('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->name('permissions.destroy');
    Route::patch('roles/{role}/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'assignToRole'])->name('roles.permissions.sync');
});
