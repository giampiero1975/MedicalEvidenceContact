<?php

namespace App\Console\Commands;

use App\Models\MoodleSite;
use App\Services\Moodle\MoodleApiClient;
use Throwable;
use Illuminate\Console\Command;

class DiagnoseMoodleIntegration extends Command
{
    protected $signature = 'moodle:diagnose
        {--site= : ID del sito Moodle}
        {--user= : ID utente Moodle da verificare}
        {--course= : ID corso opzionale da ispezionare}';

    protected $description = 'Verifica configurazione, funzioni web service, corsi e attestati di un sito Moodle';

    public function handle(): int
    {
        $siteId = (int) $this->option('site');
        $moodleUserId = (int) $this->option('user');

        if ($siteId < 1 || $moodleUserId < 1) {
            $this->error('Specificare --site e --user con ID numerici validi.');
            $this->line('Esempio: php artisan moodle:diagnose --site=1 --user=2701');

            return self::INVALID;
        }

        $site = MoodleSite::query()->find($siteId);

        if (! $site) {
            $this->error("Sito Moodle {$siteId} non trovato.");

            return self::FAILURE;
        }

        $client = new MoodleApiClient($site);
        $results = [];
        $courses = [];
        $certificatePayload = [];

        $siteInfo = $this->runCheck('Site info', function () use ($client) {
            return $client->getSiteInfo();
        }, $results);

        if (is_array($siteInfo)) {
            $availableFunctions = collect($siteInfo['functions'] ?? [])->pluck('name');
            $requiredFunctions = [
                'core_user_get_users_by_field',
                'core_enrol_get_users_courses',
                'core_course_get_contents',
                'mod_customcert_list_issues',
            ];

            $missing = collect($requiredFunctions)->reject(fn (string $function) => $availableFunctions->contains($function));
            $results[] = [
                'Funzioni richieste',
                $missing->isEmpty() ? 'OK' : 'ERRORE',
                $missing->isEmpty() ? 'Tutte disponibili' : 'Mancano: '.$missing->join(', '),
            ];
        }

        $this->runCheck('User lookup', function () use ($client, $moodleUserId) {
            $users = $client->getUserById($moodleUserId);

            if (count($users) !== 1) {
                throw new \RuntimeException('Utente non trovato o risposta non univoca.');
            }

            return $users;
        }, $results);

        $coursesResult = $this->runCheck('User courses', function () use ($client, $moodleUserId) {
            return $client->getUserCourses($moodleUserId);
        }, $results);

        if (is_array($coursesResult)) {
            $courses = $coursesResult;
        }

        $courseId = (int) ($this->option('course') ?: ($courses[0]['id'] ?? 0));

        if ($courseId > 0) {
            $this->runCheck("Course contents ({$courseId})", function () use ($client, $courseId) {
                $sections = $client->getCourseContents($courseId);
                $customcertModules = collect($sections)
                    ->flatMap(fn (array $section) => $section['modules'] ?? [])
                    ->where('modname', 'customcert')
                    ->values();

                if ($customcertModules->isEmpty()) {
                    throw new \RuntimeException('Nessun modulo customcert trovato nel corso.');
                }

                return $customcertModules->all();
            }, $results);
        } else {
            $results[] = ['Course contents', 'SKIP', 'Nessun corso disponibile o --course non specificato'];
        }

        $certificatePayload = $this->runCheck('Certificates', function () use ($client, $moodleUserId) {
            return $client->callFunction('mod_customcert_list_issues', [
                'userid' => $moodleUserId,
                'includepdf' => 1,
                'limit' => 100,
                'offset' => 0,
            ]);
        }, $results);

        if (is_array($certificatePayload)) {
            $items = $certificatePayload['issues'] ?? $certificatePayload['data'] ?? $certificatePayload;
            $hasPdf = collect(is_array($items) ? $items : [])->contains(
                fn ($item) => is_array($item) && (bool) data_get($item, 'pdf.haspdf', false)
            );

            $results[] = [
                'PDF availability',
                $hasPdf ? 'OK' : 'WARN',
                $hasPdf ? 'Almeno un PDF restituito' : 'Nessun PDF presente nella risposta',
            ];
        }

        $this->newLine();
        $this->table(['Controllo', 'Esito', 'Dettaglio'], $results);

        $hasErrors = collect($results)->contains(fn (array $row) => $row[1] === 'ERRORE');

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }

    private function runCheck(string $label, callable $callback, array &$results): mixed
    {
        try {
            $result = $callback();
            $count = is_countable($result) ? count($result) : null;
            $results[] = [$label, 'OK', $count !== null ? "Elementi: {$count}" : 'Risposta valida'];

            return $result;
        } catch (Throwable $exception) {
            $results[] = [$label, 'ERRORE', $exception->getMessage()];

            return null;
        }
    }
}
