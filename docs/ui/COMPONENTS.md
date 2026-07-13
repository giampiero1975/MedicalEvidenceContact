# Component Library

## Struttura

```text
resources/views/components/
├── ui/
├── layout/
├── business/
└── professional/
```

## Componenti UI base

Componenti attualmente disponibili:

- `x-ui.button`
- `x-ui.input`
- `x-ui.select`
- `x-ui.alert`
- `x-ui.badge`
- `x-ui.card`
- `x-ui.empty-state`
- `x-ui.page-header`
- `x-ui.sidebar-link`
- `x-ui.stat-card`

## Regole

1. Un componente deve avere una responsabilità chiara.
2. Le varianti devono essere esplicite, ad esempio `primary`, `secondary`, `danger`.
3. I componenti form devono mostrare automaticamente gli errori Laravel.
4. Gli attributi HTML devono poter essere estesi tramite `$attributes`.
5. Evitare classi Tailwind duplicate nelle pagine quando esiste già un componente equivalente.
6. Le nuove varianti devono essere aggiunte anche al Playground e documentate qui.

## Esempi

```blade
<x-ui.button>Salva</x-ui.button>
<x-ui.button variant="secondary">Annulla</x-ui.button>
<x-ui.button variant="danger">Elimina</x-ui.button>
```

```blade
<x-ui.input
    name="email"
    type="email"
    label="Email"
    help="Usa un indirizzo aziendale valido."
/>
```

```blade
<x-ui.card>
    <h2 class="text-lg font-semibold text-slate-950">Titolo</h2>
    <p class="mt-2 text-sm text-slate-600">Contenuto.</p>
</x-ui.card>
```

## Componenti futuri

Priorità v1:

- textarea;
- checkbox;
- radio;
- switch;
- modal;
- drawer;
- tabs;
- avatar;
- breadcrumb;
- table;
- pagination wrapper;
- stepper;
- timeline.

## Pattern di dominio

I componenti specifici non devono essere inseriti in `ui/`:

```text
business/job-card.blade.php
business/application-card.blade.php
business/interview-card.blade.php
professional/profile-completion.blade.php
professional/document-card.blade.php
professional/certification-card.blade.php
```
