<?php

namespace App\Console\Commands;

use App\Models\MoodleSite;
use App\Models\MoodleUserLink;
use App\Models\User;
use App\Services\Moodle\MoodleApiClient;
use Illuminate\Console\Command;

class MoodleLinkBypassCommand extends Command
{
    protected $signature = 'moodle:link-bypass
        {user : Email o ID dell utente Laravel Professional}
        {moodle-user : Email, username o ID dell utente Moodle}
        {--site=1 : ID del sito Moodle}
        {--force : Riassegna un collegamento Moodle già attivo}';

    protected $description = 'Crea in locale un collegamento Moodle attivo bypassando la verifica email';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Il bypass è consentito esclusivamente negli ambienti local e testing.');
            return self::FAILURE;
        }

        $userValue = (string) $this->argument('user');
        $professional = User::query()
            ->where('email', $userValue)
            ->when(is_numeric($userValue), fn ($query) => $query->orWhereKey((int) $userValue))
            ->first();

        if (! $professional || $professional->role !== 'professional') {
            $this->error('Utente Laravel Professional non trovato.');
            return self::FAILURE;
        }

        $site = MoodleSite::query()->whereKey((int) $this->option('site'))->where('enabled', true)->first();
        if (! $site) {
            $this->error('Sito Moodle attivo non trovato.');
            return self::FAILURE;
        }

        $lookup = (string) $this->argument('moodle-user');
        $field = is_numeric($lookup) ? 'id' : (str_contains($lookup, '@') ? 'email' : 'username');
        $users = (new MoodleApiClient($site))->getUsersByField($field, $lookup);

        if (count($users) !== 1 || blank($users[0]['id'] ?? null)) {
            $this->error('La ricerca Moodle non ha restituito un solo utente valido.');
            return self::FAILURE;
        }

        $moodleUser = $users[0];
        $conflict = MoodleUserLink::query()
            ->where('moodle_site_id', $site->id)
            ->where('moodle_user_id', $moodleUser['id'])
            ->where('status', 'active')
            ->first();

        if ($conflict && $conflict->laravel_user_id !== $professional->id && ! $this->option('force')) {
            $this->error("L account Moodle è già collegato all utente Laravel {$conflict->laravel_user_id}. Usa --force solo in locale.");
            return self::FAILURE;
        }

        if ($conflict && $this->option('force')) {
            $conflict->delete();
        }

        MoodleUserLink::query()->updateOrCreate(
            [
                'laravel_user_id' => $professional->id,
                'moodle_site_id' => $site->id,
            ],
            [
                'moodle_user_id' => $moodleUser['id'],
                'moodle_idnumber' => $moodleUser['idnumber'] ?? null,
                'moodle_username' => $moodleUser['username'] ?? null,
                'moodle_email' => $moodleUser['email'] ?? null,
                'linked_via' => 'local_test_bypass',
                'linked_at' => now(),
                'last_verified_at' => now(),
                'status' => 'active',
            ]
        );

        $this->info("Collegamento creato: Laravel {$professional->id} → Moodle {$moodleUser['id']} ({$site->name}).");
        return self::SUCCESS;
    }
}
