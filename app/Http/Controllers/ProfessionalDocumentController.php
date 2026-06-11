<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfessionalDocumentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);

        $data = $request->validate([
            'ata_certificate_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'residence_permit_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $updates = [];

        if ($request->hasFile('ata_certificate_document')) {
            $updates['ata_certificate_path'] = $data['ata_certificate_document']
                ->store('professional-documents/ata-certificates', 'public');
        }

        if ($request->hasFile('residence_permit_document')) {
            $updates['residence_permit_path'] = $data['residence_permit_document']
                ->store('professional-documents/residence-permits', 'public');
        }

        if ($updates !== []) {
            $request->user()->forceFill($updates)->save();
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Documenti aggiornati.');
    }
}
