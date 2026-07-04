# Audit documentazione AI-HTML

Data: 9 giugno 2026  
Versione valutata: 1.3.0, aggiornata a 1.4.0 durante l'audit

## Valutazione iniziale

| Area | Voto | Valutazione |
|---|---:|---|
| Architettura tecnica | 8/10 | Chiara, dettagliata e utile agli sviluppatori |
| Stato funzionalità | 8/10 | Matrice ampia, ma con alcune informazioni duplicate |
| Changelog | 8/10 | Buona tracciabilità delle feature |
| Roadmap | 6/10 | Utile, ma contiene versioni e stati non sempre sincronizzati |
| Manuale utente | 2/10 | Assente |
| Import/export operativo | 4/10 | Descritto, ma senza schema completo e procedura end-to-end |
| Media e logo remoto | 3/10 | Footer documentato; logo remoto non governato dal tema |
| Troubleshooting | 2/10 | Mancano errori comuni e diagnosi |
| Test ripetibili | 2/10 | Bug elencati ma nessun test automatico o fixture completa |
| Trasferibilità progetto | 4/10 | Menu e parte delle opzioni esportabili, ma contratto incompleto |

Valutazione complessiva iniziale: **5,2/10**.

## Punti forti

- Architettura e responsabilità dei componenti ben spiegate.
- Elenco completo delle strutture header, footer e menu rich.
- Stato di sviluppo trasparente.
- Changelog dettagliato.
- Contratto SBM esplicito.

## Lacune rilevate

1. Mancava un manuale destinato a chi deve costruire un sito.
2. La whitelist JSON copriva solo una parte dei controlli Customizer.
3. Mancavano schema, esempi completi e ordine di importazione.
4. Il logo via URL dipendeva esclusivamente da SBS.
5. L'header non utilizzava il Custom Logo WordPress come fallback.
6. Il footer non mostrava un logo immagine.
7. L'import opzioni reindirizzava alla vecchia pagina `themes.php`.
8. I campi rifiutati non erano mostrati chiaramente.
9. Non esisteva una fixture demo con tutti i media remoti.
10. Non esistevano test automatici del contratto JSON.
11. Alcuni documenti riportavano versioni o sprint non sincronizzati.
12. La lettura da terminali Windows può mostrare mojibake se non viene forzato UTF-8.

## Interventi eseguiti

- Creato `USER-GUIDE.md`.
- Creato `JSON-IMPORT-GUIDE.md`.
- Aggiunto progetto demo `remote-media-enterprise`.
- Aggiunto test `tests/options-json-test.php`.
- Estesa la whitelist a tutte le impostazioni operative.
- Aggiunti URL logo nativi e resolver unificato.
- Aggiunto logo nel footer.
- Corretto redirect Admin Hub.
- Aggiunto feedback campi rifiutati.

## Valutazione dopo gli interventi

| Area | Voto |
|---|---:|
| Architettura tecnica | 8/10 |
| Manuale utente | 8/10 |
| Import/export operativo | 9/10 |
| Media e logo remoto | 9/10 |
| Troubleshooting | 8/10 |
| Test ripetibili | 8/10 |
| Trasferibilità progetto | 8/10 |

Valutazione complessiva aggiornata: **8,3/10**.

## Limiti residui

- Manca un importatore unico che orchestri tema, menu, pagine, SBS e SBM.
- Il collaudo visuale delle otto strutture header e sette footer richiede WordPress eseguito nel browser.
- Il menu import usa ID simbolici e URL assoluti; dopo il passaggio al dominio finale può servire una sostituzione URL.
- Gli asset remoti non vengono copiati nella Media Library.
- La documentazione dei token SBM resta nel progetto SBM e deve essere consultata separatamente.

## Aggiornamento del 15 giugno 2026

La documentazione ora descrive anche:

- ownership dei componenti mobile e footer;
- separazione tra CSS strutturale e bridge SBM;
- normalizzazione delle classi Font Awesome;
- test automatici per rail, footer e fallback Bootstrap;
- procedura di cache busting tramite `AIHL_UNICODE`.

Resta raccomandata la suddivisione futura dei file amministrativi oltre 1.000 righe e di `inc/customizer/section.php`, che concentra ancora troppe responsabilita.
