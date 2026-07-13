<?php

namespace App\Services\Moodle;

use App\Models\MoodleSite;
use App\Models\MoodleUserLink;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class MoodleApiClient
{
    public function __construct(
        private readonly MoodleSite $moodleSite,
        private readonly bool $verifySsl = true
    ) {
    }

    public static function forUserLink(MoodleUserLink $moodleUserLink): self
    {
        return new self($moodleUserLink->moodleSite);
    }

    public function getSiteInfo(): array
    {
        return $this->call('core_webservice_get_site_info');
    }

    public function callFunction(string $function, array $parameters = []): array
    {
        return $this->call($function, $parameters);
    }

    public function getUserByEmail(string $email): array
    {
        return $this->getUsersByField('email', $email);
    }

    public function getUserByUsername(string $username): array
    {
        return $this->getUsersByField('username', $username);
    }

    public function getUserById(int $moodleUserId): array
    {
        return $this->getUsersByField('id', (string) $moodleUserId);
    }

    public function getUsersByField(string $field, string $value): array
    {
        $response = $this->call('core_user_get_users_by_field', [
            'field' => $field,
            'values' => [$value],
        ]);

        return collect($response)
            ->map(fn (array $user): array => [
                'id' => $user['id'] ?? null,
                'username' => $user['username'] ?? null,
                'email' => $user['email'] ?? null,
                'fullname' => $user['fullname'] ?? trim(($user['firstname'] ?? '').' '.($user['lastname'] ?? '')) ?: null,
                'idnumber' => $user['idnumber'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function getUserCourses(int $moodleUserId): array
    {
        return $this->call('core_enrol_get_users_courses', [
            'userid' => $moodleUserId,
            'returnusercount' => 0,
        ]);
    }

    public function getCourseContents(int $courseId): array
    {
        return $this->call('core_course_get_contents', [
            'courseid' => $courseId,
        ]);
    }

    public function getCustomcertIssuesForLinkedUser(
        MoodleUserLink $moodleUserLink,
        ?int $timecreatedFrom = null,
        int $limit = 100,
        int $offset = 0
    ): array {
        return $this->call('mod_customcert_list_issues', array_filter([
            'userid' => $moodleUserLink->moodle_user_id,
            'timecreatedfrom' => $timecreatedFrom,
            'includepdf' => 1,
            'limit' => min($limit, 500),
            'offset' => $offset,
        ], fn ($value) => $value !== null));
    }

    public function getUserCertificatesViaLocalPlugin(
        MoodleUserLink $moodleUserLink,
        ?int $timecreatedFrom = null,
        int $limit = 100,
        int $offset = 0
    ): array {
        return $this->call('local_laravelcertsync_get_user_certificates', array_filter([
            'userid' => $moodleUserLink->moodle_user_id,
            'timecreatedfrom' => $timecreatedFrom,
            'includepdf' => 1,
            'limit' => min($limit, 500),
            'offset' => $offset,
        ], fn ($value) => $value !== null));
    }

    public function getCertificatePdf(MoodleUserLink $moodleUserLink, int $issueId): array
    {
        return $this->call('local_laravelcertsync_get_certificate_pdf', [
            'userid' => $moodleUserLink->moodle_user_id,
            'issueid' => $issueId,
        ]);
    }

    private function call(string $function, array $parameters = []): array
    {
        try {
            $response = Http::asForm()
                ->when(! $this->verifySsl, fn ($pendingRequest) => $pendingRequest->withoutVerifying())
                ->timeout(30)
                ->post($this->endpoint(), [
                    'wstoken' => $this->moodleSite->api_token_encrypted,
                    'wsfunction' => $function,
                    'moodlewsrestformat' => 'json',
                    ...$parameters,
                ])
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            throw MoodleApiException::connection($function, $exception);
        }

        if (isset($response['exception'])) {
            throw MoodleApiException::moodle(
                $function,
                $response['message'] ?? 'Errore restituito da Moodle.',
                $response['exception'] ?? null
            );
        }

        return is_array($response) ? $response : [];
    }

    private function endpoint(): string
    {
        return rtrim($this->moodleSite->base_url, '/').'/webservice/rest/server.php';
    }
}
