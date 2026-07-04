# AI-HTML - Rapporto di refactor tecnico

Data: 17 giugno 2026  
Versione: 1.4.0

## Obiettivo

Ridurre le correzioni stratificate, assegnare una responsabilita chiara ai componenti runtime e prevenire regressioni tra header, navigazione mobile, Smart Bootstrap Manager e footer.

## Dimensione rilevata

- 70 file PHP, circa 10.700 righe.
- 17 file CSS inclusi asset vendor, circa 5.200 righe.
- 11 file JavaScript inclusi asset vendor, circa 3.900 righe.
- File applicativi piu grandi: `inc/admin/code-slots.php`, `inc/admin/deploy-projects.php`, `inc/customizer/section.php`, `inc/theme/menu-json.php`.

## Interventi completati

1. Navigazione mobile estratta da `header.php` in `inc/theme/mobile-navigation.php`.
2. Configurazione rail, bottom bar e offcanvas validata in un solo punto.
3. Regole strutturali del rail consolidate in `resource/css/ai-html.css`.
4. Bridge SBM limitato all'adattamento dei token.
5. Hover menu desktop spostato dal PHP inline a `resource/js/main.js`.
6. Footer proof points rimossi dal rendering nativo del footer.
7. Classi Font Awesome legacy normalizzate verso Font Awesome 6.
8. Marker link footer sostituiti con frecce CSS native.
9. Fascia proof del footer eliminata per ridurre rumore visivo e duplicazione informativa.
10. Aggiunti test di regressione dedicati.
11. Isolato l'offcanvas desktop con reset esplicito a breakpoint `min-width: 992px`.
12. Riscritto `aihl-bootstrap-bridge.css` come layer solo-token senza regole strutturali.
13. Riscritti i template nativi principali (`404`, `archive`, `category`, `contact`, `index`, `page`, `search`, `single`) con markup uniforme.
14. Estratti helper frontend per hero template, paginazione e stati vuoti in `inc/theme/utilities.php`.
15. Consolidato il CSS dei template nativi in `resource/css/ai-html.css`, usando token Bootstrap/SBM e senza bridge correttivi.

## Principi applicati

- Un componente possiede markup, comportamento e contratto CSS.
- I template orchestrano; non duplicano parsing o logica.
- I template nativi restano indicizzabili anche quando SBS non renderizza contenuti aggiuntivi.
- I contenuti di archivi, ricerca e single usano template-parts riusabili per card, meta, share, author e related.
- Il bridge di integrazione non deve correggere la struttura del tema.
- Il bridge di integrazione non deve cambiare stato desktop/mobile dei componenti.
- Le decorazioni non semantiche non dipendono da icon font.
- Le opzioni vuote non producono contenuto di fallback generico.
- Ogni modifica CSS o JavaScript aggiorna `AIHL_UNICODE`.

## Debito tecnico residuo

Priorita alta:

- dividere `inc/admin/code-slots.php` e `inc/admin/deploy-projects.php` in controller, servizi e view;
- dividere `inc/customizer/section.php` per area funzionale;
- aggiungere test WordPress integrati per Customizer, REST e import JSON.

Priorita media:

- eliminare il mojibake storico nei documenti e nei commenti;
- verificare e aggiornare i requisiti WordPress/PHP dichiarati in `style.css`;
- ridurre selettori `!important` dopo un audit visuale di tutte le varianti.

## Criteri di rilascio

- lint PHP senza errori;
- sintassi JavaScript valida;
- tutti i test in `tests/*-test.php` verdi;
- nessuna regola strutturale rail nel bridge SBM;
- nessun glifo Font Awesome legacy nei marker footer;
- nessuna regola offcanvas, rail o positioning nel bridge SBM;
- archivio ZIP e hash SHA-256 generati dopo i test.
