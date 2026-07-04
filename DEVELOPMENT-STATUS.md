# AI-HTML Theme â€” Stato di sviluppo

Versione: 1.7.1  
Ultimo aggiornamento: 30 giugno 2026

I loghi configurati via Customizer/API mantengono proporzioni native con limiti responsive; le varianti principale, overlay, chiara e footer restano governate dal contratto del tema.

Quando SBS e attivo, logo e social SBS sono la sorgente prioritaria usata dai componenti runtime dei Canvas.

Il manifest tema espone la governance SBM e richiede a ogni AI Canvas una modalita design
esplicita. Menu, identita, social e add-on restano componenti runtime WordPress anche in
modalita autonoma.

Documentazione canonica cross-prodotto: `../docs/smart-stack/`.

Header e footer dichiarano ora una sorgente esplicita `native` o `canvas`. La modalita
Canvas richiede uno slot contestualmente attivo; in sua assenza il tema usa il fallback
nativo per evitare strutture mancanti.

---

## Legenda

| Stato | Significato |
|---|---|
| DONE | Implementato, funzionante, testabile |
| PARTIAL | Implementato con limitazioni note |
| PLANNED | Pianificato per sprint futuro |
| N/A | Non applicabile o delegato a plugin esterno |
| BUG | Bug noto da verificare/risolvere |

---

## Obiettivo sviluppo 2026

AI-HTML deve essere la cornice tema dell'ecosistema:

- header, footer, template e menu restano responsabilita del tema;
- Bootstrap, token visuali, librerie effetti e Barba.js sono responsabilita di SBM;
- i widget e il contenuto pagina sono responsabilita di SBS;
- i controlli complessi del Customizer vengono forniti da SCF;
- il tema deve restare compatibile con Google 2026, AI crawler, accessibilita e contenuto server-side.

## Aggiornamento 2026-06-23 - Bridge header/menu

- Rimosse dal bridge SBM le regole colore su `.aihl-header-nav .nav-link`, `.dropdown-item` e `.navbar-brand`.
- Il tema AI-HTML torna proprietario dei colori header/menu, incluse varianti overlay e fullscreen hero.
- Il bridge resta limitato a token globali, spacing, border, card, form e tipografia base.

## 1. CORE THEME

| Feature | Stato | File | Note |
|---|---|---|---|
| Bootstrap flow | DONE | `functions.php` â†’ `inc/core/bootstrap.php` â†’ `loader.php` | Caricamento ordinato per blocchi |
| Costanti e option system | DONE | `inc/option.php` | `AIHL_OPTION_BASE`, `AIHL_VERSION`, `AIHL_UNICODE` |
| Helper `aihtml_option_value()` | DONE | `inc/theme/utilities.php` | Lettura opzioni con fallback |
| Plugin detection wrapper | DONE | `inc/theme/utilities.php` | `aihtml_is_plugin_active()`, `aihtml_is_site_builder_active()` |
| Activation hooks | DONE | `inc/activation.php` | Registrazione opzioni |
| Required plugins status | DONE | `inc/required-plugins.php` | Riepilogo non invasivo nella Dashboard AI-HTML + pagina tecnica completa |
| Output cleanup | DONE | `inc/output-cleanup.php` | |
| Legacy wrapper `lib/` | DONE | `lib/*.php` | Forward a `inc/theme/*` â€” non usare per nuovo codice |
| Legacy wrapper `customizer/` | DONE | `customizer/*.php` | Forward a `inc/customizer/*` â€” non usare per nuovo codice |
| Re-init per transizioni pagina | DONE | `resource/js/main.js` | Barba.js e asset transizioni sono gestiti da Smart Bootstrap Manager |

---

## 2. ADMIN HUB

| Feature | Stato | File | Note |
|---|---|---|---|
| Menu top-level AI-HTML | DONE | `inc/admin/admin-hub.php` | Posizione 59, icona SVG |
| Dashboard con stat cards | DONE | `inc/admin/admin-hub.php` | Versione, header, footer, plugin status |
| Dashboard link rapidi | DONE | | Card a tutte le sottopagine + Customizer |
| Sottopagina: Plugin | DONE | slug `aihl-plugins` | Wrappa `aihl_render_plugins_page` |
| Sottopagina: Menu JSON | DONE | slug `aihl-menu-json` | Wrappa `aihl_render_menu_json_page` |
| Sottopagina: Rich Menu Guida | DONE | slug `aihl-menu-help` | Wrappa `aihl_render_rich_menu_help_page` |
| Sottopagina: Opzioni JSON | DONE | slug `aihl-options-json` | Wrappa `aihl_render_options_json_page` |
| Sottopagina: Compliance | DONE | slug `aihl-compliance` | Wrappa `aihl_render_compliance_2026_page` |
| Template unificato | DONE | `aihl_admin_page_template()` | Header scuro + tab nav + contenuto + footer |
| CSS admin dedicato | DONE | Inline su `wp-admin` | Solo su pagine `aihl-*` |
| Font Awesome admin | DONE | | Carica FA solid per icone tab |
| Rimozione vecchie pagine da Aspetto | DONE | `remove_submenu_page` priorita 999 | |
| Registry sottopagine | DONE | `aihl_admin_get_subpages()` | Array aggiungere = 1 entry |
| Form import menu JSON | BUG | | Da verificare POST sotto nuovo slug `admin.php` |

---

## 3. HEADER

| Feature | Stato | Customizer | Note |
|---|---|---|---|
| Struttura: Standard | DONE | `header_structure = standard` | Logo sx + nav + CTA dx |
| Struttura: Dual Bar | DONE | `= dualbar` | Topbar dark + navbar |
| Struttura: Centered | DONE | `= centered` | Logo centrato |
| Struttura: Topbar + Navbar | DONE | `= topbar-nav` | Topbar scroll-away + navbar sticky |
| Struttura: Mega Centered | DONE | `= mega-centered` | Menu sx + logo + menu dx |
| Struttura: Sidebar | DONE | `= sidebar` | Barra verticale 280px |
| Struttura: Triple Row | DONE | `= triple-row` | Utility + Brand bar + Nav bar |
| Struttura: Stacked Centered | DONE | `= stacked-centered` | Utility + Logo grande + Nav centrata |
| Nav layout: Clean | DONE | `header_nav_layout = clean` | |
| Nav layout: Pills | DONE | `= pills` | |
| Nav layout: Underline | DONE | `= underline` | |
| Nav layout: Compact | DONE | `= compact` | |
| Overlay mode (auto/always/never) | DONE | `header_overlay_mode` | Blur e opacity configurabili |
| CTA button | DONE | `header_cta_label/url` | |
| Login button | DONE | `header_login_label/url` | |
| Toggle Logo on/off | DONE | `header_show_logo` | |
| Logo remoto via JSON | DONE | `site_logo_url` + varianti | Fallback SBS e Custom Logo WordPress |
| Toggle CTA on/off | DONE | `header_show_cta` | |
| Toggle Login on/off | DONE | `header_show_login` | |
| Text variant (6 opzioni) | DONE | `header_nav_text_variant` | Normal, uppercase, lowercase, italic, combinazioni |
| Font weight nav | DONE | `header_nav_font_weight` | 300-800 |
| Letter spacing nav | DONE | `header_nav_letter_spacing` | 0-0.2em |
| Search: Dropdown | DONE | `header_search_style = icon-dropdown` | Barra slide-down |
| Search: Fullscreen | DONE | `= icon-fullscreen` | Overlay scuro |
| Search: Inline | DONE | `= inline` | Campo nella navbar |
| Search: Off | DONE | `= none` | |
| Topbar scroll-away | DONE | `header_topbar_scroll_behavior` | |
| Sticky: Solid | DONE | `header_sticky_style = solid` | |
| Sticky: Blur | DONE | `= blur` | Glassmorphism |
| Sticky: Transparent | DONE | `= transparent` | |
| Sticky: Gradient Fade | DONE | `= gradient-fade` | Apple/Nike style |
| Brand Bar (triple-row) | DONE | | Logo + search + login + CTA |
| Brand Bar (stacked-centered) | DONE | | Logo grande + tagline |
| Topbar componente condiviso | DONE | | Usata da dualbar, topbar-nav, triple-row, stacked-centered |
| Offcanvas mobile | DONE | | Left/right configurabile |
| Dropdown indicator icon | DONE | `menu_dropdown_indicator` | Chevron-down su item con submenu, toggle on/off via Customizer/API |
| Skip link accessibilita | DONE | | `#main` focus |
| SBS slots header | DONE | | `slot.header.actions`, `slot.header.secondary` |
| Hook `aihl/header/topbar/right` | DONE | | Estensioni plugin nella topbar |
| Spacing nav-link token SBM | DONE | | Usa `--bs-navbar-nav-link-padding-x` con floor `max()` |

---

## 4. MENU SYSTEM

| Feature | Stato | Note |
|---|---|---|
| Walker `AIHL_Nav_Menu_Walker` | DONE | Profondita 3 livelli |
| Mode: Auto / Simple / Rich | DONE | |
| Layout: Split | DONE | Lista + pannello laterale |
| Layout: Compact | DONE | Lista densa + pannello |
| Layout: Columns | DONE | 2 colonne |
| Layout: Grid | DONE | Card 3 colonne centrate |
| Layout: Tabbed | DONE | Tab card con bordo |
| Layout: Featured | DONE | Immagine grande + overlay icona |
| Layout: Showcase | DONE | Hero bg image + gradient |
| Campo: Icona (FA) | DONE | Qualsiasi livello |
| Campo: Badge + colore | DONE | Color picker admin |
| Campo: Sottotitolo | DONE | |
| Campo: Sopratitolo (eyebrow) | DONE | |
| Campo: Immagine (URL/Media) | DONE | Media picker jQuery |
| Campo: Evidenzia (highlight) | DONE | |
| Campo: Colore accento | DONE | Color picker, icona + hover |
| Campo: Stile CTA item | DONE | btn-primary / outline / secondary |
| Campo: Rich Content HTML | DONE | Solo livello 0 â€” pannello laterale |
| Campo: Rich CTA (label+url) | DONE | Bottone in fondo al mega menu |
| Campo: Rich Footer HTML | DONE | Barra footer nel mega menu |
| Admin UX: sezioni colorate | DONE | Comportamento / Visivo / Rich |
| JSON Export singolo/tutti | DONE | Download `.json` |
| JSON Import da file upload | DONE | `.json` max 2MB |
| JSON Import da textarea | DONE | |
| JSON Sostituzione menu | DONE | Checkbox replace |
| JSON 4 Preset | DONE | Simple, Rich, Rich 6, Enterprise Full |
| JSON 16 meta keys | DONE | Inclusi colore, badge color, CTA item |
| JSON Sanitizzazione import | DONE | kses_post, esc_url_raw, sanitize_hex_color |
| Location: topic | DONE | Menu principale |
| Location: utili | DONE | Footer link |
| Location: naviga | DONE | Topbar utility |
| Location: footer | DONE | Legacy |
| Location: topic_left/right | DONE | Mega centered |
| Location: footer_col_1..4 | DONE | Mega footer |
| Audit mobile 7 layout rich | PARTIAL | Da testare tutti i layout nell'offcanvas mobile |

---

## 5. FOOTER

| Feature | Stato | Customizer | Note |
|---|---|---|---|
| Enterprise | DONE | `footer_variant = enterprise` | Gradient + trust + newsletter |
| Futuristic | DONE | `= futuristic` | Dark radial gradient |
| Corporate | DONE | `= corporate` | Light bg pulito |
| Compact | DONE | `= compact` | Padding ridotto |
| Mega Footer | DONE | `= mega-footer` | Multi-colonna via 4 menu locations |
| Minimal | DONE | `= minimal` | Riga singola |
| CTA Footer | DONE | `= cta-footer` | Hero CTA + enterprise sotto |
| Background image + overlay | DONE | `footer_background_*`, `footer_overlay_*` | Locale o remota |
| Logo footer remoto | DONE | `footer_logo_url` | Fallback sul logo principale |
| Newsletter Mailchimp | DONE | `mailchip_footer` | mc4wp_form shortcode |
| Trust bar dinamica (1-5) | REMOVED | Footer nativo | Rimossa dal rendering e dai controlli per ridurre rumore visivo |
| CTA hero (titolo + 2 btn) | DONE | `footer_cta_title/subtitle/btn*` | Solo su cta-footer |
| Colonne mega footer (3-5) | DONE | `footer_columns_count` | |
| Social links | DONE | | Via Smart Builder Site |
| Contatti | DONE | | Indirizzo, telefono, email |
| SBS slots footer | DONE | | `slot.content.after`, `slot.footer.tools` |

---

## 6. MOBILE

| Feature | Stato | Customizer | Note |
|---|---|---|---|
| Floating Rail | DONE | `mobile_nav_style = rail` | Menu, home, telefono, scroll â€” FIX: estratto fuori da code-slot override block |
| Bottom Bar | DONE | `= bottom-bar` | Home, Search, Menu, CTA, Account â€” FIX: isset() guard per variabili code-slot |
| Nessuna | DONE | `= none` | |
| Rail posizione | DONE | `mobile_rail_position` | Left/right |
| Rail autohide | DONE | `mobile_rail_autohide` | Nasconde sotto 40px scroll |
| Bottom bar safe area | DONE | | `env(safe-area-inset-bottom)` |
| Offcanvas accordion | DONE | | Toggle submenu mobile |

---

## 7. INTEGRATIONS

| Feature | Stato | File | Note |
|---|---|---|---|
| SBM bridge PHP | DONE | `inc/integrations/smart-bootstrap-manager.php` | Inline CSS dinamico |
| SBM bridge CSS | DONE | `resource/css/aihl-bootstrap-bridge.css` | Token mapping statico |
| SBS compatibility | DONE | `inc/theme/utilities.php` | Slot system, logo, social |
| SCF wrappers | DONE | `inc/customizer/section.php` | Toggle, textbox, repeater |
| Google Compliance 2026 | DONE | `inc/integrations/google-compliance-2026.php` | Audit automatico |
| SEO integration | DONE | `inc/integrations/seo.php` | |
| AI Auth Core | DONE | `inc/integrations/ai-auth-core.php` | API keys (sotto Impostazioni) |
| AI API | DONE | `inc/integrations/ai-api.php` | |
| Opzioni JSON complete | DONE | `inc/theme/options-json.php` | 60 campi + feedback rifiutati |

---

## 8. ASSET MANAGEMENT

| Feature | Stato | Note |
|---|---|---|
| Conditional animate/wow | DONE | Solo about/contact o contenuto `.wow` |
| Conditional owl carousel | DONE | Solo contenuto `testimonial-carousel` |
| Conditional FA brands | DONE | Solo single/about/contact/social |
| CSS dep `smart-bootstrap` | DONE | Fallback senza SBM |
| JS defer main.js | DONE | |
| Inline JS hover dropdown | DONE | Timer 340ms desktop |
| Versioning AIHL_UNICODE | DONE | Cache busting |
| FA admin per Admin Hub | DONE | Condizionale su pagine `aihl-*` |
| Logo remoto header/footer | DONE | URL assoluti senza Media Library |

---

## 9. CUSTOMIZER

| Sezione | Stato | N. controlli | Note |
|---|---|---|---|
| Sito | DONE | 1 | Descrizione |
| Articoli | DONE | 5 | Image size, next/prev, related, link, content width |
| Contatti | DONE | 4 | Indirizzo, telefono, email, Google Maps |
| Contact Form | DONE | 1 | ID modulo |
| Mailchimp | DONE | 1 | ID modulo |
| Header | DONE | 20+ | Struttura, overlay, nav layout/text/weight/spacing, CTA, login, search, sticky, topbar scroll, mobile nav, show logo/cta/login, dropdown indicator |
| Sfondo Pagina | DONE | 8 | page_bg_type, color, image, image_opacity, image_size, pattern, overlay_color, overlay_opacity |
| Footer | DONE | 25+ | Variante, bg image/overlay/position/size/repeat/tone, colonne, CTA title/subtitle/btn1/btn2, trust bar 5x |
| Reset | DONE | - | Reset opzioni |

---

## 10. TEMPLATE

| Template | Stato | Note |
|---|---|---|
| `header.php` | DONE | 8 strutture, search, topbar, brand bar, offcanvas |
| `footer.php` | DONE | 7 varianti, trust bar, CTA hero, mega-footer |
| `index.php` | DONE | Fallback contenuti con hero semantica, card riusabili e paginazione |
| `single.php` | DONE | Articolo con microdata `Article`, share, author box, tag e related |
| `page.php` | DONE | Pagina standard con microdata `WebPage` e hook `aihl_before/after_main_content` |
| `search.php` | DONE | Ricerca noindex/follow, hero con search form, result count e card verticali |
| `category.php` | DONE | Categoria con blocco SBS opzionale e fallback nativo indicizzabile |
| `404.php` | DONE | Stato utile con ricerca, CTA home e ultimi contenuti |
| `about.php` | DONE | Template pagina about |
| `contact.php` | DONE | Template contatto con opzioni tema, form shortcode, social SBS, mappa e microdata `ContactPage` |
| `home.php` | DONE | Blog index con 3 layout (grid/list/magazine) + sidebar opzionale |
| `front-page.php` | N/A | Delegato a Smart Builder Site |
| `archive.php` | DONE | Archivio generico con hero semantica, breadcrumbs, griglia e sidebar opzionale |
| `template-parts/card-post.php` | DONE | Card con 3 layout (horizontal/vertical/list) |
| `template-parts/post-meta.php` | DONE | Meta con 3 stili (inline/block/minimal) + tempo lettura |
| `template-parts/share-buttons.php` | DONE | 4 social + Web Share API, 3 stili |
| `template-parts/author-box.php` | DONE | Avatar, bio, 5 social links |
| `template-parts/related-posts.php` | DONE | 3 articoli correlati per categoria |

---

## 11. QUALITA E COMPLIANCE

| Area | Stato | Note |
|---|---|---|
| PHP senza fatal se plugin assenti | DONE | Wrapper su tutte le funzioni plugin |
| Escaping output | DONE | Audit completo v1.3.0 â€” zero output non escaped |
| i18n text domain | DONE | Tutte le stringhe con AIHL_TEXT_DOMAIN |
| Accessibilita skip link | DONE | `#main` su tutte le strutture header |
| Accessibilita aria-label | PARTIAL | Principali presenti, WCAG 2.1 AA audit da fare |
| `prefers-reduced-motion` | DONE | CSS globale |
| Mobile responsive | DONE | Bootstrap grid + breakpoint specifici |
| SBM compliance | DONE | ~92% â€” token runtime, no hardcode colori |
| SBM compliance gap | PARTIAL | Ridurre `!important`, estendere `--sbin-primary-contrast` |
| Token nav-link corretto | DONE | `--bs-navbar-nav-link-padding-x` con `max()` floor |

---

## 12. BUG NOTI

| # | Bug | Priorita | Stato |
|---|---|---|---|
| 1 | Form import Menu JSON da verificare sotto nuovo slug admin.php | Alta | Da testare |
| 2 | Audit mobile offcanvas con tutti i 7 layout rich | Media | Da testare |
| 3 | Sidebar header in dark mode SBM da verificare | Bassa | Da testare |

---

## 13. PAGE BACKGROUND SYSTEM

| Feature | Stato | Note |
|---|---|---|
| File `inc/theme/page-background.php` | DONE | Meta box + rendering + REST API |
| Meta box "Sfondo Pagina" | DONE | 8 campi: type, color, image, image_opacity, image_size, pattern, overlay_color, overlay_opacity |
| Customizer defaults | DONE | `page_bg_*` in sezione "Sfondo Pagina" |
| CSS patterns (4) | DONE | dots, grid, diagonal, cross â€” via `::before` pseudo-element |
| Template hooks | DONE | `aihl_before_main_content` / `aihl_after_main_content` su `page.php` e `single.php` |
| REST `GET/PUT/DELETE /pages/{id}/background` | DONE | Per-page background via API |
| REST `GET /page-background/patterns` | DONE | Lista pattern disponibili |
| API whitelist (9 opzioni) | DONE | `menu_dropdown_indicator` + 8 `page_bg_*` in `ai-api.php` |

---

## 14. ROADMAP FUTURA

| Sprint | Focus | Stato |
|---|---|---|
| 6 | Template Parts e Blog (card, meta, share, author box, home.php, archive.php) | Prossimo |
| 7 | CPT vetrina_item + campi custom + archive/single dedicati | Pianificato |
| 8 | Qualita tecnica (i18n completa, encoding, migrazione WOW/Owl, `!important` cleanup, WCAG audit) | Pianificato |
| 9 | SEO + Performance (breadcrumbs fallback, responsive images, critical CSS, Lighthouse >= 80) | Pianificato |
| 10 | Sidebar evoluta + Area Member (accordion nav, login WP, dashboard widget area) | Pianificato |

---

## 15. REFACTOR STRUTTURALE 15 GIUGNO 2026

| Area | Stato | Implementazione |
|---|---|---|
| Navigazione mobile | DONE | Configurazione e rendering isolati in `inc/theme/mobile-navigation.php` |
| Footer proof points | REMOVED | Rendering e opzioni rimosse dal footer nativo |
| JavaScript header | DONE | Hover desktop trasferito da inline script a `resource/js/main.js` |
| Bridge SBM | DONE | Rimosse dal bridge le regole strutturali del rail mobile |
| Footer link marker | DONE | Freccia CSS nativa, senza dipendenza da glifi Font Awesome |
| Cache frontend | DONE | Aggiornato `AIHL_UNICODE` |
| Test regressione | DONE | Test dedicati a rail mobile, footer e fallback risorse |
| Header desktop/mobile | DONE | Offcanvas desktop isolato dal pannello mobile; bridge SBM solo-token |
