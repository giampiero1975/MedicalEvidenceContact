<?php

namespace App\Services\Moodle;

use App\Models\MoodleUserLink;
use App\Models\UserCertificate;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MoodleCertificateSyncService
{
    public function __construct(
        private readonly MoodleFlowLogger $logger
    ) {
    }

    /** @return array{received:int,saved:int,driver:string,trace_id:string} */
    public function sync(MoodleUserLink $link): array
    {
        $flow = 'certificate_sync';
        $startedAt = microtime(true);
        $traceId = $this->logger->begin($flow, [
            'moodle_user_link_id' => $link->id,
            'laravel_user_id' => $link->laravel_user_id,
            'moodle_site_id' => $link->moodle_site_id,
            'moodle_user_id' => $link->moodle_user_id,
            'link_status' => $link->status,
        ]);

        try {
            abort_unless($link->status === 'active', 422, 'Il collegamento Moodle non è attivo.');

            $link->loadMissing('moodleSite');
            $driver = $link->moodleSite->certificate_sync_driver ?: 'disabled';

            $this->logger->step($traceId, $flow, 'driver.resolved', [
                'driver' => $driver,
                'site_name' => $link->moodleSite->name,
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            $client = MoodleApiClient::forUserLink($link);
            $this->logger->step($traceId, $flow, 'moodle_api.sync.started', ['driver' => $driver]);

            $payload = match ($driver) {
                'local_plugin', 'local_laravelcertsync' => $client->getUserCertificatesViaLocalPlugin($link),
                'customcert', 'mod_customcert', 'native_mod_customcert' => $client->getCustomcertIssuesForLinkedUser($link),
                default => throw new RuntimeException("Driver sincronizzazione attestati non supportato: {$driver}"),
            };

            $this->logger->step($traceId, $flow, 'moodle_api.sync.completed', [
                'driver' => $driver,
                'payload_top_level_keys' => array_keys($payload),
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            $items = $this->extractItems($payload);
            $courseIndex = $this->buildCourseIndex($client, $link, $traceId, $flow);
            $saved = 0;
            $skipped = 0;

            $this->logger->step($traceId, $flow, 'certificates.extracted', [
                'received' => $items->count(),
                'course_matches_available' => count($courseIndex),
            ]);

            foreach ($items as $index => $item) {
                $item = $this->enrichWithCourse($item, $courseIndex, $traceId, $flow);

                if ($this->persist($link, $item, $traceId, $flow)) {
                    $saved++;
                    continue;
                }

                $skipped++;
                $this->logger->warning($traceId, $flow, 'certificate.skipped', [
                    'index' => $index,
                    'available_keys' => array_keys($item),
                    'reason' => 'missing_issue_id_and_certificate_code',
                ]);
            }

            $now = now();
            $link->forceFill(['last_certificate_sync_at' => $now])->save();
            $link->moodleSite->forceFill(['last_certificate_sync_at' => $now])->save();

            $result = [
                'received' => $items->count(),
                'saved' => $saved,
                'driver' => $driver,
                'trace_id' => $traceId,
            ];

            $this->logger->success($traceId, $flow, [
                ...$result,
                'skipped' => $skipped,
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->logger->failure($traceId, $flow, 'flow.failed', $exception, [
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            throw $exception;
        }
    }

    /** @return Collection<int, array<string, mixed>> */
    private function extractItems(array $payload): Collection
    {
        $items = $payload['certificates'] ?? $payload['issues'] ?? $payload['data'] ?? $payload;

        if (! is_array($items)) {
            return collect();
        }

        return collect($items)->filter(fn ($item) => is_array($item))->values();
    }

    /** @return array<int, array<string, mixed>> */
    private function buildCourseIndex(
        MoodleApiClient $client,
        MoodleUserLink $link,
        string $traceId,
        string $flow
    ): array {
        try {
            $this->logger->step($traceId, $flow, 'course_enrichment.started');
            $courses = $client->getUserCourses((int) $link->moodle_user_id);
            $index = [];

            foreach ($courses as $course) {
                if (! is_array($course) || blank($course['id'] ?? null)) {
                    continue;
                }

                $courseId = (int) $course['id'];
                $sections = $client->getCourseContents($courseId);

                foreach ($sections as $section) {
                    foreach (($section['modules'] ?? []) as $module) {
                        if (($module['modname'] ?? null) !== 'customcert' || blank($module['instance'] ?? null)) {
                            continue;
                        }

                        $index[(int) $module['instance']] = [
                            'course' => [
                                'id' => $courseId,
                                'fullname' => $course['fullname'] ?? null,
                                'shortname' => $course['shortname'] ?? null,
                            ],
                            'coursemoduleid' => isset($module['id']) ? (int) $module['id'] : null,
                            'certificate' => [
                                'name' => $module['name'] ?? null,
                            ],
                        ];
                    }
                }
            }

            $this->logger->step($traceId, $flow, 'course_enrichment.completed', [
                'courses_received' => count($courses),
                'customcert_modules_indexed' => count($index),
            ]);

            return $index;
        } catch (Throwable $exception) {
            $this->logger->warning($traceId, $flow, 'course_enrichment.unavailable', [
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /** @param array<string, mixed> $item @param array<int, array<string, mixed>> $courseIndex */
    private function enrichWithCourse(array $item, array $courseIndex, string $traceId, string $flow): array
    {
        $customcertId = $this->int($item, ['issue.customcertid', 'customcertid', 'customcert_id', 'certificateid']);

        if (! $customcertId || ! isset($courseIndex[$customcertId])) {
            $this->logger->warning($traceId, $flow, 'certificate.course_not_resolved', [
                'customcert_id' => $customcertId,
                'issue_id' => $this->int($item, ['issue.id', 'issueid', 'issue_id', 'id']),
            ]);

            return $item;
        }

        $enrichment = $courseIndex[$customcertId];
        $item['course'] = $enrichment['course'];
        $item['coursemoduleid'] = $enrichment['coursemoduleid'];
        $item['certificate'] = array_filter([
            'name' => $enrichment['certificate']['name'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $this->logger->step($traceId, $flow, 'certificate.course_resolved', [
            'customcert_id' => $customcertId,
            'course_id' => $item['course']['id'] ?? null,
            'course_module_id' => $item['coursemoduleid'] ?? null,
        ]);

        return $item;
    }

    /** @param array<string, mixed> $item */
    private function persist(MoodleUserLink $link, array $item, string $traceId, string $flow): bool
    {
        $issueId = $this->int($item, ['issue.id', 'issueid', 'issue_id', 'id']);
        $certificateCode = $this->string($item, ['issue.code', 'code', 'certificatecode', 'certificate_code']);

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
        $storedPdfPath = $this->storePdf($link, $item, $issueId, $certificateCode, $traceId, $flow);

        $rawPayload = $item;
        if (isset($rawPayload['pdf']['content'])) {
            $rawPayload['pdf']['content'] = null;
            $rawPayload['pdf']['haspdf'] = filled($storedPdfPath);
        }

        $certificate->fill([
            'moodle_customcert_id' => $this->int($item, ['issue.customcertid', 'customcertid', 'customcert_id', 'certificateid']),
            'moodle_customcert_issue_id' => $issueId,
            'moodle_course_module_id' => $this->int($item, ['coursemoduleid', 'course_module_id', 'cmid']),
            'moodle_context_id' => $this->int($item, ['template.contextid', 'contextid', 'context_id']),
            'course_id' => $this->int($item, ['course.id', 'courseid', 'course_id']),
            'course_fullname' => $this->string($item, ['course.fullname', 'coursefullname', 'course_fullname', 'fullname']),
            'course_shortname' => $this->string($item, ['course.shortname', 'courseshortname', 'course_shortname', 'shortname']),
            'certificate_name' => $this->string($item, ['certificate.name', 'certificatename', 'certificate_name', 'template.name', 'name']),
            'template_id' => $this->int($item, ['template.id', 'templateid', 'template_id']),
            'template_name' => $this->string($item, ['template.name', 'templatename', 'template_name']),
            'certificate_code' => $certificateCode,
            'issued_at' => $this->date($item, ['issue.timecreated', 'timecreated', 'issuedat', 'issued_at', 'issueddate']),
            'expires_at' => $this->date($item, ['issue.expiresat', 'expiresat', 'expires_at', 'expirydate']),
            'download_url' => $this->string($item, ['pdf.url', 'downloadurl', 'download_url']),
            'verification_url' => $this->string($item, ['verificationurl', 'verification_url', 'verifyurl']),
            'verification_is_public' => (bool) ($item['verification_is_public'] ?? $item['verificationispublic'] ?? false),
            'pdf_stored_path' => $storedPdfPath ?: $certificate->pdf_stored_path,
            'raw_payload_json' => $rawPayload,
        ]);

        $certificate->save();

        return true;
    }

    /** @param array<string, mixed> $item */
    private function storePdf(
        MoodleUserLink $link,
        array $item,
        ?int $issueId,
        ?string $certificateCode,
        string $traceId,
        string $flow
    ): ?string {
        $encoded = $this->string($item, ['pdf.content', 'pdfcontent', 'pdf_content']);

        if (blank($encoded)) {
            return null;
        }

        $binary = base64_decode($encoded, true);
        if ($binary === false || ! str_starts_with($binary, '%PDF-')) {
            $this->logger->warning($traceId, $flow, 'certificate.pdf_invalid', [
                'issue_id' => $issueId,
                'certificate_code' => $certificateCode,
            ]);

            return null;
        }

        $fileKey = $issueId ?: preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $certificateCode);
        $path = "moodle-certificates/{$link->laravel_user_id}/{$link->moodle_site_id}/{$fileKey}.pdf";

        Storage::disk('local')->put($path, $binary);

        $this->logger->step($traceId, $flow, 'certificate.pdf_stored', [
            'issue_id' => $issueId,
            'path' => $path,
            'bytes' => strlen($binary),
        ]);

        return $path;
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
            return is_numeric($value) ? Carbon::createFromTimestamp((int) $value) : Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function elapsedMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
