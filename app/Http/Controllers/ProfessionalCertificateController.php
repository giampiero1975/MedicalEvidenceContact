<?php

namespace App\Http\Controllers;

use App\Models\UserCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfessionalCertificateController extends Controller
{
    public function view(Request $request, UserCertificate $certificate): StreamedResponse
    {
        $this->authorizeOwner($request, $certificate);
        $this->ensurePdfExists($certificate);

        return Storage::disk('local')->response(
            $certificate->pdf_stored_path,
            $this->filename($certificate),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$this->filename($certificate).'"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function download(Request $request, UserCertificate $certificate): StreamedResponse
    {
        $this->authorizeOwner($request, $certificate);
        $this->ensurePdfExists($certificate);

        return Storage::disk('local')->download(
            $certificate->pdf_stored_path,
            $this->filename($certificate),
            ['Content-Type' => 'application/pdf']
        );
    }

    private function authorizeOwner(Request $request, UserCertificate $certificate): void
    {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless($certificate->laravel_user_id === $request->user()->id, 403);
    }

    private function ensurePdfExists(UserCertificate $certificate): void
    {
        abort_unless(
            filled($certificate->pdf_stored_path)
                && Storage::disk('local')->exists($certificate->pdf_stored_path),
            404
        );
    }

    private function filename(UserCertificate $certificate): string
    {
        $base = $certificate->certificate_name ?: $certificate->template_name ?: 'attestato-moodle';
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $base) ?: 'attestato-moodle';

        return trim($safe, '-').'.pdf';
    }
}
