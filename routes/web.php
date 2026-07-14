<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GisController;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\ComplaintArchive;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\IncomingComplaints;
use App\Livewire\Admin\MonitoringMap;
use App\Livewire\Public\ComplaintDetail;
use App\Livewire\Public\ComplaintEditor;
use App\Livewire\Public\ComplaintGallery;
use App\Livewire\Public\ComplaintWizard;
use App\Livewire\Public\HomePage;
use App\Livewire\Public\ProfileDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/galeri', ComplaintGallery::class)->name('gallery');
Route::get('/keluhan/{complaint:slug}', ComplaintDetail::class)->name('complaints.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
    Route::get('/register', [SessionController::class, 'register'])->name('register');
    Route::post('/register', [SessionController::class, 'createAccount'])->name('register.store');
    Route::get('/login/otp', [OtpController::class, 'create'])->name('otp.create');
    Route::post('/login/otp/send', [OtpController::class, 'send'])->middleware('throttle:3,1')->name('otp.send');
    Route::post('/login/otp/verify', [OtpController::class, 'verify'])->middleware('throttle:8,1')->name('otp.verify');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/lapor', ComplaintWizard::class)->name('complaints.create');
    Route::get('/lapor/{complaint:slug}/edit', ComplaintEditor::class)->name('complaints.edit');
    Route::get('/profil', ProfileDashboard::class)->name('profile');
    Route::post('/api/gis/reverse-geocode', [GisController::class, 'reverseGeocode'])->middleware('throttle:20,1')->name('gis.reverse');
});

Route::prefix('api/gis')->name('gis.')->group(function (): void {
    Route::get('/complaints', [GisController::class, 'complaints'])->name('complaints');
    Route::get('/regions', [GisController::class, 'regions'])->name('regions');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/laporan/masuk', IncomingComplaints::class)->name('complaints.incoming');
    Route::get('/laporan/arsip', ComplaintArchive::class)->name('complaints.archive');
    Route::get('/kategori', CategoryManager::class)->name('categories');
    Route::get('/peta', MonitoringMap::class)->name('map');
    Route::get('/export/excel', [ExportController::class, 'excel'])->name('export.excel');
    Route::get('/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
});
