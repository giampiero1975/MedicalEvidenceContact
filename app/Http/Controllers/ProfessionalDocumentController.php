<?php

namespace App\Http\Controllers;

use App\Services\ProfessionalDocumentStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfessionalDocumentController extends Controller
{
    public function store(Request $request, ProfessionalDocumentStorage $documents): RedirectResponse
    {
        $this->authorizeProfessional($request);

        $data = $request->validate([
            'ata_certificate_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'residence_permit_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'redirect_to' => ['nullable', 'string', 'in:documents'],
        ]);

        $documents->store($request->user(), $data);

        $route = $request->input('redirect_to') === 'documents'
            ? 'professional.documents.index'
            : 'dashboard';

        return redirect()
            ->route($route)
            ->with('status', 'Documenti aggiornati.');
    }

    public function view(Request $request, string $type, ProfessionalDocumentStorage $documents): StreamedResponse
    {
        $this->authorizeProfessional($request);
        $document = $documents->get($request->user(), $type);

        abort_if($document === null || ! Storage::disk($document['disk'])->exists($document['path']), 404);

        return Storage::disk($document['disk'])->response(
            $document['path'],
            $document['original_name'],
            [
                'Content-Type' => $document['mime_type'] ?: 'application/octet-stream',
                'Content-Disposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_INLINE,
                    $document['original_name']
                ),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function download(Request $request, string $type, ProfessionalDocumentStorage $documents): StreamedResponse
    {
        $this->authorizeProfessional($request);
        $document = $documents->get($request->user(), $type);

        abort_if($document === null || ! Storage::disk($document['disk'])->exists($document['path']), 404);

        return Storage::disk($document['disk'])->download(
            $document['path'],
            $document['original_name'],
            array_filter(['Content-Type' => $document['mime_type']])
        );
    }

    public function destroy(Request $request, string $type, ProfessionalDocumentStorage $documents): RedirectResponse
    {
        $this->authorizeProfessional($request);
        abort_unless($documents->delete($request->user(), $type), 404);

        return redirect()
            ->route('professional.documents.index')
            ->with('status', 'Documento eliminato.');
    }

    private function authorizeProfessional(Request $request): void
    {
        abort_unless($request->user()->role === 'professional', 403);
    }
}
