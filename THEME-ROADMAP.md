# AI-HTML Theme — Roadmap di prodotto

Versione: 1.4.0  
Ultimo aggiornamento: 9 giugno 2026

---

## Identita del prodotto

AI-HTML e il **tema base fondazione** dell'ecosistema Smart eCommerce per WordPress. Non e un tema consumer finale: e il layer su cui si costruiscono temi enterprise verticali.

### Ecosistema di riferimento

| Plugin | Ruolo | Dipendenza |
|---|---|---|
| Smart Bootstrap Manager (SBM) | Token CSS, palette, tipografia, layout, effetti | Raccomandata |
| Smart Customizer Framework (SCF) | Controlli Customizer avanzati (Builder, Compose) | Richiesta per Customizer completo |
| Smart Builder Site (SBS) | Page Builder corporate e blog | Raccomandata per home/pagine |

### Target utente

- Sviluppatori che costruiscono siti enterprise WordPress
- Agenzie che usano l'ecosistema Smart eCommerce
- Progetti corporate, SaaS, editorial, dashboard, landing page

---

## Stato attuale — v1.4.0

### Completato (Sprint 1-5)

| Area | Feature count | Note |
|---|---|---|
| Header | 8 strutture, 4 nav layout, 4 sticky style, 4 search, 3 toggle | Copertura enterprise completa |
| Menu | 7 rich layout, 12 campi item, JSON import/export con 4 preset | Sistema menu piu avanzato dell'ecosistema |
| Footer | 7 varianti, trust bar dinamica, CTA hero, mega-footer | Copertura enterprise completa |
| Mobile | 3 stili (rail, bottom-bar, none) | |
| Admin | Hub unificato con dashboard + 5 sottopagine + template comune | |
| Template Parts | 5 blocchi riusabili (card-post, post-meta, author-box, share-buttons, related-posts) | |
| Blog | home.php (3 layout), archive.php, single.php riscritto con template-parts | |
| Quality | Escaping e i18n completati | |
| Customizer | 50+ controlli in 8 sezioni | |
| Integrazioni | SBM, SBS, SCF, SEO, Google Compliance 2026, AI Auth | |
| Documentazione | 5 documenti tecnici (Architecture, Dev Status, Roadmap, Changelog, SBM Compliance) | |
| Provisioning JSON | Opzioni complete, menu, logo/media remoti, demo e test automatico | |

### Gap residui v1.4.0

| Priorita | Gap |
|---|---|
| Alta | Verificare form import sotto nuovi slug admin hub |
| Alta | Audit mobile offcanvas con tutti i 7 layout rich |
| Media | Ridurre `!important` nel bridge CSS |
| Media | Estendere `--sbin-primary-contrast` a bottom-bar, sidebar, CTA custom |
| Bassa | Sidebar header in dark mode SBM |
| Bassa | Escaping output audit completo |
| Bassa | i18n non uniforme su file legacy |

---

## Sprint futuri

### Sprint 6 — Template Parts e Blog — COMPLETATO v1.3.0

**Obiettivo**: Blog completo con componenti riusabili. **FATTO.**

| # | Task | Priorita |
|---|---|---|
| 1 | Creare `template-parts/card-post.php` — card articolo con thumbnail, titolo, excerpt, meta | Alta |
| 2 | Creare `template-parts/post-meta.php` — data, autore, categoria, tempo lettura | Alta |
| 3 | Creare `template-parts/author-box.php` — avatar, bio, link social | Alta |
| 4 | Creare `template-parts/share-buttons.php` — condivisione social (no JS esterno) | Media |
| 5 | Creare `home.php` — blog index con layout configurabile (griglia/lista/magazine) da Customizer | Alta |
| 6 | Creare `archive.php` potenziato — sidebar opzionale, filtri, layout configurabile | Alta |
| 7 | Paginazione avanzata — load more / infinite scroll opzionale | Media |
| 8 | Related posts in `single.php` via template-part | Media |

**Criteri di done**: Blog navigabile con card, meta, author box, paginazione. Layout selezionabile dal Customizer.

---

### Sprint 7 — Vetrina data-driven

**Obiettivo**: CPT per servizi/progetti con gestione da backend.

| # | Task | Priorita |
|---|---|---|
| 1 | Registrare CPT `vetrina_item` con tassonomie `vetrina_category` e `vetrina_tag` | Alta |
| 2 | Campi custom: headline, excerpt, CTA (label+url), icona (FA), ordine, immagine | Alta |
| 3 | Template `archive-vetrina_item.php` con griglia card | Alta |
| 4 | Template `single-vetrina_item.php` con layout dettaglio | Alta |
| 5 | Shortcode `[aihl_vetrina]` per embedding in pagine | Media |
| 6 | Integrazione con Smart Builder Site widget | Media |

**Criteri di done**: Vetrina servizi gestibile da backend senza ACF, con archive e single dedicati.

---

### Sprint 8 — Qualita tecnica

**Obiettivo**: Pulizia codice, i18n, accessibilita.

| # | Task | Priorita |
|---|---|---|
| 1 | i18n completa: `__()` / `esc_html__()` su TUTTI i file con `AIHL_TEXT_DOMAIN` | Alta |
| 2 | Escaping audit: verificare `esc_html`, `esc_attr`, `esc_url` su tutti gli output | Alta |
| 3 | Cleanup encoding UTF-8 e accenti nei file legacy | Media |
| 4 | Migrazione WOW.js/Owl Carousel verso effetti SBM o fallback documentati | Media |
| 5 | Riduzione `!important` nel bridge CSS (target: < 10 occorrenze) | Media |
| 6 | Audit WCAG 2.1 AA: focus ring, color contrast, screen reader, keyboard nav | Alta |
| 7 | Rimozione codice commentato e file inutilizzati | Bassa |

**Criteri di done**: Zero warning PHP, i18n completa, WCAG AA sui componenti principali.

---

### Sprint 9 — SEO e Performance

**Obiettivo**: Lighthouse >= 80, SEO strutturale.

| # | Task | Priorita |
|---|---|---|
| 1 | Breadcrumbs con fallback tema (senza dipendenza plugin) | Alta |
| 2 | Schema.org markup per article, breadcrumb, organization | Alta |
| 3 | Immagini responsive con `srcset` e `sizes` coerenti | Media |
| 4 | Lazyload nativo (`loading="lazy"` + `decoding="async"`) audit | Media |
| 5 | Rimozione asset CSS/JS non utilizzati nelle pagine dove non servono | Media |
| 6 | Critical CSS inline per above-the-fold | Media |
| 7 | Lighthouse audit e fix fino a score >= 80 su mobile (home + single) | Alta |

**Criteri di done**: Lighthouse mobile >= 80, breadcrumbs funzionanti, schema.org validato.

---

### Sprint 10 — Sidebar evoluta e Area Member

**Obiettivo**: Layout dashboard e area riservata.

| # | Task | Priorita |
|---|---|---|
| 1 | Sidebar con navigazione accordion/tree (multi-livello collassabile) | Alta |
| 2 | Integrazione con login/logout WordPress nativo | Alta |
| 3 | Template `dashboard.php` con widget area configurabili | Media |
| 4 | Protezione contenuti per utenti loggati (gate template) | Media |
| 5 | Notifiche in-page per area riservata | Bassa |

**Criteri di done**: Area member navigabile con sidebar, login WP integrato, dashboard con widget.

---

## Criteri di done globali (tema v2.0)

- [ ] Tema attivabile senza fatal anche senza plugin opzionali
- [ ] Home corporate editabile da backend (via SBS)
- [ ] Blog completo: listing, categorie, ricerca, single con card/meta/author
- [ ] Header/Footer selezionabili da Customizer con anteprima live
- [ ] 8 strutture header + 7 varianti footer funzionanti
- [ ] Menu system con 7 layout rich + JSON import/export
- [ ] Admin Hub con dashboard e pagine unificate
- [ ] Lighthouse mobile >= 80 su home e single
- [ ] WCAG 2.1 AA sui componenti principali
- [ ] i18n completa con text domain uniforme
- [ ] SBM compliance >= 95%
- [ ] Documentazione tecnica aggiornata
