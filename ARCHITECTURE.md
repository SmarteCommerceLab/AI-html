# AI-HTML Theme — Architettura tecnica

Versione: 1.5.1  
Ultimo aggiornamento: 22 giugno 2026

---

## Scopo del tema

AI-HTML e il tema base dell'ecosistema Smart eCommerce. Funziona come fondazione per costruire temi enterprise WordPress. Non e un tema stand-alone: e progettato per integrarsi con:

- **Smart Bootstrap Manager** (SBM) — token CSS runtime, palette, tipografia, layout, effetti
- **Smart Customizer Framework** (SCF) — controlli Customizer avanzati (Builder, Compose, toggle, repeater)
- **Smart Builder Site** (SBS) — page builder corporate e blog compose

Il tema puo funzionare senza questi plugin (fallback Bootstrap standard), ma l'esperienza completa richiede almeno SBM.

---

## Confine con UI/FX e page transitions

AI-HTML non e il runtime degli effetti. Quando Smart Bootstrap Manager e attivo:

- Bootstrap, Barba.js, Chart.js e l'intero framework GSAP sono governati da SBM;
- il tema fornisce contenitori stabili, header/footer e reinizializzazione dei propri comportamenti dopo transizioni pagina;
- il tema non deve duplicare Bootstrap ne inizializzare librerie UI/FX gia gestite da SBM;
- il contenuto critico deve restare nel markup server-side prodotto da WordPress/SBS.
## Flusso di avvio (Bootstrap Flow)

```
functions.php
  └── inc/core/bootstrap.php
        └── inc/core/loader.php (aihl_require_files)
              ├── inc/option.php
              ├── inc/activation.php
              ├── inc/output-cleanup.php
              ├── inc/resource.php
              ├── inc/required-plugins.php
              ├── inc/admin/admin-hub.php
              ├── inc/customizer/panel.php
              ├── inc/customizer/section.php
              ├── inc/customizer/reset.php
              ├── inc/theme/support.php
              ├── inc/theme/menu.php
              ├── inc/theme/menu-fields.php
              ├── inc/theme/menu-help.php
              ├── inc/theme/menu-json.php
              ├── inc/theme/menu-walker.php
              ├── inc/theme/image-size.php
              ├── inc/theme/post-occhiello.php
              ├── inc/theme/utilities.php
              ├── inc/theme/options-json.php
              ├── inc/integrations/smart-bootstrap-manager.php
              ├── inc/integrations/google-compliance-2026.php
              ├── inc/integrations/seo.php
              ├── inc/integrations/ai-auth-core.php
              └── inc/integrations/ai-api.php
```

---

## Mappa cartelle

| Cartella | Responsabilita |
|---|---|
| `inc/core/` | Boot e orchestrazione caricamento file |
| `inc/admin/` | Admin Hub: menu top-level AI-HTML, dashboard, template e stile unificati per tutte le pagine admin |
| `inc/` | Runtime WordPress: option system, activation, resource enqueue, required plugins |
| `inc/customizer/` | Customizer: panel, sections (header/footer/sito/articoli/contatti/mailchimp), reset, sanitizzazione |
| `inc/theme/` | Comportamento tema: theme support, menu system (walker/fields/help/json), utilities, image sizes, post helpers |
| `inc/integrations/` | Bridge verso plugin: Smart Bootstrap Manager, SEO, Google Compliance 2026, AI Auth, AI API |
| `template-parts/` | Blocchi riusabili: card-post (3 layout), post-meta (3 stili), author-box, share-buttons (3 stili), related-posts |
| `resource/css/` | CSS frontend: `ai-html.css` (tema principale), `aihl-bootstrap-bridge.css` (bridge SBM), `aihl-menu-walker.css` (mega menu) |
| `resource/js/` | JavaScript frontend: `main.js` (scroll, search, mobile menu, topbar) |
| `resource/img/` | Immagini statiche tema |
| `resource/css/fontawesome/` | Font Awesome 6.4.2 (solid + brands) |
| `lib/` | **Legacy** — wrapper di compatibilita verso `inc/theme/*`. Non usare per nuovo codice |
| `customizer/` | **Legacy** — wrapper di compatibilita verso `inc/customizer/*`. Non usare per nuovo codice |

---

## Ordine caricamento CSS frontend

```
1. smart-bootstrap          (SBM — Bootstrap + token runtime inline)
2. ai-html-theme            (tema principale — priorita 99)
3. aihl-menu-walker         (mega menu CSS — dopo tema)
4. aihl-bootstrap-bridge    (bridge SBM statico — priorita 120)
   └── inline: bridge CSS dinamico da aihl_build_bootstrap_bridge_css()
5. animate/owl/brands       (condizionali)
```

---

## Sistema header — 8 strutture

| Struttura | Customizer key | Descrizione | Riferimento |
|---|---|---|---|
| `standard` | `header_structure` | Logo sx + nav + CTA dx | Stripe, Linear, Vercel |
| `dualbar` | | Topbar dark + navbar | Enterprise custom |
| `centered` | | Logo centrato | Basic centered |
| `topbar-nav` | | Topbar utility scroll-away + navbar sticky | Salesforce, HubSpot |
| `mega-centered` | | Menu sx + logo centrato + menu dx | Vogue, Gucci |
| `sidebar` | | Barra verticale fissa 280px | Notion app, AWS Console |
| `triple-row` | | Utility + Brand bar (logo+search+CTA) + Nav bar | IBM, Deloitte, PwC |
| `stacked-centered` | | Utility + Logo grande centrato + Nav centrata | NYTimes, The Guardian |

### Componenti header condivisi

- **Topbar** (`aihl-topbar`): usata da dualbar, topbar-nav, triple-row, stacked-centered. Variante dark per dualbar. Scroll-away configurabile.
- **Brand Bar** (`aihl-brand-bar`): usata da triple-row e stacked-centered. Logo + azioni (triple-row) o logo centrato + tagline (stacked-centered). Nascosta su mobile.
- **Offcanvas**: menu mobile condiviso da tutte le strutture. Posizione left/right configurabile.
- **Logo resolver** (`aihl_get_site_logo_data`): usa URL AI-HTML, logo SBS, Custom Logo WordPress e infine nome sito.

### Sticky/Overlay styles

| Stile | Effetto |
|---|---|
| `solid` | Sfondo opaco standard |
| `blur` | Glassmorphism (semitrasparente + backdrop-filter) |
| `transparent` | Trasparente fino a scroll, poi solido |
| `gradient-fade` | Gradient scuro alto→trasparente (Apple/Nike) |

### Search desktop

| Stile | Effetto |
|---|---|
| `none` | Disabilitato |
| `icon-dropdown` | Icona → barra slide-down |
| `icon-fullscreen` | Icona → overlay scuro fullscreen |
| `inline` | Campo input inline nella navbar |

### Toggle visibilita

Logo, CTA e Login sono singolarmente disattivabili da Customizer via classi CSS (`aihl-hide-logo`, `aihl-hide-cta`, `aihl-hide-login`).

---

## Sistema menu — Walker e campi

### AIHL_Nav_Menu_Walker

Walker personalizzato che estende `Walker_Nav_Menu`. Supporta profondita 3 livelli.

### Modi menu per voce top-level

| Modo | Comportamento |
|---|---|
| Auto (vuoto) | Rich se ha figli, simple altrimenti |
| `simple` | Dropdown classico Bootstrap |
| `rich` | Mega menu full-width |

### 7 layout rich

| Layout | Descrizione |
|---|---|
| `split` | Lista link + pannello laterale HTML |
| `compact` | Lista densa + pannello ridotto |
| `columns` | 2 colonne senza pannello |
| `grid` | Card 3 colonne centrate con icona grande |
| `tabbed` | Item come tab card con bordo |
| `featured` | Card con immagine grande sopra + icona overlay |
| `showcase` | Hero card con bg image + gradient scuro + testo bianco |

### Campi per singola voce menu

| Campo | Meta key | Tipo | Dove appare |
|---|---|---|---|
| Icona | `_aihl_menu_icon` | text (classe FA) | Qualsiasi livello |
| Badge | `_aihl_menu_badge` | text | Dropdown items |
| Colore badge | `_aihl_menu_badge_color` | hex | Dropdown items |
| Sottotitolo | `_aihl_menu_subtitle` | text | Dropdown items |
| Sopratitolo (eyebrow) | `_aihl_menu_eyebrow` | text | Dropdown items |
| Immagine | `_aihl_menu_image` / `_aihl_menu_image_id` | url / int | Rich items |
| Evidenzia | `_aihl_menu_highlight` | bool | Qualsiasi |
| Colore accento | `_aihl_menu_color` | hex | Qualsiasi — icona e hover |
| Stile CTA | `_aihl_menu_item_cta` | select | Qualsiasi — trasforma in bottone |

### Campi per voce parent (livello 0)

| Campo | Meta key | Tipo |
|---|---|---|
| Layout rich | `_aihl_menu_rich_layout` | select |
| Pannello laterale HTML | `_aihl_menu_rich_content` | html |
| CTA label mega menu | `_aihl_menu_rich_cta_label` | text |
| CTA URL mega menu | `_aihl_menu_rich_cta_url` | url |
| Footer mega menu | `_aihl_menu_rich_footer` | html |

### Menu locations

| Location | Uso |
|---|---|
| `topic` | Menu principale navbar |
| `utili` | Footer link utili |
| `naviga` | Topbar utility |
| `footer` | Footer (legacy) |
| `topic_left` | Mega centered — menu sinistro |
| `topic_right` | Mega centered — menu destro |
| `footer_col_1..4` | Mega footer — colonne |

### JSON Import/Export

Pagina admin dedicata con:
- Export singolo menu o tutti → file `.json`
- Import da file upload o textarea
- Sostituzione menu esistenti
- 4 preset dimostrativi (Simple, Rich, Rich 6, Enterprise Full)
- Tutti i 16 meta keys esportati con sanitizzazione specifica per tipo

### Opzioni tema JSON

- 60 campi trasferibili tra sito, media, header, mobile, footer, contatti e integrazioni.
- Logo principale, overlay, chiaro e footer configurabili tramite URL assoluto.
- Sfondo footer, CTA, trust bar e mappa importabili.
- Schema disponibile via `/wp-json/aihtml/v1/ai/options/schema`.
- Test standalone in `tests/options-json-test.php`.

---

## Sistema footer — 7 varianti

| Variante | Descrizione |
|---|---|
| `enterprise` | Gradient + trust bar + newsletter + contatti + social |
| `futuristic` | Dark bg con radial gradient + glassmorphism |
| `corporate` | Pulito su sfondo light |
| `compact` | Padding ridotto, gutter stretti |
| `mega-footer` | Multi-colonna via `footer_col_1..4` menu locations |
| `minimal` | Riga singola: copyright + link inline + social |
| `cta-footer` | Hero CTA (titolo + 2 bottoni) sopra footer enterprise |

### Componenti footer

- **Trust bar dinamica**: fino a 5 item con icona FA + testo, configurabili da Customizer. Fallback 3 item default.
- **Newsletter CTA**: integrazione Mailchimp via shortcode `mc4wp_form`.
- **Background decorativo**: immagine locale o remota con opacity, position, size, repeat configurabili. Overlay con colore e opacity.
- **Footer CTA hero**: titolo, sottotitolo, 2 bottoni (primario + outline).

---

## Code Slots

Il modulo `inc/admin/code-slots.php` permette injection e override tramite HTML, CSS e JavaScript.

- `header_full` e `footer_full` sostituiscono completamente i componenti nativi.
- Gli slot `mixed` separano HTML (`code`), CSS (`css`) e JavaScript (`js`).
- I contesti supportano pagina, template, tassonomia, login, negazione e liste separate da virgola.
- Gli slot sono importabili/esportabili nel formato `aihl-code-slots`.
- API: `/wp-json/aihtml/v1/ai/code-slots`.
- Progetto di validazione: `demo-projects/code-slots-header-validation/`.

---

## Mobile — 3 stili navigazione

| Stile | Descrizione |
|---|---|
| `rail` | Pilole verticali flottanti (menu, home, telefono, scroll) |
| `bottom-bar` | Barra inferiore fissa stile app (Home, Search, Menu, CTA, Account) |
| `none` | Disabilitato |

---

## Admin Hub (v1.2.0)

Menu top-level **AI-HTML** nella sidebar admin con:

| Pagina | Slug | Funzione |
|---|---|---|
| Dashboard | `aihl-dashboard` | Panoramica stato tema + link rapidi |
| Plugin | `aihl-plugins` | Stato tecnico completo dei plugin richiesti/raccomandati; la Dashboard mostra il solo riepilogo operativo |
| Menu JSON | `aihl-menu-json` | Import/export menu |
| Rich Menu Guida | `aihl-menu-help` | Documentazione campi menu |
| Opzioni JSON | `aihl-options-json` | Editor JSON opzioni tema |
| Compliance | `aihl-compliance` | Audit Google/AI 2026 |

Template unificato: header scuro con logo/versione, tab navigazione con icone, area contenuto, footer credits. Architettura a registry (`aihl_admin_get_subpages()`).

---

## Contratto SBM (Smart Bootstrap Manager)

### Regola fondamentale

Il tema consuma **esclusivamente** variabili CSS runtime `--bs-*` e `--sbin-*` per colori, tipografia, spacing, radius e componenti. Non hardcoda valori.

### Token usati

| Categoria | Token principali |
|---|---|
| Colori | `--bs-primary`, `--bs-secondary`, `--bs-light`, `--bs-dark`, `--bs-body-bg`, `--bs-body-color`, `--bs-link-color`, `--bs-link-hover-color`, `--bs-border-color`, `--bs-*-rgb` |
| Tipografia | `--bs-body-font-family`, `--bs-body-font-size`, `--bs-body-line-height`, `--bs-headings-font-family`, `--bs-headings-line-height`, `--sbin-headings-weight` |
| Componenti | `--sbin-btn-padding-y/x`, `--sbin-btn-font-weight`, `--sbin-btn-border-radius`, `--sbin-input-border-radius`, `--sbin-card-border-radius` |
| Nav | `--bs-navbar-nav-link-padding-x`, `--bs-nav-link-padding-y` |
| Layout | `--bs-spacer`, `--bs-border-radius`, `--bs-border-radius-lg` |
| Contrasto | `--sbin-primary-contrast` |

### Nota critica: token nav padding

Bootstrap 5.3 setta `--bs-nav-link-padding-x: 0` dentro `.navbar-nav`. Il tema deve usare `--bs-navbar-nav-link-padding-x` (default `0.5rem`) per i nav-link dentro la navbar.

---

## Regole per nuovo sviluppo

1. Nuovo codice runtime: solo in `inc/theme/` o `inc/integrations/`, mai in `lib/`
2. Nuova logica Customizer: solo in `inc/customizer/`
3. Nuove pagine admin: aggiungere entry in `aihl_admin_get_subpages()` in `inc/admin/admin-hub.php`
4. CSS: usare token `--bs-*` / `--sbin-*` con fallback. Zero hardcode colori
5. `functions.php` resta minimale e dichiarativo
6. Wrapper legacy (`lib/`, `customizer/`) restano per compatibilita fino a rimozione pianificata
7. Ogni modifica strutturale aggiorna questo documento

---

## Componenti runtime estratti

Dal refactor del 15 giugno 2026, i template delegano la logica a componenti dedicati:

| Componente | Responsabilita |
|---|---|
| `inc/theme/mobile-navigation.php` | Valida le opzioni mobile e renderizza rail o bottom bar |
| `resource/js/main.js` | Gestisce scroll, topbar, ricerca, offcanvas, submenu e hover desktop |

### Regole di ownership CSS

- `resource/css/ai-html.css`: struttura e UI dei componenti nativi.
- `resource/css/aihl-menu-walker.css`: layout desktop dei menu rich.
- `resource/css/aihl-bootstrap-bridge.css`: solo adattamento ai token SBM; nessuna regola strutturale duplicata.
- Il desktop reset dell'offcanvas vive in `resource/css/ai-html.css` dentro `@media (min-width: 992px)`.
- Il mobile offcanvas vive in `resource/css/ai-html.css` dentro `@media (max-width: 991.98px)`.
- Il bridge SBM non deve impostare `position`, `display`, `height`, `width`, `transform` o `background` strutturali su offcanvas, rail o navbar.
- I marker decorativi devono essere CSS nativo quando non rappresentano contenuto semantico.
- Le icone informative usano Font Awesome 6 con prefissi `fa-solid` o `fa-brands`.
