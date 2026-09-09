<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminBusinessTypeController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminJobPostingController;
use App\Http\Controllers\AdminUiPlaygroundController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BusinessCandidateApplicationController;
use App\Http\Controllers\BusinessDashboardController;
use App\Http\Controllers\BusinessDepartmentController;
use App\Http\Controllers\BusinessLocationController;
use App\Http\Controllers\BusinessPointOfContactController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobApplicationNoteController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\JobPostingFavoriteController;
use App\Http\Controllers\MoodleAccountLinkController;
use App\Http\Controllers\MoodleCertificateSyncController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\ProfessionalApplicationsController;
use App\Http\Controllers\ProfessionalCertificateController;
use App\Http\Controllers\ProfessionalDocumentController;
use App\Http\Controllers\ProfessionalDocumentsPageController;
use App\Http\Controllers\ProfessionalExperienceController;
use App\Http\Controllers\ProfessionalProfileItemController;
use App\Http\Controllers\ProfessionalDashboardController;
use Illuminate\Http\Request;

Route::get('/', fn () => view('welcome'));
Route::get('/admin', function (Request $request) {
    if (! $request->user()) {
        return redirect()->route('admin.login');
    }

    abort_unless($request->user()->role === 'admin', 403);

    return redirect()->route('admin.dashboard');
})->name('admin.entry');
Route::get('/admin/login', fn () => view('auth.staff-login'))->middleware('guest')->name('admin.login');
Route::redirect('/staff/login', '/admin/login')->middleware('guest')->name('staff.login');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if ($request->user()->role === 'admin') return redirect()->route('admin.dashboard');
        if ($request->user()->role === 'professional') return app(ProfessionalDashboardController::class)($request);
        return app(BusinessDashboardController::class)($request);
    })->name('dashboard');

    Route::get('/preferenze-notifiche', [NotificationPreferenceController::class, 'edit'])->name('notification-preferences.edit');
    Route::put('/preferenze-notifiche', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');

    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::get('/admin/ui', AdminUiPlaygroundController::class)->name('admin.ui.index');
    Route::resource('/admin/users', AdminUserController::class)->names('admin.users')->except('show');
    Route::resource('/admin/annunci', AdminJobPostingController::class)->names('admin.job-postings')->parameters(['annunci' => 'jobPosting'])->except('show');
    Route::patch('/admin/business-types/{businessType}/toggle', [AdminBusinessTypeController::class, 'toggle'])->name('admin.business-types.toggle');
    Route::resource('/admin/business-types', AdminBusinessTypeController::class)
        ->names('admin.business-types')
        ->parameters(['business-types' => 'businessType'])
        ->except('show');

    Route::get('/annunci', [JobPostingController::class, 'index'])->name('job-postings.index');
    Route::get('/annunci/crea', [JobPostingController::class, 'create'])->name('job-postings.create');
    Route::post('/annunci', [JobPostingController::class, 'store'])->name('job-postings.store');
    Route::get('/annunci/{jobPosting}', [JobPostingController::class, 'show'])->name('job-postings.show');
    Route::get('/annunci/{jobPosting}/modifica', [JobPostingController::class, 'edit'])->name('job-postings.edit');
    Route::put('/annunci/{jobPosting}', [JobPostingController::class, 'update'])->name('job-postings.update');
    Route::delete('/annunci/{jobPosting}', [JobPostingController::class, 'destroy'])->name('job-postings.destroy');
    Route::get('/annunci/{jobPosting}/candidature', [JobPostingController::class, 'applications'])->name('job-postings.applications');
    Route::post('/annunci/{jobPosting}/candidati', [JobApplicationController::class, 'store'])->name('job-applications.store');
    Route::post('/annunci/{jobPosting}/preferito', [JobPostingFavoriteController::class, 'store'])->name('job-postings.favorites.store');
    Route::delete('/annunci/{jobPosting}/preferito', [JobPostingFavoriteController::class, 'destroy'])->name('job-postings.favorites.destroy');
    Route::get('/business/candidature/{jobApplication}', [BusinessCandidateApplicationController::class, 'show'])->name('business.applications.show');
    Route::post('/business/candidature/{jobApplication}/note', [JobApplicationNoteController::class, 'store'])->name('business.applications.notes.store');
    Route::post('/business/candidature/{jobApplication}/colloqui', [InterviewController::class, 'store'])->name('business.applications.interviews.store');
    Route::patch('/business/colloqui/{interview}/conferma', [InterviewController::class, 'confirm'])->name('business.interviews.confirm');
    Route::patch('/colloqui/{interview}/risposta', [InterviewController::class, 'respond'])->name('professional.interviews.respond');
    Route::patch('/colloqui/{interview}/riprogramma', [InterviewController::class, 'reschedule'])->name('interviews.reschedule');
    Route::get('/colloqui/{interview}/annullamento', [InterviewController::class, 'cancellationConfirmation'])->name('interviews.cancellation.confirmation');
    Route::patch('/colloqui/{interview}/annullamento/conferma', [InterviewController::class, 'confirmCancellation'])->name('interviews.cancellation.confirm');
    Route::patch('/colloqui/{interview}/annulla', [InterviewController::class, 'cancel'])->name('interviews.cancel');
    Route::patch('/candidature/{jobApplication}/stato', [JobApplicationController::class, 'updateStatus'])->name('job-applications.status.update');
    Route::get('/colloqui', [InterviewController::class, 'index'])->name('interviews.index');

    Route::get('/business/profilo', [BusinessProfileController::class, 'edit'])->name('business.profile.edit');
    Route::put('/business/profilo', [BusinessProfileController::class, 'update'])->name('business.profile.update');
    Route::get('/business/profilo/logo', [BusinessProfileController::class, 'logo'])->name('business.profile.logo');
    Route::get('/business/sedi', [BusinessLocationController::class, 'index'])->name('business.locations.index');
    Route::post('/business/sedi', [BusinessLocationController::class, 'store'])->name('business.locations.store');
    Route::get('/business/sedi/{location}/modifica', [BusinessLocationController::class, 'edit'])->name('business.locations.edit');
    Route::put('/business/sedi/{location}', [BusinessLocationController::class, 'update'])->name('business.locations.update');
    Route::delete('/business/sedi/{location}', [BusinessLocationController::class, 'destroy'])->name('business.locations.destroy');
    Route::get('/business/reparti', [BusinessDepartmentController::class, 'index'])->name('business.departments.index');
    Route::post('/business/reparti', [BusinessDepartmentController::class, 'store'])->name('business.departments.store');
    Route::get('/business/reparti/{department}/modifica', [BusinessDepartmentController::class, 'edit'])->name('business.departments.edit');
    Route::put('/business/reparti/{department}', [BusinessDepartmentController::class, 'update'])->name('business.departments.update');
    Route::delete('/business/reparti/{department}', [BusinessDepartmentController::class, 'destroy'])->name('business.departments.destroy');
    Route::get('/business/point-of-contact', [BusinessPointOfContactController::class, 'index'])->name('business-points-of-contact.index');
    Route::post('/business/point-of-contact', [BusinessPointOfContactController::class, 'store'])->name('business-points-of-contact.store');

    Route::get('/professionista/candidature', ProfessionalApplicationsController::class)->name('professional.applications.index');
    Route::get('/professionista/esperienze', ProfessionalExperienceController::class)->name('professional.experiences.index');
    Route::get('/professionista/documenti', ProfessionalDocumentsPageController::class)->name('professional.documents.index');
    Route::post('/professionista/documenti', [ProfessionalDocumentController::class, 'store'])->name('professional-documents.store');
    Route::get('/professionista/documenti/{type}/visualizza', [ProfessionalDocumentController::class, 'view'])->whereIn('type', ['ata_certificate', 'residence_permit'])->name('professional-documents.view');
    Route::get('/professionista/documenti/{type}/scarica', [ProfessionalDocumentController::class, 'download'])->whereIn('type', ['ata_certificate', 'residence_permit'])->name('professional-documents.download');
    Route::delete('/professionista/documenti/{type}', [ProfessionalDocumentController::class, 'destroy'])->whereIn('type', ['ata_certificate', 'residence_permit'])->name('professional-documents.destroy');

    Route::get('/professionista/moodle', [MoodleAccountLinkController::class, 'index'])->name('professional.moodle.index');
    Route::post('/professionista/moodle/collegamenti', [MoodleAccountLinkController::class, 'start'])->name('professional.moodle.start');
    Route::delete('/professionista/moodle/collegamenti/{moodleUserLink}', [MoodleAccountLinkController::class, 'disconnect'])->name('professional.moodle.disconnect');
    Route::post('/professionista/moodle/collegamenti/{moodleUserLink}/sincronizza-attestati', MoodleCertificateSyncController::class)->name('professional.moodle.certificates.sync');
    Route::get('/professionista/moodle/attestati/{certificate}/visualizza', [ProfessionalCertificateController::class, 'view'])->name('professional.moodle.certificates.view');
    Route::get('/professionista/moodle/attestati/{certificate}/scarica', [ProfessionalCertificateController::class, 'download'])->name('professional.moodle.certificates.download');
    Route::get('/professionista/moodle/tentativi/{attempt}/verifica', [MoodleAccountLinkController::class, 'showVerify'])->name('professional.moodle.verify.show');
    Route::post('/professionista/moodle/tentativi/{attempt}/verifica', [MoodleAccountLinkController::class, 'verify'])->name('professional.moodle.verify');
    Route::post('/professionista/moodle/tentativi/{attempt}/annulla', [MoodleAccountLinkController::class, 'cancel'])->name('professional.moodle.cancel');
    Route::post('/professionista/profilo-elementi', [ProfessionalProfileItemController::class, 'store'])->name('professional-profile-items.store');
    Route::put('/professionista/profilo-elementi/{professionalProfileItem}', [ProfessionalProfileItemController::class, 'update'])->name('professional-profile-items.update');
    Route::delete('/professionista/profilo-elementi/{professionalProfileItem}', [ProfessionalProfileItemController::class, 'destroy'])->name('professional-profile-items.destroy');
});