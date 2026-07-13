# Medical UI Kit — Colors

## Principi

La palette usa Tailwind CSS senza colori personalizzati in questa fase. I token devono essere scelti per funzione, non per gusto locale della singola pagina.

## Token principali

| Funzione | Token base | Uso |
|---|---|---|
| Primary | `teal-700` | azioni principali, navigazione attiva, focus |
| Neutral | `slate-*` | testo, bordi, superfici, layout |
| Success | `emerald-600` | completato, attivo, verificato |
| Warning | `amber-600` | attenzione, verifica richiesta |
| Danger | `rose-600` | errore, eliminazione, scadenza |
| Info | `sky-600` | comunicazioni informative |

## Regole

- Testo principale: `slate-950` o `slate-900`.
- Testo secondario: `slate-600`.
- Testo di supporto: `slate-500`.
- Bordi standard: `slate-200` o `slate-300` nei form.
- Superficie applicativa: `slate-50`.
- Card: sfondo bianco con bordo `slate-200`.
- Focus interattivo: famiglia `teal`.
- Il colore non deve essere l'unico indicatore di stato: usare anche testo, icona o label.
