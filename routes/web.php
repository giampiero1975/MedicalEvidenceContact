<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminJobPostingController;
use App\Http\Controllers\AdminRegistrationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BusinessPointOfContactController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\ProfessionalDocumentController;
use App\Http\Controllers\ProfessionalProfileItemController;
use App\Http\Controllers\ProfessionalDashboardController;
use Illuminate\Http\Request;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', function () {
    return view('auth.staff-login');
})->middleware('guest')->name('admin.login');

Route::get('/admin/register', [AdminRegistrationController::class, 'create'])
    ->middleware('guest')
    ->name('admin.register');

Route::post('/admin/register', [AdminRegistrationController::class, 'store'])
    ->middleware('guest')
    ->name('admin.register.store');

Route::redirect('/staff/login', '/admin/login')->middleware('guest')->name('staff.login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()->role === 'professional') {
            return app(ProfessionalDashboardController::class)($request);
        }

        return app(JobPostingController::class)->index($request);
    })->name('dashboard');

    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::resource('/admin/users', AdminUserController::class)->names('admin.users')->except('show');
    Route::resource('/admin/annunci', AdminJobPostingController::class)->names('admin.job-postings')->parameters(['annunci' => 'jobPosting'])->except('show');

    Route::get('/annunci', [JobPostingController::class, 'index'])->name('job-postings.index');
    Route::get('/annunci/crea', [JobPostingController::class, 'create'])->name('job-postings.create');
    Route::post('/annunci', [JobPostingController::class, 'store'])->name('job-postings.store');
    Route::get('/annunci/{jobPosting}', [JobPostingController::class, 'show'])->name('job-postings.show');
    Route::get('/annunci/{jobPosting}/modifica', [JobPostingController::class, 'edit'])->name('job-postings.edit');
    Route::put('/annunci/{jobPosting}', [JobPostingController::class, 'update'])->name('job-postings.update');
    Route::delete('/annunci/{jobPosting}', [JobPostingController::class, 'destroy'])->name('job-postings.destroy');
    Route::get('/annunci/{jobPosting}/candidature', [JobPostingController::class, 'applications'])->name('job-postings.applications');
    Route::post('/annunci/{jobPosting}/candidati', [JobApplicationController::class, 'store'])->name('job-applications.store');
    Route::get('/business/point-of-contact', [BusinessPointOfContactController::class, 'index'])->name('business-points-of-contact.index');
    Route::post('/business/point-of-contact', [BusinessPointOfContactController::class, 'store'])->name('business-points-of-contact.store');
    Route::post('/professionista/documenti', [ProfessionalDocumentController::class, 'store'])->name('professional-documents.store');
    Route::post('/professionista/profilo-elementi', [ProfessionalProfileItemController::class, 'store'])->name('professional-profile-items.store');
    Route::put('/professionista/profilo-elementi/{professionalProfileItem}', [ProfessionalProfileItemController::class, 'update'])->name('professional-profile-items.update');
    Route::delete('/professionista/profilo-elementi/{professionalProfileItem}', [ProfessionalProfileItemController::class, 'destroy'])->name('professional-profile-items.destroy');
});
