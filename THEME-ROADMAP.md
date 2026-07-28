# AI-HTML Theme - Roadmap di sviluppo

Versione di riferimento: 1.12.4
Ultimo aggiornamento: 28 luglio 2026

## Ruolo del tema

AI-HTML e la shell WordPress dello Smart eCommerce Stack. Governa struttura,
presentazione, identita, navigazione, integrazioni runtime e API del tema.

Confini di responsabilita:

| Componente | Responsabilita |
|---|---|
| AI-HTML | Header, footer, menu, template, Customizer, Canvas e API strutturali |
| Smart Bootstrap Manager | Bootstrap, token, tipografia, componenti, motion e design governance |
| Smart Builder Site | Contenuto pagina, widget Builder/Compose, revisioni e rendering |
| Smart Customizer Frameworks | Controlli Customizer avanzati e relativo catalogo |

CPT applicativi, autenticazione, area membri e regole di accesso non devono
essere implementati nel tema. AI-HTML puo fornire esclusivamente template e
integrazioni per plugin proprietari di tali domini.

## 1.12.4 - Stabilizzazione Canvas

Stato: in rilascio.

- Editor amministrativo dedicato per Canvas Header e Footer.
- Editor HTML, CSS e JavaScript a schede con CodeMirror.
- Diagnostica di slot, fallback nativo e risoluzione menu.
- Stato Canvas disponibile nella dashboard e nella REST API.
- Schema OpenAPI concreto per diagnostica e stato Canvas.
- Collegamento diretto dal Customizer all'editor dello slot.
- CI su PHP minimo supportato e PHP corrente.
- Costruzione e validazione preventiva del pacchetto di release.

Criteri di uscita:

- suite PHP completa senza errori;
- sorgenti compatibili con PHP 7.4;
- versione sincronizzata tra header, costante e stable tag;
- pacchetto privo di test, strumenti e file di sviluppo;
- fallback nativo sempre disponibile per Canvas non valido.

## 1.13.0 - Customizer modulare

- Conservare i pannelli incapsulati e la gerarchia corrente.
- Separare il file sezioni per dominio: Header, Footer, Blog, Identita e Integrazioni.
- Usare il registro opzioni come sorgente unica per sanitizzazione, Customizer e API.
- Definire un contratto condiviso per pannelli nidificati con SCF.
- Conservare un adapter interno per il funzionamento standalone.
- Aggiungere test browser per apertura pannelli, salvataggio e anteprima.

Criterio di uscita: nessuna sezione piatta fuori dal pannello AI-HTML e nessuna
divergenza tra opzioni Customizer e API.

## 1.14.0 - Canvas Governance

- Versionare lo schema dei Canvas.
- Validare componenti runtime, menu, logo e dipendenze prima dell'attivazione.
- Aggiungere anteprima isolata, snapshot e rollback.
- Applicare le modalita SBM `governed`, `adaptive` e `autonomous`.
- Impedire l'attivazione di un Canvas strutturalmente invalido.
- Aggiungere test visuali per header e footer su desktop e mobile.

Criterio di uscita: un Canvas invalido non puo sostituire la struttura nativa.

## 1.15.0 - Modularizzazione API

- Separare route, controller, schema e servizi di dominio.
- Ridurre i moduli principali a responsabilita singola.
- Eliminare gli schemi OpenAPI generici dalle operazioni gestionali.
- Aggiungere `dry_run` e idempotenza a deploy, import e reset.
- Introdurre test di contratto con SBM, SBS e SCF.
- Verificare automaticamente la copertura API di tutte le opzioni gestibili.

Criterio di uscita: ogni funzione amministrativa, salvo il bootstrap delle
credenziali, e disponibile tramite API autenticata e documentata.

## 1.16.0 - Frontend e prestazioni

- Migrare WOW.js e Owl Carousel verso componenti SBS/SBM o alternative native.
- Ridurre `!important` e colori non tokenizzati.
- Caricare asset per template e capacita effettivamente utilizzate.
- Verificare immagini responsive e Core Web Vitals.
- Aggiungere budget Lighthouse sulle pagine di riferimento.

Criterio di uscita: Lighthouse mobile almeno 90 su home, archivio e articolo.

## 1.17.0 - Accessibilita e SEO

- Audit WCAG 2.2 AA.
- Test automatici tastiera, focus, menu multilivello e reduced motion.
- Breadcrumb e dati strutturati senza duplicazioni con plugin SEO attivi.
- Verifica semantica dei componenti Canvas e dei template editoriali.

## 2.0 - Pulizia architetturale

- Rimuovere wrapper legacy dopo un ciclo documentato di deprecazione.
- Introdurre autoload e namespace per i nuovi servizi.
- Aggiornare i requisiti minimi con una procedura di migrazione.
- Versionare formalmente i contratti tra tema e plugin.
- Mantenere compatibilita dei dati e rollback durante l'aggiornamento.

## Gate globali

- Tema attivabile senza plugin opzionali.
- Header e footer nativi sempre disponibili come fallback.
- Menu mai selezionati in modo ambiguo.
- Customizer gerarchico verificato nel browser.
- API e OpenAPI prive di riferimenti irrisolti.
- Pacchetto, checksum, manifest e download pubblico verificati.
- Documentazione e versione aggiornate nello stesso commit della release.
