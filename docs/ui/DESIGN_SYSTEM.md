# Medical Evidence Contact — Design System

## Scopo

Il Medical UI Kit è il riferimento unico per tutte le interfacce pubbliche, Professional, Business e Admin.

Obiettivi:

- coerenza visiva;
- riuso dei componenti;
- accessibilità;
- riduzione del codice duplicato;
- evoluzione controllata della UI.

## Stack UI approvato

- Blade;
- Livewire 3;
- Alpine.js;
- Tailwind CSS;
- Jetstream/Fortify per autenticazione e account.

Non vengono introdotti React, Vue, Inertia o ulteriori scaffolding UI.

## Principi

1. **Chiarezza prima della decorazione**: ogni schermata deve rendere evidente l'azione principale.
2. **Un solo Design System**: Professional, Business e Admin condividono componenti e token.
3. **Composizione**: le pagine si costruiscono assemblando componenti, non copiando markup.
4. **Gerarchia prevedibile**: page header, sezioni, card, azioni e feedback seguono sempre lo stesso ordine.
5. **Accessibilità by default**: contrasto, focus, label e navigazione da tastiera sono requisiti, non rifiniture.
6. **Progressive enhancement**: le funzioni principali restano server-driven; Livewire e Alpine migliorano l'esperienza.

## Palette iniziale

| Ruolo | Token Tailwind | Uso |
|---|---|---|
| Primary | `teal-700` | CTA principali, navigazione attiva |
| Neutral | `slate-*` | testi, bordi, sfondi |
| Success | `emerald-*` | conferme, stati attivi |
| Warning | `amber-*` | verifiche, scadenze imminenti |
| Danger | `rose-*` | errori, eliminazioni |
| Info | `sky-*` | informazioni contestuali |

## Tipografia

Font principale: Figtree.

- Page title: `text-3xl font-semibold tracking-tight`.
- Section title: `text-2xl font-semibold tracking-tight`.
- Card title: `text-lg font-semibold`.
- Body: `text-sm leading-6`.
- Eyebrow: `text-xs font-semibold uppercase tracking-[0.18em]`.

## Spaziatura

- Pagina: `px-4 sm:px-6 lg:px-8`.
- Sezione principale: `space-y-8`.
- Card: padding standard `p-6`.
- Gap griglie: `gap-6` o `gap-8`.
- Form: `space-y-5` o `space-y-6`.

## Border radius

Lo standard del prodotto è `rounded-xl`. Le eccezioni devono essere motivate.

## Stato

Versione corrente: **UI Framework v1 — Foundation**.
