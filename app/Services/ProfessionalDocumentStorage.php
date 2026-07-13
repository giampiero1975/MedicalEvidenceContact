<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfessionalDocumentStorage
{
    private const DOCUMENT_FIELDS = [
        'ata_certificate_document' => [
            'key' => 'ata_certificate',
            'legacy_path' => 'ata_certificate_path',
            'directory' => 'professional-documents/ata-certificates',
        ],
        'residence_permit_document' => [
            'key' => 'residence_permit',
            'legacy_path' => 'residence_permit_path',
            'directory' => 'professional-documents/residence-permits',
        ],
    ];

    /**
     * @param  array<string, UploadedFile|null>  $files
     * @return array<string, string>
     */
    public function store(User $user, array $files): array
    {
        $record = $user->professionalDocument()->firstOrNew(['user_id' => $user->id]);
        $documents = $record->documents ?? [];
        $legacyUpdates = [];
        $disk = $this->diskName();

        foreach (self::DOCUMENT_FIELDS as $input => $metadata) {
            $file = $files[$input] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $existingPath = data_get($documents, $metadata['key'].'.path');
            $existingDisk = data_get($documents, $metadata['key'].'.disk', $disk);

            $path = $this->putDocument($user, $file, $metadata['directory'], $disk);

            if (filled($existingPath)) {
                Storage::disk($existingDisk)->delete($existingPath);
            }

            $documents[$metadata['key']] = [
                'disk' => $disk,
                'path' => $path,
                'url' => $this->documentUrl($disk, $path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toISOString(),
            ];

            $legacyUpdates[$metadata['legacy_path']] = $path;
        }

        if ($documents !== ($record->documents ?? [])) {
            $record->fill([
                'user_id' => $user->id,
                'documents' => $documents,
            ])->save();
        }

        if ($legacyUpdates !== []) {
            $user->forceFill($legacyUpdates)->save();
        }

        return $legacyUpdates;
    }

    /**
     * @return array{disk:string,path:string,original_name:string,mime_type:?string,size:?int,uploaded_at:?string,legacy_path:string}|null
     */
    public function get(User $user, string $type): ?array
    {
        $metadata = $this->metadataForType($type);
        $document = data_get($user->professionalDocument?->documents ?? [], $metadata['key']);
        $legacyPath = $user->{$metadata['legacy_path']};
        $path = data_get($document, 'path', $legacyPath);

        if (blank($path)) {
            return null;
        }

        return [
            'disk' => data_get($document, 'disk', $this->diskName()),
            'path' => $path,
            'original_name' => data_get($document, 'original_name', basename($path)),
            'mime_type' => data_get($document, 'mime_type'),
            'size' => data_get($document, 'size'),
            'uploaded_at' => data_get($document, 'uploaded_at'),
            'legacy_path' => $metadata['legacy_path'],
        ];
    }

    public function delete(User $user, string $type): bool
    {
        $metadata = $this->metadataForType($type);
        $document = $this->get($user, $type);

        if ($document === null) {
            return false;
        }

        Storage::disk($document['disk'])->delete($document['path']);

        $record = $user->professionalDocument;

        if ($record) {
            $documents = $record->documents ?? [];
            unset($documents[$metadata['key']]);
            $record->forceFill(['documents' => $documents])->save();
        }

        $user->forceFill([$metadata['legacy_path'] => null])->save();

        return true;
    }

    /**
     * @return array{key:string,legacy_path:string,directory:string}
     */
    private function metadataForType(string $type): array
    {
        $input = $type.'_document';
        abort_unless(array_key_exists($input, self::DOCUMENT_FIELDS), 404);

        return self::DOCUMENT_FIELDS[$input];
    }

    private function diskName(): string
    {
        return config('filesystems.professional_documents_disk', 'professional_documents');
    }

    private function putDocument(User $user, UploadedFile $file, string $directory, string $disk): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $filename = Str::uuid().'.'.$extension;
        $path = trim($directory, '/').'/'.$user->id.'/'.$filename;
        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        if ($sourcePath === '' || ! is_file($sourcePath)) {
            throw new RuntimeException('Unable to read the uploaded professional document.');
        }

        $stream = fopen($sourcePath, 'r');

        if ($stream === false) {
            throw new RuntimeException('Unable to open the uploaded professional document.');
        }

        try {
            $stored = Storage::disk($disk)->put($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $stored) {
            throw new RuntimeException('Unable to upload the professional document.');
        }

        return $path;
    }

    private function documentUrl(string $disk, string $path): ?string
    {
        if ($disk === 's3') {
            return Storage::disk($disk)->url($path);
        }

        return null;
    }
}
