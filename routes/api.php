<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Server is running',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

Route::get('/contact', [UserController::class, 'contact']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'role:finance_batik|admin_batik|finance_tourism|admin_tourism|admin'])->prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/', [TransactionController::class, 'store']);
    Route::get('/{id}', [TransactionController::class, 'show']);
    Route::put('/{id}', [TransactionController::class, 'update']);
    Route::delete('/{id}', [TransactionController::class, 'destroy']);
    Route::get('/analytics/data', [TransactionController::class, 'analytics']);
    Route::get('/export/data', [TransactionController::class, 'export']);
});

Route::middleware(['auth:sanctum', 'role:finance_batik|admin_batik|finance_tourism|admin_tourism'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
});

// Article
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/{id}', [ArticleController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [ArticleController::class, 'store']);
        Route::patch('/{id}', [ArticleController::class, 'update']);
        Route::delete('/{id}', [ArticleController::class, 'destroy']);
    });
});

// Event
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{id}', [EventController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [EventController::class, 'store']);
        Route::patch('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
    });
});

// Gallery
Route::prefix('galleries')->group(function () {
    Route::get('/', [GalleryController::class, 'index']);
    Route::get('/{id}', [GalleryController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [GalleryController::class, 'store']);
        Route::patch('/{id}', [GalleryController::class, 'update']);
        Route::delete('/{id}', [GalleryController::class, 'destroy']);
    });
});

// Package
Route::prefix('packages')->group(function () {
    Route::get('/', [PackageController::class, 'index']);
    Route::get('/{id}', [PackageController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [PackageController::class, 'store']);
        Route::patch('/{id}', [PackageController::class, 'update']);
        Route::delete('/{id}', [PackageController::class, 'destroy']);
    });
});

// Setting
Route::prefix('settings')->group(function () {
    Route::get('/', [SettingController::class, 'index']);
    Route::get('/{id}', [SettingController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [SettingController::class, 'store']);
        Route::patch('/{id}', [SettingController::class, 'update']);
        Route::delete('/{id}', [SettingController::class, 'destroy']);
    });
});

// Tour
Route::prefix('tours')->group(function () {
    Route::get('/', [TourController::class, 'index']);
    Route::get('/{id}', [TourController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [TourController::class, 'store']);
        Route::patch('/{id}', [TourController::class, 'update']);
        Route::delete('/{id}', [TourController::class, 'destroy']);
    });
});

// User
Route::middleware(['auth:sanctum'])->prefix('users')->group(function () {
    Route::patch('/', [UserController::class, 'update']);
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::patch('/{id}', [UserController::class, 'updateById']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});

// Ticket
Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index']);
    Route::get('/{id}', [TicketController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [TicketController::class, 'store']);
        Route::patch('/{id}', [TicketController::class, 'update']);
        Route::delete('/{id}', [TicketController::class, 'destroy']);
    });
});

// Order
Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/cancel/{id}', [OrderController::class, 'cancel']);
    Route::get('/history', [OrderController::class, 'history']);

    Route::post('/scan', [OrderController::class, 'scan'])->middleware('role:admin');
    Route::get('/visitor', [OrderController::class, 'visitor'])->middleware('role:admin');
});

Route::post('/callback', [OrderController::class, 'callback']);
Route::get('/callback', [OrderController::class, 'callback']);
