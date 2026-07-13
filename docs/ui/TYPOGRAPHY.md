# Medical UI Kit — Typography

## Font

Il font applicativo è Figtree, caricato dai layout condivisi.

## Scala

| Livello | Classi consigliate | Uso |
|---|---|---|
| Eyebrow | `text-xs font-semibold uppercase tracking-[0.18em]` | contesto e categorie |
| Page title | `text-3xl font-semibold tracking-tight` | titolo principale pagina |
| Section title | `text-2xl font-semibold tracking-tight` | sezioni principali |
| Card title | `text-lg font-semibold` | card e pannelli |
| Body | `text-sm leading-6` | testo applicativo standard |
| Help text | `text-sm text-slate-500` | istruzioni e supporto |
| Label | `text-sm font-medium text-slate-700` | campi form |

## Regole

- Un solo `h1` semantico per pagina.
- La gerarchia visiva deve seguire quella HTML.
- Evitare testo tutto maiuscolo salvo eyebrow e badge brevi.
- Le descrizioni lunghe usano `leading-6` per leggibilità.
- Non ridurre il testo applicativo sotto `text-sm`, salvo metadata e badge.
