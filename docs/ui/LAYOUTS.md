# Layouts

## Layout disponibili

### Guest

File:

```text
resources/views/layouts/guest.blade.php
```

Uso:

- login;
- registrazione;
- recupero password;
- pagine pubbliche di autenticazione.

### App

File:

```text
resources/views/layouts/app.blade.php
```

Uso:

- area Professional;
- area Business.

Caratteristiche:

- sidebar adattiva per ruolo;
- header sticky;
- drawer mobile;
- supporto Livewire e Alpine;
- compatibilità con lo slot `$header` Jetstream durante il refactor.

### Admin

File:

```text
resources/views/layouts/admin.blade.php
```

Uso:

- dashboard amministrativa;
- gestione utenti;
- gestione annunci;
- UI Playground.

L'area Admin è separata dai flussi pubblici e usa esclusivamente account con `users.role = admin`.

## Regole pagina

Ordine consigliato:

1. page header;
2. feedback globale;
3. metriche o riepilogo;
4. contenuto principale;
5. azioni secondarie.

Esempio:

```blade
@extends('layouts.admin')

@section('content')
    <x-ui.page-header
        title="Utenti"
        subtitle="Gestione degli account della piattaforma."
    />

    <div class="mt-8 space-y-8">
        <x-ui.card>
            ...
        </x-ui.card>
    </div>
@endsection
```

## Responsive

- Mobile first.
- Sidebar persistente da `lg` in poi.
- Drawer mobile controllato da Alpine.
- Tabelle sempre avvolte in `overflow-x-auto`.
- Azioni principali impilate su mobile e affiancate da `sm` in poi.
