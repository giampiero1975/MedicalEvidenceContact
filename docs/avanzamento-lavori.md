# Avanzamento lavori

Documento operativo per tracciare lo stato del progetto rispetto alla documentazione in `docs/`.

Fonti:
- `prdMedicalEvidenceContact.txt`
- `pdrFINALE.txt`
- `Software Requirement Document (SRD).txt`

Legenda:
- `[x]` completato
- `[~]` parziale / da rifinire
- `[ ]` da fare

## Business

### Registrazione e autenticazione

- [x] Registrazione account business pubblico.
  - Riferimenti: RF-008, RF-010, US-003.
- [~] POC principale in registrazione.
  - Riferimenti: RF-009, RF-011.
  - Stato: il POC e gestito dopo il login con funzione dedicata `Aggiungi Point of Contact`.
- [x] Login business con redirect all'area operativa.
  - Riferimenti: RF-012, RF-013.
- [ ] Email di conferma registrazione al POC principale.
  - Riferimenti: RF-011, RF-059, RF-060.

### Profilo aziendale

- [~] Anagrafica azienda: nome, tipo, localita, numero dipendenti.
  - Riferimenti: RF-008, RF-020.
  - Da fare: indirizzo completo, partita IVA, descrizione azienda.
- [x] Aggiunta Point of Contact da area business.
  - Riferimenti: RF-021, US-015.
- [ ] Modifica POC esistente.
  - Riferimenti: RF-021.
- [ ] Eliminazione POC, impedendo la rimozione dell'unico POC.
  - Riferimenti: RF-021.
- [ ] Designazione POC principale.
  - Riferimenti: RF-021.
- [ ] Credenziali accesso per POC aggiunti.
  - Riferimenti: RF-022, RF-023.

### Annunci

- [x] Creazione annuncio da business.
  - Riferimenti: RF-027, RF-028, RF-029, US-003.
  - Campi coperti: titolo, descrizione, numero posizioni, indirizzo sede lavoro, abilita richieste, tipo contratto, range retribuzione, data scadenza, stato.
- [x] CRUD annunci business.
  - Riferimenti: RF-031, RF-032, US-014.
- [~] Gestione stati attivo/scaduto.
  - Riferimenti: RF-032, RF-034.
  - Da fare: chiusura anticipata, archiviazione e viste attivi/scaduti/tutti.
- [ ] Blocco eliminazione annunci con candidature attive o archiviazione alternativa.
  - Riferimenti: RF-033.
- [ ] Email conferma pubblicazione annuncio al POC creatore.
  - Riferimenti: RF-030, RF-059.
- [ ] Campi avanzati annuncio: categoria professionale, benefit, requisiti preferenziali, orario lavoro, editor rich text.
  - Riferimenti: RF-027.

### Candidature ricevute

- [x] Visualizzazione profili candidati senza email e telefono.
  - Riferimenti: RF-044, RF-045, US-006, RNF-014.
- [~] Lista candidature per annuncio.
  - Riferimenti: RF-044.
  - Da fare: filtri per stato, conteggi e aggregazione dashboard.
- [ ] Cambio stato candidatura a visualizzata.
  - Riferimenti: RF-044.
- [ ] Rifiuto candidatura con email automatica al professionista.
  - Riferimenti: RF-046, RF-059.
- [~] Invito a colloquio da candidatura.
  - Riferimenti: RF-047, RF-049, US-007.
  - Stato: frontend presente nella lista candidature business; mancano salvataggio slot, invio email e cambio stato candidatura.

### Colloqui e calendario

- [~] Creazione slot colloquio da business.
  - Riferimenti: RF-048, US-007, INT-001.
  - Stato: interfaccia frontend con data, ora inizio/fine e modalita; mancano modello dati, controller, validazione e integrazione calendario.
- [ ] Invio slot al professionista con link diretto.
  - Riferimenti: RF-049, RF-050.
- [~] Conferma/rifiuto slot scelto dal professionista.
  - Riferimenti: RF-052, RF-053.
  - Stato: frontend professionista con scelta slot e pulsante conferma non attivo; manca persistenza e gestione richieste business.
- [~] Stati colloquio: richiesto, accettato, rifiutato, completato, annullato.
  - Riferimenti: RF-055.
  - Stato: stati rappresentati in UI; manca tabella/stato reale nel database e transizioni lato backend.
- [ ] Riprogrammazione e annullamento colloquio.
  - Riferimenti: RF-056, RF-057, US-016.
- [~] Sblocco contatti solo a colloquio accettato e con consenso.
  - Riferimenti: RF-054, US-009, US-010, RNF-014.
  - Stato: avviso frontend presente e contatti ancora non esposti; manca logica di consenso/sblocco.

### Dashboard business

- [~] Area annunci business.
  - Riferimenti: RF-064.
  - Stato: gestione annunci presente.
- [ ] Riepilogo dashboard: annunci attivi, candidature totali, colloqui settimana, annunci in scadenza.
  - Riferimenti: RF-064.
- [ ] Tab candidature aggregate.
  - Riferimenti: RF-064.
- [~] Tab colloqui.
  - Riferimenti: RF-064.
  - Stato: primo blocco colloquio presente nella lista candidature business; manca tab dedicato aggregato.
- [~] Tab/area POC.
  - Riferimenti: RF-064, RF-065.

## Professionista

### Registrazione e autenticazione

- [x] Registrazione pubblico professionista.
  - Riferimenti: RF-004, RF-005, US-001.
- [x] Nazionalita con lista selezionabile.
  - Riferimenti: requisito progetto su nazionalita.
- [x] Indirizzo strutturato: citta, paese, provincia, CAP, indirizzo.
  - Riferimenti: RF-004.
- [ ] Categoria professionale: OSS, Infermiere, Anestesista, Fisioterapista.
  - Riferimenti: RF-004, RF-027.
- [ ] Email di verifica registrazione.
  - Riferimenti: RF-006, RF-059.

### Profilo professionista

- [~] Modifica dati anagrafici e indirizzo.
  - Riferimenti: RF-015.
- [x] Esperienze professionali CRUD.
  - Riferimenti: RF-016.
- [x] Formazione CRUD.
  - Riferimenti: RF-016.
- [ ] Lingue conosciute CRUD.
  - Riferimenti: RF-016.
- [ ] Preferenze impiego: struttura, contratto, automunito, disponibilita geografica.
  - Riferimenti: RF-004.
- [ ] Visibilita profilo pubblico/privato.
  - Riferimenti: RF-018.
- [ ] Anteprima profilo come visto dalle aziende, senza contatti.
  - Riferimenti: RF-019, RNF-014.

### Documenti e attestati

- [x] Upload attestato ATA dal form professionista.
  - Riferimenti: RF-016, INT-005.
- [x] Upload permesso di soggiorno solo per nazionalita diversa da italiana.
  - Riferimenti: requisito progetto su nazionalita/documenti.
- [x] Tabella documenti professionista `professional_documents`.
  - Riferimenti: INT-005.
  - Struttura: `user_id` e `documents` JSON.
- [x] Salvataggio path/link documenti AWS S3 nel JSON.
  - Riferimenti: INT-005.
  - JSON: disk, path, url, original_name, mime_type, size, uploaded_at.
- [ ] URL firmati temporanei per download sicuro.
  - Riferimenti: INT-005.
- [ ] Separazione attestati piattaforma e attestati esterni.
  - Riferimenti: RF-017, US-002.
- [ ] Import automatico attestati da piattaforma corsi/MOODLE.
  - Riferimenti: RF-007, INT-003, US-002.
- [ ] Metadati attestati esterni: nome corso, ente erogatore, data conseguimento.
  - Riferimenti: RF-004, INT-005.

### Ricerca e consultazione annunci

- [x] Visualizzazione annunci attivi nella pagina principale professionista.
  - Riferimenti: RF-035, RF-037, RF-039.
- [~] Dettaglio annuncio.
  - Riferimenti: RF-039.
- [~] Filtri ricerca annunci: keyword, localita, tipo contratto, categoria azienda, range retribuzione, categoria professionale, data pubblicazione.
  - Riferimenti: RF-035, US-004.
  - Stato: filtri frontend/backend presenti; categoria professionale cercata su titolo e competenze finche non esiste un campo dedicato.
- [ ] Paginazione risultati 20 per pagina.
  - Riferimenti: RF-036.
- [ ] Badge nuova pubblicazione ultimi 3 giorni.
  - Riferimenti: RF-037.
- [ ] Salvataggio annunci preferiti.
  - Riferimenti: RF-038, US-013.

### Candidature

- [x] Candidatura a un annuncio.
  - Riferimenti: RF-040, RF-042, US-005.
- [x] Prevenzione candidatura duplicata allo stesso annuncio.
  - Riferimenti: RF-043.
- [x] Lista candidature nella dashboard professionista.
  - Riferimenti: RF-042, RF-062.
- [~] Stato candidatura visibile.
  - Riferimenti: RF-042, RF-044.
- [ ] Modal candidatura con riepilogo profilo, messaggio presentazione e checkbox conferma.
  - Riferimenti: RF-041.
- [ ] Email conferma candidatura al professionista.
  - Riferimenti: RF-042, RF-059.
- [ ] Email nuova candidatura al business.
  - Riferimenti: RF-042, RF-059.

### Colloqui

- [~] Ricezione invito a colloquio con lista slot.
  - Riferimenti: RF-050, RF-051, US-008.
  - Stato: frontend dashboard professionista presente con slot dimostrativi; manca recupero inviti reali.
- [~] Selezione slot e invio richiesta al business.
  - Riferimenti: RF-052.
  - Stato: selezione rappresentata in UI con pulsante disabilitato; manca endpoint di conferma.
- [~] Visualizzazione colloqui in dashboard.
  - Riferimenti: RF-062.
  - Stato: sezione "Colloqui" aggiunta alla dashboard professionista.
- [ ] Riprogrammazione/annullamento colloquio accettato.
  - Riferimenti: RF-056, US-016.
- [~] Visualizzazione contatti solo quando colloquio accettato.
  - Riferimenti: RF-054, US-010, RNF-014.
  - Stato: avviso privacy presente e contatti non renderizzati; manca sblocco condizionale su stato accettato.

### Dashboard professionista

- [~] Dashboard con annunci e candidature.
  - Riferimenti: RF-062.
- [ ] Riepilogo: candidature inviate, colloqui in programma, inviti ricevuti, completamento profilo.
  - Riferimenti: RF-062.
- [ ] Tab candidature con filtri.
  - Riferimenti: RF-062.
- [~] Tab colloqui.
  - Riferimenti: RF-062.
- [~] Tab inviti ricevuti.
  - Riferimenti: RF-062.
- [ ] Tab annunci salvati.
  - Riferimenti: RF-062.
- [ ] Badge notifiche non lette.
  - Riferimenti: RF-063.

## Note tecniche trasversali

- [x] Laravel 12 con Jetstream/Livewire.
- [x] MySQL Laragon configurato su `127.0.0.1:3306`.
- [x] Pacchetto S3 installato: `league/flysystem-aws-s3-v3`.
- [~] Config AWS S3 predisposta in `.env`.
  - Da fare: inserire credenziali reali AWS.
- [ ] Notifiche email transazionali complete.
  - Riferimenti: RF-059, RF-060, INT-002.
- [ ] Preferenze notifiche per categoria/frequenza.
  - Riferimenti: RF-061.
- [ ] Export GDPR e cancellazione dati.
  - Riferimenti: RNF-015.
- [ ] Logging, backup e policy retention secondo privacy.
  - Riferimenti: RNF-016, RNF-018, RNF-025.

## Ultimo aggiornamento

- Data: 2026-06-23
- Ambiente principale: `C:\laragon\www\MedicalEvidenceContact`
- Stato applicativo ultimo controllo: `http://medicalevidencecontact.test` raggiungibile
