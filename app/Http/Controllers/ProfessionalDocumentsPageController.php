<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDocumentsPageController extends Controller
{
    public function __invoke(Request $request): View
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
                    'key' => 'ata_certificate_document',
                    'label' => 'Attestato ATA',
                    'description' => 'Attestato professionale richiesto per completare il profilo.',
                    'path' => $user->ata_certificate_path,
                    'required' => true,
                ],
                ...(! $isItalian ? [[
                    'key' => 'residence_permit_document',
                    'label' => 'Permesso di soggiorno',
                    'description' => 'Documento richiesto per i professionisti con cittadinanza non italiana.',
                    'path' => $user->residence_permit_path,
                    'required' => true,
                ]] : []),
            ]),
        ]);
    }
}
