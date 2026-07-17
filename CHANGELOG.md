# AI-HTML Theme - Changelog

Tutte le modifiche significative al tema sono documentate in questo file.
Formato: [Keep a Changelog](https://keepachangelog.com/). Versioning: Semantic.

---

## [1.10.4] - 2026-07-17

### Modificato
- Area amministrativa allineata al contratto Smart Admin Panel v2: header dinamico, sidebar descrittiva, pathbar e shell responsive.

### Verifica
- Aggiunto test di contratto UI contro regressioni alla navigazione legacy a tab.

## [1.10.3] - 2026-07-16

### Corretto
- L'endpoint REST di creazione pagina applica lo `slug` richiesto al campo WordPress `post_name` e restituisce lo slug effettivo, rendendo deterministiche le ricostruzioni multi-pagina in bozza.

## [1.10.2] - 2026-07-14

### Corretto
- Il comando `Controlla aggiornamenti` e ora visibile anche nella schermata Temi standard dei siti non Multisite, con nonce e verifica capability server-side.

## [1.10.1] - 2026-07-14

### Aggiunto
- Link `Controlla aggiornamenti` nelle azioni del tema, con invalidazione cache e verifica immediata del manifest Smart Repository.

## [1.10.0] - 2026-07-14

### Aggiunto
- Operazioni REST con scope `publish` per stato pagina e assegnazione della homepage WordPress.
- Discovery delle capability della Smart AI Key per pianificare la pubblicazione in sicurezza.

### Modificato
- La creazione di pagine via AI produce esclusivamente bozze; la pubblicazione richiede lo scope dedicato.
- Stato pagina e homepage espongono il pre-stato necessario al rollback transazionale del gateway.

## [1.9.2] - 2026-07-14

### Corretto
- Il ripristino governato conserva anche lo slug precedente, compreso lo slug vuoto delle bozze AI, evitando residui `__trashed-*` dopo un rollback.

## [1.9.1] - 2026-07-14

### Corretto
- Titolo `h1` e descrizione semantica nell'indice articoli per installazioni senza pagina statica pubblicata.
- Nomi accessibili per tutti i campi e pulsanti di ricerca del tema.
- A capo sicuro delle etichette nel menu mobile per evitare overflow su viewport stretti.

## [1.9.0] - 2026-07-14

### Aggiunto
- Endpoint governato `POST /wp-json/aihtml/v1/ai/pages/{id}/restore` per ripristinare dal cestino le pagine AI senza pubblicarle.
- Contratto OpenAPI e test statico per rollback pagina completamente reversibile tramite Smart AI Key.

## [1.8.9] - 2026-07-12

### Aggiunto
- Endpoint governato `DELETE /wp-json/aihtml/v1/ai/pages/{id}` per il rollback delle pagine AI non pubblicate.
- Protezione esplicita delle pagine pubblicate e contratto OpenAPI del cestino pagina.

## [1.8.8] - 2026-07-08

### Aggiunto
- Consumo del contract runtime Smart Bootstrap Manager tramite `smart_bootstrap_manager_consumer_contract('ai-html')`.
- Classi body derivate dal contract SBM per consumer, theme mode e stato motion GSAP/static.
- Documentazione tema aggiornata con endpoint `/ai/consumer-contract?consumer=ai-html`.

### Migliorato
- Bridge SBM/AI-HTML allineato al modello Bootstrap-first e motion governato da SBM.

## [1.8.7] - 2026-07-07

### Aggiunto
- Updater pubblico Smart Repository per rilevare gli aggiornamenti tema da `updates/themes/ai-html.json`.
- Workflow release GitHub per generare ZIP tema, checksum e manifest pubblico.
- Generatore OpenAPI automatico per le route REST `aihtml/v1`.
- Endpoint Swagger-compatible `/wp-json/aihtml/v1/ai/openapi` e alias `/wp-json/aihtml/v1/openapi`.
- Pagina admin `Swagger` sotto il menu AI-HTML con riepilogo endpoint e JSON OpenAPI.

## [1.8.6] - 2026-07-02

### Aggiunto
- Componente factory reset ihl:factory per ripristino tema-only di opzioni, Code Slot e cache runtime.

## [1.8.5] - 2026-07-02

### Corretto
- Reset AI-HTML reso autonomo e tema-only: non orchestra piu reset di plugin esterni.
- Rimosso il filtro tra prodotti `smart_reset_registry` dal flusso operativo.
- Endpoint REST dedicati: `GET /aihtml/v1/ai/reset/registry` e `POST /aihtml/v1/ai/reset/execute`.
- Snapshot limitato alle sole opzioni governate dal tema AI-HTML.

## [1.8.4] - 2026-07-02

### Aggiunto
- Console admin `AI-HTML > Smart Reset` per reset selettivo del tema.
- Componenti reset AI-HTML per opzioni tema, Code Slots e cache runtime.
- Snapshot JSON preventivo in `wp-content/uploads/ai-html-reset/` prima di ogni reset reale.

## [1.8.3] - 2026-07-02

### Modificato

- Rimossi gli avvisi globali dei plugin mancanti dalle schermate Articoli, Pagine e dalle altre aree amministrative WordPress.
- Spostato lo stato delle dipendenze esclusivamente nella Dashboard AI-HTML con un riepilogo compatto e separato tra plugin richiesti e consigliati.
- Mantenuta nella pagina `Plugin Dipendenze` la tabella tecnica completa con stato, percorso e funzione di ogni integrazione.

## [1.8.1] - 2026-07-01

### Aggiunto

- Preferenza box autore individuale salvata in `user_meta`.
- Pannello AI-HTML accessibile a ogni utente autenticato per il proprio stile.
- Gestione amministrativa dalla scheda utente.
- Endpoint personale `GET|POST /aihtml/v1/ai/author-profile`.
- Meta utente esposto tramite REST con autorizzazione sul proprietario.

## [1.8.0] - 2026-07-01

### Aggiunto

- Cinque nuovi preset autore: `simple`, `editorial`, `enterprise`, `impact`, `signature`.
- Selezione preset autore sincronizzata con Customizer e REST/JSON.
- Layout responsive e token visuali ereditati da Smart Bootstrap Manager.

## [1.7.1] - 2026-06-30

### Aggiunto

- Selettori espliciti `Header nativo / Header AI Canvas` e `Footer nativo / Footer AI Canvas`.
- Sincronizzazione delle sorgenti struttura con Customizer, REST API e Opzioni JSON.
- Fallback automatico al tema nativo quando lo slot Canvas selezionato non e disponibile.
- Migrazione iniziale degli override `header_full` e `footer_full` gia attivi.

## [1.6.2] - 2026-06-28

### Modificato

- Quando SBS e attivo, le sue quattro varianti logo diventano la sorgente prioritaria del contratto tema.
- Header e footer Canvas possono consumare i profili SBS tramite il componente server-side `smart-social`.

## [1.6.1] - 2026-06-28

### Corretto

- I loghi mantengono il rapporto d'aspetto con dimensioni massime responsive per header, footer, sidebar e menu mobile.
- Immagini quadrate e wordmark orizzontali usano `object-fit: contain` senza forzare contemporaneamente larghezza e altezza.

## [1.6.0] - 2026-06-27

### Aggiunto

- Introdotto lo Smart Theme Integration Contract per il dialogo tra AI-HTML, SBS, SBM e motori AI.
- Esposti manifest runtime e discovery add-on via REST API.
- Aggiunti componenti server-side `smart-logo`, `smart-menu`, `smart-social`, `smart-contact` e `smart-addon` per Code Slots e AI Canvas.
- Dichiarata la politica di fallback per logo, menu, contatti e risorse add-on.

### Modificato

- I controlli Contact Form 7 e Mailchimp rilevano le risorse disponibili e salvano esclusivamente ID numerici.
- `header_full` e `footer_full` possono consumare menu e identita configurati in WordPress senza link duplicati.

## [1.5.3] - 2026-06-26

### Corretto

- Rimosso il controllo sugli handle AOS di Smart Bootstrap Manager: AI-HTML non duplica piu librerie motion.
- Corretto il colore dei link dell'header overlay dopo lo scroll: menu principale e stati attivi restano chiari sulla toolbar scura anche con Smart Bootstrap Manager attivo.
- Reso contestuale il colore dell'header overlay dualbar: menu, social, dropdown indicator e search restano chiari sulle superfici scure non ancora scrollate.
- Limitato il bridge Smart Bootstrap Manager alle superfici non overlay/non scure, evitando override globali `!important` su header hero e footer dark.
- Reso adattivo il dropdown `directory`: i menu con poche voci diventano pannelli compatti, mentre il mega-menu resta riservato a tassonomie estese o annidate.
- Corretto il contrasto dei trigger aperti su header `gradient-fade`, inclusi stati Bootstrap `.show` e `aria-expanded`.
- Trasformato l'header hero iniziale in overlay immersivo: niente barra/blur visibile all'apertura, testo e logo leggibili tramite ombra leggera, superficie scura solo dopo scroll.
- Aggiunto test di regressione per impedire il ritorno di colori globali SBM non compatibili con overlay e dark surface.

### Aggiunto

- Aggiunto progetto demo `smart-ecommerce-footer` con slot `footer_full` importabile via Code Slots.

---

## [1.5.1] - 2026-06-18

### Corretto

- Rimossa ownership Barba.js dal tema: wrapper, asset e transizioni pagina sono gestiti da Smart Bootstrap Manager.
- Mantenuto `window.aihlInitTheme()` come hook tema per re-inizializzare header, menu, search e componenti AI-HTML dopo transizioni esterne.

## [1.5.0] - 2026-06-18

### Aggiunto

- Preparato il runtime AI-HTML per essere re-inizializzato dopo navigazioni senza full reload.

### Migliorato

- Riscritto `resource/js/main.js` con inizializzazioni idempotenti, adatte a pagine sostituite dinamicamente.
- Aggiornato versioning tema a `1.5.0` e cache key asset a `202606181500`.

## [1.4.0] - 2026-06-09

### Correzioni del 2026-06-15

- Corretto l'offcanvas mobile degli header overlay: il `backdrop-filter` della navbar non limita più il menu all'altezza dell'header.
- Forzata l'altezza dell'offcanvas a `100dvh` con fallback `100vh`.
- Corretto il contrasto delle voci mobile che potevano risultare bianche sul pannello bianco.
- Rimossa la crescita verticale del menu che spingeva le voci fuori dalla prima schermata.
- Le regole essenziali dell'offcanvas sono ora nel CSS base e funzionano senza Smart Bootstrap Manager.
- Aggiunto fallback Bootstrap CSS/JS quando nessun plugin registra Bootstrap.
- Rail e bottom bar native non vengono più generate durante un override Code Slot `header_full`.
- Rifatto il sistema submenu mobile come accordion verticale indipendente dal dropdown Bootstrap.
- Il walker genera ID univoci, `aria-controls` e un pulsante mobile dedicato per ogni voce padre.
- I submenu multilivello restano nel flusso verticale e non vengono più affiancati al link padre.
- Aggiunto test di regressione per altezza viewport e leggibilità del menu.

### Refactor runtime e footer del 2026-06-15

- Estratta la navigazione mobile in un componente runtime dedicato.
- Estratti parsing e rendering degli elementi distintivi del footer.
- Ridisegnata la fascia distintiva del footer come griglia compatta di card.
- Sostituiti i glifi legacy dei link footer con frecce CSS native.
- Normalizzate automaticamente le vecchie classi Font Awesome verso Font Awesome 6.
- Spostata la logica hover desktop dal PHP inline a `resource/js/main.js`.
- Rimosse dal bridge SBM le regole strutturali duplicate del rail mobile.

### Correzioni del 2026-06-16

- Isolato il layout desktop dell'offcanvas: a partire da 992px torna trasparente, statico e non eredita piu lo sfondo del pannello mobile.
- Riscritto il bridge SBM statico come solo mapping di token Bootstrap/SBM, senza regole di layout o breakpoint.
- Aggiunto test `desktop-header-css-test.php` per impedire nuove contaminazioni mobile su desktop.
- Rimossa dal footer nativo la fascia degli elementi distintivi/trust item, inclusi CSS, controlli Customizer, whitelist API e chiavi nei demo JSON.
- Corretto il resolver della variante logo: il logo trasparente viene usato solo con overlay hero reale, non su tutte le pagine con sticky style trasparente.
- Reso l'hamburger mobile un'icona CSS basata su `currentColor`, visibile anche su header overlay e personalizzabile tramite token SBM.
- Separata la variante logo della navbar dalla variante del pannello mobile: sulle pagine interne la navbar usa il logo default, mentre l'offcanvas mobile usa sempre la variante chiara/trasparente adatta alla testata scura.
- Allineate le icone social provenienti da SBS al comportamento visivo AI-HTML: dati gestiti dal plugin, classi e stati hover/focus governati dal tema con token Bootstrap/SBM.
- Rimosso il bordo Bootstrap dalle icone social renderizzate dal tema, mantenendo sorgente dati e attivazione in SBS.

### Refactor template nativi del 2026-06-17

- Riscritti `404.php`, `archive.php`, `category.php`, `contact.php`, `index.php`, `page.php`, `search.php` e `single.php` con struttura semantica uniforme.
- Aggiunti helper riusabili per hero template, stati vuoti e paginazione accessibile in `inc/theme/utilities.php`.
- Mantenuta la compatibilità di `category.php` con Smart Builder Site tramite blocco Builder incapsulato e fallback nativo indicizzabile.
- Aggiornata la pagina contatti con dati tema, social da SBS, shortcode form, microdata `ContactPage` e mappa sanitizzata.
- Aggiornato `single.php` con microdata `Article`, meta autore/data, immagine principale, share, tag, author box e related posts.
- Aggiunto CSS dedicato ai template nativi in `resource/css/ai-html.css` usando variabili Bootstrap/SBM senza regole strutturali invasive.
- Rivista la pagina articolo del 2026-06-22: la condivisione social ora usa un box responsive integrato, orizzontale su mobile e sticky laterale solo su desktop.
- Ridisegnato l'author box articolo come blocco E-E-A-T: bio, ruolo autore, conteggio contenuti, profilo autore, social e `author.url` nel JSON-LD.
- Aggiunta modalità menu `dropdown` con layout `directory` e `panel`: dropdown avanzati ispirati a marketplace e pannelli compatti, configurabili da menu admin e JSON.

### Import JSON e media remote

- Aggiunto supporto nativo ai logo via URL: principale, overlay, chiaro e footer.
- Definita precedenza logo: URL AI-HTML, configurazione SBS, Custom Logo WordPress, nome sito.
- Header e footer usano lo stesso resolver logo e supportano progetti senza Media Library.
- Estesa la whitelist Opzioni JSON a tutte le impostazioni operative di header, footer, mobile, contatti, moduli e media.
- Aggiunti tipi di sanitizzazione `float` e `maps_html`.
- Corretto il redirect dell'import Opzioni JSON verso l'Admin Hub.
- Aggiunto feedback per i campi JSON rifiutati.
- Aggiunti manuale utente, guida import JSON, audit documentazione, progetto demo remote-media e test automatici.
- Documentato e collaudato il sistema Code Slots con header `header_full` importabile.
- Corretto il rendering duplicato di `head_end`; `head_start` ora è realmente subito dopo il charset.
- Corretta l'interpretazione dei contesti Code Slots separati da virgola.

---

## [1.3.0] - 2026-06-06

### Sprint 6 — Template Parts e Blog

#### Template Parts creati
- **`template-parts/card-post.php`**: Card articolo riusabile con 3 layout (`horizontal`, `vertical`, `list`). Supporta thumbnail on/off, excerpt words configurabile, heading tag/class custom.
- **`template-parts/post-meta.php`**: Meta post con 3 stili (`inline`, `block`, `minimal`). Mostra data, autore, categoria primaria, tempo di lettura calcolato. Toggle per ogni campo.
- **`template-parts/author-box.php`**: Box autore con avatar, nome, bio e 5 link social (Facebook, Twitter, Instagram, YouTube, LinkedIn). Avatar size configurabile.
- **`template-parts/share-buttons.php`**: Pulsanti condivisione con 3 stili (`vertical`, `horizontal`, `minimal`). Facebook, Twitter, LinkedIn, WhatsApp + Web Share API nativa. Zero JS esterni.
- **`template-parts/related-posts.php`**: Articoli correlati per categoria con card verticali. Query `WP_Query` con random order, max 3 post. Integrato con `card-post.php`.

#### Template riscritti
- **`single.php`**: riscritto per usare template-parts (share-buttons sidebar, author-box, related-posts). Codice duplicato eliminato.
- **`search.php`**: riscritto con `card-post.php` horizontal. Aggiunto messaggio "Risultati per: query".
- **`index.php`**: riscritto con `card-post.php` list.

#### Nuovi template
- **`home.php`**: Blog index con 3 layout da Customizer:
  - `grid` — card verticali 3 colonne
  - `list` — titolo + excerpt sequenziale
  - `magazine` — primo post hero grande + griglia sotto
  Sidebar opzionale con fallback categorie/tag.
- **`archive.php`**: Archivio generico con breadcrumbs, header archivio, griglia card, sidebar opzionale.

#### Customizer
- Nuovo controllo `blog_layout` (grid/list/magazine) nella sezione Articoli.
- Nuovo toggle `blog_sidebar` per sidebar in blog e archivi.

#### Infrastruttura
- Registrata widget area `blog-sidebar` in `support.php`.
- CSS per template-parts: card hover, thumbnail aspect-ratio, post-meta separatori, author-box, related-posts, magazine hero, archive header.

### Quality (Sprint 8 parziale)
- Audit escaping completato: zero output non escaped nei template.
- Audit i18n completato: tutte le stringhe tradotte con `AIHL_TEXT_DOMAIN`.
- Nessun codice commentato significativo da rimuovere.

---

## [1.2.0] - 2026-06-06

### Aggiunto
- **Admin Hub**: menu top-level "AI-HTML" nella sidebar admin WordPress con icona SVG dedicata.
- **Dashboard admin**: panoramica stato tema (versione, header structure, footer variant, plugin attivi) con card stat e link rapidi a tutti gli strumenti + Customizer.
- **5 sottopagine unificate** sotto AI-HTML: Plugin, Menu JSON, Rich Menu Guida, Opzioni JSON, Compliance.
- **Template admin unificato** (`aihl_admin_page_template`): header scuro con logo e versione, barra tab navigazione con icone Font Awesome, area contenuto con titolo/descrizione, footer credits.
- **CSS admin dedicato**: stile coerente responsive, grid dashboard, card con hover. Caricato solo su pagine `aihl-*`.
- **Registry sottopagine**: `aihl_admin_get_subpages()` — aggiungere una pagina = 1 entry nell'array.
- Font Awesome caricato in admin per icone tab.

### Modificato
- Vecchie pagine rimosse da "Aspetto" (`remove_submenu_page` priorita 999).
- Le funzioni render originali continuano a funzionare wrappate dal template hub (titolo duplicato nascosto via CSS).

### Corretto
- **Fix critico spacing voci menu navbar**: sostituito token `--bs-nav-link-padding-x` (azzerato a `0` da Bootstrap 5.3 dentro `.navbar-nav`) con `--bs-navbar-nav-link-padding-x` (default `0.5rem`) su TUTTE le regole CSS del tema.
- Rimossa regola `.navbar .nav-link` padding dal bridge CSS (`aihl-bootstrap-bridge.css`) che sovrascriveva il tema.
- Topbar dark: colore testo ora `var(--bs-light)` con `opacity: 1` (era `#fff` hardcoded).
- Social link utility: `color: inherit` invece di `var(--bs-light)` forzato.

---

## [1.1.2] - 2026-06-03

### Aggiunto
- **Toggle visibilita** Logo, CTA e Login singolarmente da Customizer (`header_show_logo/cta/login`). Classi CSS `aihl-hide-logo`, `aihl-hide-cta`, `aihl-hide-login`.
- **Colore accento voce menu** (`_aihl_menu_color`): color picker admin per colore icona e bordo hover per singola voce.
- **Colore badge menu** (`_aihl_menu_badge_color`): sfondo badge personalizzabile con color picker.
- **Stile CTA item menu** (`_aihl_menu_item_cta`): trasforma voce in bottone (Primary, Outline, Secondary).
- **Rich layout Featured**: card con immagine grande sopra, testo sotto, icona overlay circolare.
- **Rich layout Showcase**: hero card con bg image full, gradient scuro, testo bianco sovrapposto.
- **Admin UX menu migliorata**: campi raggruppati in sezioni colorate (Comportamento blu, Visivo verde, Rich rosso). Color picker HTML5 sincronizzato.
- Preset JSON "Enterprise Full" con tutti i 7 layout + colori + badge + CTA items.
- Upload file `.json` nel form import menu (oltre al textarea).

### Modificato
- Topbar e navbar overlay coordinati su hero fullscreen per strutture dualbar e topbar-nav.
- Link top-level con flex gap esplicito (icona, testo, badge separati).
- Admin menu fields: descrizioni e placeholder in italiano.

---

## [1.1.1] - 2026-06-03

### Aggiunto
- **Struttura Triple Row** (`header_structure = triple-row`): Utility bar + Brand bar (logo+search+login+CTA) + Nav bar dedicata. Stile IBM, Deloitte, SAP, PwC.
- **Struttura Stacked Centered** (`header_structure = stacked-centered`): Utility bar + Logo grande centrato (display-6) + tagline + Nav centrata. Stile NYTimes, The Guardian.
- **Brand Bar componente** (`aihl-brand-bar`): condiviso tra triple-row e stacked-centered. Nascosta su mobile.
- **Sticky Gradient Fade** (`header_sticky_style = gradient-fade`): gradient scuro dall'alto che sfuma a trasparente su hero foto/video. Stile Apple/Nike.

---

## [1.1.0] - 2026-06-03

### Aggiunto
- **Struttura Topbar + Navbar** (`topbar-nav`): topbar utility scroll-away + navbar sticky.
- **Struttura Mega Centered** (`mega-centered`): menu sx + logo centrato + menu dx.
- **Struttura Sidebar** (`sidebar`): barra verticale fissa 280px con fallback mobile.
- **Search desktop**: 3 stili (icon-dropdown, icon-fullscreen, inline) + toggle off.
- **Sticky style**: solid, blur (glassmorphism), transparent.
- **Topbar scroll-away**: si nasconde allo scroll giu.
- **Rich layout Grid**: card 3 colonne centrate.
- **Rich layout Tabbed**: item come tab card con bordo.
- **Rich CTA button**: bottone in fondo al mega menu (label+url per voce parent).
- **Rich footer slot**: HTML nella parte inferiore del mega menu.
- **Menu locations**: `topic_left`, `topic_right` (mega-centered), `footer_col_1..4` (mega-footer).
- **Footer Mega Footer**: multi-colonna via 4 menu locations. Colonne configurabili 3-5.
- **Footer Minimal**: riga singola copyright + link + social.
- **Footer CTA Footer**: hero CTA (titolo + sottotitolo + 2 bottoni) sopra enterprise.
- **Trust bar dinamica**: fino a 5 item da Customizer (icona FA + testo).
- **Bottom bar mobile**: barra inferiore fissa stile app (Home, Search, Menu, CTA, Account).
- **Mobile rail evoluta**: aggiunto bottone telefono.
- 3 stili mobile: `rail`, `bottom-bar`, `none`.
- Hook `aihl/header/topbar/right` per estensioni plugin.
- Customizer: search style, topbar scroll, sticky style, mobile nav style, footer CTA/trust/colonne.
- `CHANGELOG.md`, `DEVELOPMENT-STATUS.md` creati.

### Corretto
- Fix `esc_html()` con argomento extra in `support.php`.
- Sincronizzazione versione `style.css` / `functions.php`.

---

## [1.0.10] - 2026-05-29

### Aggiunto
- Rich mega menu: layout split, compact, columns.
- Campi menu: icon, badge, subtitle, eyebrow, image, highlight.
- JSON import/export menu con 3 preset.
- Header: strutture standard, dualbar, centered. Nav layout clean/pills/underline/compact.
- Header: overlay auto/always/never con blur e opacity. Mobile rail con autohide.
- Footer: varianti enterprise, futuristic, corporate, compact. Background con overlay.
- Bridge Smart Bootstrap Manager con CSS dinamico inline.
- Conditional asset loading (animate, owl, fontawesome brands).
- Google Compliance 2026 integration.
- Menu help page. Admin media picker per immagini menu.

---

## [1.0.0] - 2026-05

### Aggiunto
- Tema base Bootstrap con template WordPress standard.
- Integrazione Smart Builder Site (slot system).
- Integrazione Smart Customizer Framework.
- Customizer: Sito, Articoli, Contatti, Contact Form, Mailchimp.
- Template: index, single, page, search, category, 404, about, contact.
# 1.8.2

- Aggiunta copertina tema `screenshot.png` 1200x900 per la schermata Aspetto > Temi.
- Allineati i metadati di compatibilita a WordPress 7.0 e PHP 7.4.
- Rafforzata la descrizione enterprise e AI-ready del prodotto.
- Inclusi nel pacchetto tutti i preset Author Box globali e per utente.
- Consolidata la documentazione distributiva con `readme.txt`.
