<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\OverviewController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
 * The UI only calls Actions (BR6). Records are loaded by id inside the controllers,
 * after the conference context middleware ran; no implicit model binding.
 */

Route::get('/', HomeController::class)->name('home');

// --- Login (one window for participants and admins, R11) ---------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/forgot-password', [PasswordController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/choose', [LoginController::class, 'choose'])->name('choose');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')->whereNumber('id')->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:3,1')->name('verification.send');
});

// --- Registration on the conference subdomain (US-03, US-04) -------------------------
Route::middleware('conference.host')->group(function () {
    Route::get('/register', [RegistrationController::class, 'show'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('guest')->name('register.store');
    Route::post('/register/existing', [RegistrationController::class, 'storeExisting'])->middleware('auth')->name('register.existing');
    Route::get('/register/check-email', [RegistrationController::class, 'checkEmail'])->name('register.check-email');
});

// --- User page (M4) -----------------------------------------------------------------
Route::middleware(['auth', 'conference.owner'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/submissions', [AccountController::class, 'submissions'])->name('submissions.index');
    Route::get('/registrations/{registration}', [AccountController::class, 'show'])->whereNumber('registration')->name('registrations.show');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::post('/submissions/{submission}/submit', [AccountController::class, 'submit'])->whereNumber('submission')->name('submissions.submit');
    Route::post('/submissions/{submission}/versions', [AccountController::class, 'replace'])->whereNumber('submission')->name('submissions.replace');
    Route::get('/submission-versions/{version}', [AccountController::class, 'downloadSubmission'])->whereNumber('version')->name('submissions.download');
    Route::get('/invoices/{invoice}', [AccountController::class, 'downloadInvoice'])->whereNumber('invoice')->name('invoices.download');
});

// --- Admin (staff of the active conference, BR4) -------------------------------------
Route::middleware(['auth', 'conference.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.registrations.index'))->name('home');
    Route::get('/overview', OverviewController::class)->name('overview');
    Route::post('/conference', [AdminRegistrationController::class, 'switchConference'])->name('conference.switch');

    Route::get('/registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/find', [AdminRegistrationController::class, 'findByVariableSymbol'])->name('registrations.find');
    Route::get('/registrations/{registration}', [AdminRegistrationController::class, 'show'])->whereNumber('registration')->name('registrations.show');
    Route::post('/registrations/{registration}/payments', [AdminRegistrationController::class, 'recordPayment'])->whereNumber('registration')->name('registrations.payments');
    Route::post('/registrations/{registration}/fee-waiver', [AdminRegistrationController::class, 'waiveFee'])->whereNumber('registration')->name('registrations.waiver');
    Route::post('/registrations/{registration}/status', [AdminRegistrationController::class, 'changeStatus'])->whereNumber('registration')->name('registrations.status');
    Route::post('/invoices/{invoice}/retry', [AdminRegistrationController::class, 'retryInvoice'])->whereNumber('invoice')->name('invoices.retry');
    Route::get('/invoices/{invoice}', [AdminRegistrationController::class, 'downloadInvoice'])->whereNumber('invoice')->name('invoices.download');

    Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [AdminSubmissionController::class, 'show'])->whereNumber('submission')->name('submissions.show');
    Route::post('/submissions/{submission}/decision', [AdminSubmissionController::class, 'decide'])->whereNumber('submission')->name('submissions.decide');
    Route::get('/submission-versions/{version}', [AdminSubmissionController::class, 'download'])->whereNumber('version')->name('submissions.download');

    Route::get('/exports', [ExportController::class, 'show'])->name('exports.show');
    Route::post('/exports', [ExportController::class, 'download'])->name('exports.download');

    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team', [TeamController::class, 'store'])->name('team.store');
    Route::post('/team/remove', [TeamController::class, 'destroy'])->name('team.destroy');
});
