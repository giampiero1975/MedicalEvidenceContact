<?php

namespace App\Services\Moodle;

use App\Models\MoodleUserLink;
use App\Models\UserCertificate;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class MoodleCertificateSyncService
{
    /**
     * @return array{received:int,saved:int,driver:string}
     */
    public function sync(MoodleUserLink $link): array
    {
        abort_unless($link->status === 'active', 422, 'Il collegamento Moodle non è attivo.');

        $link->loadMissing('moodleSite');
        $driver = $link->moodleSite->certificate_sync_driver ?: 'local_plugin';
        $client = MoodleApiClient::forUserLink($link);

        $payload = match ($driver) {
            'local_plugin', 'local_laravelcertsync' => $client->getUserCertificatesViaLocalPlugin($link),
            'customcert', 'mod_customcert' => $client->getCustomcertIssuesForLinkedUser($link),
            default => throw new RuntimeException("Driver sincronizzazione attestati non supportato: {$driver}"),
        };

        $items = $this->extractItems($payload);
        $saved = 0;

        foreach ($items as $item) {
            if ($this->persist($link, $item)) {
                $saved++;
            }
        }

        $now = now();
        $link->forceFill(['last_certificate_sync_at' => $now])->save();
        $link->moodleSite->forceFill(['last_certificate_sync_at' => $now])->save();

        return [
            'received' => $items->count(),
            'saved' => $saved,
            'driver' => $driver,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function extractItems(array $payload): Collection
    {
        $items = $payload['certificates']
            ?? $payload['issues']
            ?? $payload['data']
            ?? $payload;

        if (! is_array($items)) {
            return collect();
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->values();
    }

    /** @param array<string, mixed> $item */
    private function persist(MoodleUserLink $link, array $item): bool
    {
        $issueId = $this->int($item, ['issueid', 'issue_id', 'id']);
        $certificateCode = $this->string($item, ['code', 'certificatecode', 'certificate_code']);

        if (! $issueId && ! $certificateCode) {
            return false;
        }

        $identity = [
            'laravel_user_id' => $link->laravel_user_id,
            'moodle_site_id' => $link->moodle_site_id,
            'moodle_user_id' => $link->moodle_user_id,
        ];

        $query = UserCertificate::query()->where($identity);
        $issueId
            ? $query->where('moodle_customcert_issue_id', $issueId)
            : $query->where('certificate_code', $certificateCode);

        $certificate = $query->first() ?? new UserCertificate($identity);

        $certificate->fill([
            'moodle_customcert_id' => $this->int($item, ['customcertid', 'customcert_id', 'certificateid']),
            'moodle_customcert_issue_id' => $issueId,
            'moodle_course_module_id' => $this->int($item, ['coursemoduleid', 'course_module_id', 'cmid']),
            'moodle_context_id' => $this->int($item, ['contextid', 'context_id']),
            'course_id' => $this->int($item, ['courseid', 'course_id']),
            'course_fullname' => $this->string($item, ['coursefullname', 'course_fullname', 'fullname']),
            'course_shortname' => $this->string($item, ['courseshortname', 'course_shortname', 'shortname']),
            'certificate_name' => $this->string($item, ['certificatename', 'certificate_name', 'name']),
            'template_id' => $this->int($item, ['templateid', 'template_id']),
            'template_name' => $this->string($item, ['templatename', 'template_name']),
            'certificate_code' => $certificateCode,
            'issued_at' => $this->date($item, ['timecreated', 'issuedat', 'issued_at', 'issueddate']),
            'expires_at' => $this->date($item, ['expiresat', 'expires_at', 'expirydate']),
            'download_url' => $this->string($item, ['downloadurl', 'download_url']),
            'verification_url' => $this->string($item, ['verificationurl', 'verification_url', 'verifyurl']),
            'verification_is_public' => (bool) ($item['verification_is_public'] ?? $item['verificationispublic'] ?? false),
            'raw_payload_json' => $item,
        ]);

        $certificate->save();

        return true;
    }

    /** @param array<string, mixed> $item */
    private function string(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($item, $key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $item */
    private function int(array $item, array $keys): ?int
    {
        $value = $this->string($item, $keys);
        return is_numeric($value) ? (int) $value : null;
    }

    /** @param array<string, mixed> $item */
    private function date(array $item, array $keys): ?Carbon
    {
        $value = $this->string($item, $keys);

        if ($value === null) {
            return null;
        }

        try {
            return is_numeric($value)
                ? Carbon::createFromTimestamp((int) $value)
                : Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
