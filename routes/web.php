<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeLoginController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WorldHeartDayController;


Route::get('/', [EmployeeLoginController::class, 'loginForm'])->name('login');
Route::post('/login', [EmployeeLoginController::class, 'login'])->name('employee.login');
Route::middleware('auth:employee')->group(function () {

    Route::get('/dashboard', [EmployeeLoginController::class, 'dashboard'])->name('dashboard');

    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
    Route::post('/doctors/store', [DoctorController::class, 'store'])->name('doctors.store');
    Route::get('/doctors/{id}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
    Route::post('/doctors/{id}/update', [DoctorController::class, 'update'])->name('doctors.update');
    Route::get('/doctors/{id}/destroy', [DoctorController::class, 'destroy'])->name('doctors.destroy');
    Route::get('/api/msl-number', [DoctorController::class, 'getMslNumber'])->name('api.msl_number');
    Route::get('/api/doctors-by-employee', [DoctorController::class, 'doctorsByEmployee'])
        ->name('api.doctors_by_employee');


    Route::post('/logout', [EmployeeLoginController::class, 'logout'])->name('logout');

});
Route::get('admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AuthController::class, 'login'])->name('login.post');
Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
// Admin Routes (protected)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/doctors', [AdminController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/export', [AdminController::class, 'export'])->name('doctors.export');
    Route::get('/doctors/download-photos', [AdminController::class, 'downloadPhotos'])
        ->name('doctors.download-photos');
    Route::get('/world-heart-day', [WorldHeartDayController::class, 'index'])->name('world-heart-day.index');
    Route::post('/world-heart-day/import', [WorldHeartDayController::class, 'import'])->name('world-heart-day.import');
    Route::get('/world-heart-day/export', [WorldHeartDayController::class, 'export'])->name('world-heart-day.export');
    Route::get('/world-heart-day/download-banners', [WorldHeartDayController::class, 'downloadBanners'])
        ->name('world-heart-day.download-banners');
    Route::post('/world-heart-day/{entry}/gender', [WorldHeartDayController::class, 'updateGender'])
        ->name('world-heart-day.gender');
    Route::delete('/world-heart-day/{entry}/banner', [WorldHeartDayController::class, 'deleteBanner'])
        ->name('world-heart-day.banner.delete');
});
