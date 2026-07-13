# Medical UI Kit — Forms

## Componenti canonici

- `<x-ui.input>`
- `<x-ui.select>`
- `<x-ui.textarea>`
- `<x-ui.checkbox>`
- `<x-ui.button>`
- `<x-ui.alert>` per errori generali

## Regole

- Ogni controllo deve avere una label visibile.
- Il testo di aiuto compare sotto il campo e non sostituisce la label.
- Gli errori devono essere associati al campo e leggibili anche senza colore.
- I campi obbligatori devono essere validati lato server; Livewire o Alpine migliorano la UX ma non sostituiscono Laravel validation.
- I form lunghi vanno suddivisi in sezioni o step.
- L'azione primaria va a destra nelle toolbar desktop e resta facilmente raggiungibile su mobile.
- Le azioni distruttive richiedono conferma esplicita.

## Layout

- Una colonna su mobile.
- Due colonne solo quando i campi sono logicamente accoppiati.
- Spaziatura standard tra campi: `space-y-5` oppure `gap-5`.
- Footer azioni separato con bordo superiore nelle card complesse.
