<?php

namespace App\Http\Controllers;

use App\Services\ProfessionalDocumentStorage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDocumentsPageController extends Controller
{
    public function __invoke(Request $request, ProfessionalDocumentStorage $storage): View
    {
        abort_unless($request->user()->role === 'professional', 403);

        $user = $request->user();
        $isItalian = in_array(
            strtolower(trim((string) $user->nationality)),
            ['italiana', 'italiano', 'italia', 'italian'],
            true
        );

        return view('professionals.documents.index', [
            'documents' => collect([
                [
                    'type' => 'ata_certificate',
                    'key' => 'ata_certificate_document',
                    'label' => 'Attestato ATA',
                    'description' => 'Attestato professionale richiesto per completare il profilo.',
                    'file' => $storage->get($user, 'ata_certificate'),
                    'required' => true,
                ],
                ...(! $isItalian ? [[
                    'type' => 'residence_permit',
                    'key' => 'residence_permit_document',
                    'label' => 'Permesso di soggiorno',
                    'description' => 'Documento richiesto per i professionisti con cittadinanza non italiana.',
                    'file' => $storage->get($user, 'residence_permit'),
                    'required' => true,
                ]] : []),
            ]),
        ]);
    }
}
