# Integrazione Moodle

## Obiettivo

Il modulo collega un profilo Professional a un account Moodle verificato e sincronizza attestati `mod_customcert`, PDF e dati del corso associato.

## Flusso applicativo

1. Il Professional seleziona un sito Moodle e cerca l'account per email o username.
2. Laravel usa `core_user_get_users_by_field`.
3. Il codice di verifica viene inviato all'email restituita da Moodle.
4. Dopo la conferma viene creato un collegamento `moodle_user_links` con stato `active`.
5. La sincronizzazione usa `mod_customcert_list_issues` con `includepdf=1`.
6. Il PDF Base64 viene verificato e salvato nello storage privato Laravel.
7. I corsi dell'utente vengono letti tramite `core_enrol_get_users_courses`.
8. I contenuti dei corsi vengono letti tramite `core_course_get_contents`.
9. Il modulo `customcert` viene associato confrontando `module.instance` con `issue.customcertid`.
10. Il record `user_certificates` viene aggiornato senza duplicazioni usando l'issue ID Moodle.

## Funzioni Moodle richieste

Il servizio esterno associato al token deve esporre:

```text
core_webservice_get_site_info
core_user_get_users
core_user_get_users_by_field
core_enrol_get_users_courses
core_course_get_contents
mod_customcert_list_issues
```

Dopo aver aggiunto o rimosso funzioni dal servizio web, svuotare tutte le cache Moodle:

```text
Amministrazione del sito → Sviluppo → Svuota tutte le cache
```

Da CLI Moodle:

```bash
php admin/cli/purge_caches.php
```

## Driver attestati

Il campo `moodle_sites.certificate_sync_driver` accetta:

```text
native_mod_customcert
local_laravelcertsync
disabled
```

La configurazione verificata usa:

```text
native_mod_customcert
```

## Storage PDF

I PDF sono salvati sul disco Laravel `local`:

```text
moodle-certificates/{laravel_user_id}/{moodle_site_id}/{issue_id}.pdf
```

Il contenuto Base64 non viene mantenuto in `raw_payload_json`.

Visualizzazione e download passano da rotte autenticate e verificano la proprietà dell'attestato.

## Logging

Il canale dedicato scrive in:

```text
storage/logs/moodle-YYYY-MM-DD.log
```

Per seguire il flusso:

```bash
tail -f storage/logs/moodle-$(date +%Y-%m-%d).log
```

Eventi principali:

```text
flow.started
driver.resolved
moodle_api.sync.started
moodle_api.sync.completed
course_enrichment.started
course_enrichment.completed
certificate.course_resolved
certificate.pdf_stored
flow.completed
flow.failed
```

## Collegamento bypass per sviluppo

Disponibile soltanto negli ambienti `local` e `testing`:

```bash
php artisan moodle:link-bypass EMAIL_UTENTE_MEDICAL ID_O_USERNAME_MOODLE --site=1
```

Per sostituire un collegamento Moodle già esistente in locale:

```bash
php artisan moodle:link-bypass EMAIL_UTENTE_MEDICAL ID_O_USERNAME_MOODLE --site=1 --force
```

Il bypass non deve essere usato in produzione.

## Diagnostica

Eseguire:

```bash
php artisan moodle:diagnose --site=1 --user=2701
```

Per forzare l'ispezione di un corso specifico:

```bash
php artisan moodle:diagnose --site=1 --user=2701 --course=40
```

Il comando verifica:

```text
Site info
Funzioni web service richieste
User lookup
Corsi dell'utente
Contenuti del corso e presenza customcert
Attestati
Disponibilità PDF
```

Non stampa token, password, contenuti PDF o dati sensibili completi.

## Configurazione verificata

Esempio del matching completato:

```text
moodle_user_id: 2701
moodle_customcert_id: 48
moodle_customcert_issue_id: 4734
moodle_course_module_id: 872
course_id: 40
course_fullname: Sterilizzazione dello strumentario chirurgico
certificate_name: Attestato di Partecipazione
```

## Comportamento non bloccante

Se l'enrichment dei corsi non è disponibile, l'attestato e il PDF vengono comunque sincronizzati. Il problema viene registrato nel log Moodle e i campi del corso restano null fino a una sincronizzazione successiva.
