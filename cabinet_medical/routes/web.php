<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', function (string $locale): RedirectResponse {
    if (in_array($locale, ['en', 'fr'])) {
        session(['locale' => $locale]);
    }

    return back();
})->name('language');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

        Route::get('/admin/services', [AdminController::class, 'services'])->name('admin.services');
        Route::post('/admin/services', [AdminController::class, 'storeService'])->name('admin.services.store');
        Route::put('/admin/services/{service}', [AdminController::class, 'updateService'])->name('admin.services.update');
        Route::delete('/admin/services/{service}', [AdminController::class, 'destroyService'])->name('admin.services.destroy');

        Route::get('/admin/appointments', [AdminController::class, 'appointments'])->name('admin.appointments');
    });

    Route::middleware('doctor')->group(function () {
        Route::get('/doctor', [DoctorController::class, 'dashboard'])->name('doctor.dashboard');
        Route::get('/doctor/appointments', [DoctorController::class, 'appointments'])->name('doctor.appointments');
        Route::post('/doctor/appointments/{appointment}/accept', [DoctorController::class, 'accept'])->name('doctor.appointments.accept');
        Route::post('/doctor/appointments/{appointment}/cancel', [DoctorController::class, 'cancel'])->name('doctor.appointments.cancel');
        Route::get('/doctor/patients/{patient}', [DoctorController::class, 'patient'])->name('doctor.patients.show');
    });

    Route::middleware('patient')->group(function () {
        Route::get('/patient', [PatientController::class, 'dashboard'])->name('patient.dashboard');
        Route::get('/patient/appointments', [PatientController::class, 'appointments'])->name('patient.appointments');
        Route::get('/patient/appointments/create', [PatientController::class, 'create'])->name('patient.appointments.create');
        Route::post('/patient/appointments', [PatientController::class, 'store'])->name('patient.appointments.store');
        Route::get('/patient/appointments/{appointment}/edit', [PatientController::class, 'edit'])->name('patient.appointments.edit');
        Route::put('/patient/appointments/{appointment}', [PatientController::class, 'update'])->name('patient.appointments.update');
        Route::post('/patient/appointments/{appointment}/cancel', [PatientController::class, 'cancel'])->name('patient.appointments.cancel');
    });

    Route::middleware('role:'.implode(',', User::roles()))->group(function () {
        Route::get('/appointments/search', [AppointmentController::class, 'search'])->name('appointments.search');

        Route::middleware('admin')->group(function () {
            Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
            Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
        });

        Route::resource('appointments', AppointmentController::class)->except('show');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
