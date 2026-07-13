# Accessibilità

## Baseline

Medical Evidence Contact adotta WCAG 2.1 AA come riferimento operativo per le nuove interfacce.

## Requisiti minimi

- ogni campo form deve avere una label associata;
- gli errori devono essere leggibili e collegati al campo;
- il focus da tastiera deve essere sempre visibile;
- il colore non deve essere l'unico mezzo per comunicare uno stato;
- i pulsanti devono avere testo comprensibile;
- le tabelle devono usare intestazioni semantiche;
- i modali futuri devono gestire focus trap, chiusura con Escape e ritorno del focus;
- i link attivi devono essere identificabili anche senza affidarsi solo al colore;
- testi e controlli devono mantenere contrasto sufficiente.

## Form

Preferire i componenti `x-ui.input` e `x-ui.select`, che includono:

- label;
- messaggio di aiuto;
- stato errore;
- focus ring coerente.

## Navigazione

- usare elementi `nav` con `aria-label`;
- preservare un ordine DOM logico;
- non rendere disponibile una funzione solo tramite hover;
- il drawer mobile deve poter essere chiuso da tastiera.

## Test manuale minimo

Prima di considerare verificata una schermata:

1. navigare usando solo Tab e Shift+Tab;
2. verificare la visibilità del focus;
3. aumentare lo zoom browser al 200%;
4. verificare mobile a 320 px;
5. controllare che errori e stati siano comprensibili senza colore.
