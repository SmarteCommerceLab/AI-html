# AI-HTML / Smart Bootstrap Manager — Compliance Report

Versione tema: 1.5.3  
Versione SBM: 1.3.1  
Ultimo aggiornamento: 23 giugno 2026

---

## Sintesi

**Livello compliance: ~96%**

Il tema consuma esclusivamente token CSS runtime `--bs-*` e `--sbin-*` per tutti i componenti visivi. Zero colori hardcoded fuori da fallback `var()`. L'ordine di caricamento CSS rispetta la cascade SBM.

---

## Aggiornamento UI/FX 2026-06-22

AI-HTML resta consumer di SBM per gli effetti avanzati:

- Barba.js e page transitions sono caricati e configurati da SBM.
- Il tema mantiene header, footer, template e reinizializzazione dei propri comportamenti dopo transizione.
- Il tema non deve duplicare Bootstrap, GSAP e relativi plugin, Chart.js o Barba.js.
- Il contenuto principale deve rimanere server-side e non dipendere da animazioni per essere leggibile.

## Aggiornamento 2026-06-23 - Confine bridge/header

- Il bridge `aihl-bootstrap-bridge.css` non colora piu nav link, dropdown item e navbar brand.
- I colori header/menu restano nel CSS tema `ai-html.css`, cosi overlay/fullscreen hero mantengono testo bianco quando richiesto.
- SBM fornisce token, non regole componenti header.

## Matrice compliance

### Colori e palette

| Requisito SBM | Stato | Evidenza |
|---|---|---|
| `--bs-primary` / `--bs-primary-rgb` | OK | Header, footer, menu, CTA, trust bar, badge |
| `--bs-secondary` / `--bs-secondary-rgb` | OK | Componenti secondari |
| `--bs-light` / `--bs-dark` / `--bs-*-rgb` | OK | Topbar dark, overlay, footer futuristic |
| `--bs-body-bg` / `--bs-body-color` / `--bs-*-rgb` | OK | Sidebar, bottom bar, search, tutti i bg |
| `--bs-link-color` / `--bs-link-hover-color` | OK | Nav link, footer link, utility link |
| `--bs-border-color` | OK | Tutti i bordi: card, trust, dropdown, search, sidebar |
| `--sbin-primary-contrast` | PARTIAL | Usato su CTA footer. Da estendere a bottom-bar e sidebar active |
| Nessun hardcode `#0d6efd` fuori da `var()` | OK | Verificato con grep su tutti i CSS |

### Tipografia

| Requisito SBM | Stato | Evidenza |
|---|---|---|
| `--bs-body-font-family` | OK | Bridge CSS body |
| `--bs-body-font-size` / `--bs-body-line-height` | OK | Bridge CSS body |
| `--bs-headings-font-family` | OK | Bridge CSS + inline bridge |
| `--bs-headings-line-height` | OK | Bridge CSS + inline bridge |
| `--sbin-headings-weight` | OK | `ai-html.css` headings, `bridge` headings |
| Nessun font duplicato se SBM mode `local`/`off` | OK | Tema non carica font propri |

### Layout e spacing

| Requisito SBM | Stato | Evidenza |
|---|---|---|
| `--bs-spacer` | OK | Header, footer, sidebar, service items |
| `--bs-border-radius` / `sm` / `lg` | OK | Card, dropdown, input, button, sidebar |
| `--sbin-card-border-radius` | OK | Footer CTA, trust bar, menu rich content |
| `--sbin-input-border-radius` | OK | Form, dropdown items |

### Componenti

| Requisito SBM | Stato | Evidenza |
|---|---|---|
| `--sbin-btn-padding-y/x` | OK | Header CTA, login, footer submit |
| `--sbin-btn-font-weight` | OK | `.btn`, bridge inline |
| `--sbin-btn-border-radius` | OK | Tutti i bottoni, square buttons |
| `--bs-navbar-nav-link-padding-x` | OK | Nav link padding con `max()` floor |
| `--bs-nav-link-padding-y` | OK | Nav link padding verticale |
| Nessun Bootstrap duplicato | OK | Enqueue condizionale su handle `smart-bootstrap` |
| CSS tema dopo `smart-bootstrap` | OK | `ai-html-theme` dep `smart-bootstrap`, prio 99 |
| Bridge dopo tema | OK | `aihl-bootstrap-bridge` dep `smart-bootstrap` + `ai-html-theme`, prio 120 |
| Bridge non sovrascrive padding nav | OK | Regola `.navbar .nav-link` padding rimossa dal bridge |

### Effetti

| Requisito SBM | Stato | Evidenza |
|---|---|---|
| No librerie effetti duplicate | OK | AI-HTML non carica librerie; le scene demandano il runtime a SBM |
| `prefers-reduced-motion` | OK | CSS globale in `ai-html.css` |

### Nuovi componenti v1.2.0

| Componente | Token usati | Stato |
|---|---|---|
| Admin Hub | Non frontend — solo admin CSS | N/A |
| Topbar (condivisa) | `--bs-dark`, `--bs-light`, `--bs-border-color`, `--bs-primary` | OK |
| Brand Bar | `--bs-body-bg`, `--bs-border-color`, `--bs-body-color` | OK |
| Search Dropdown | `--bs-body-bg`, `--bs-border-color` | OK |
| Search Fullscreen | `--bs-dark-rgb`, `--bs-light-rgb`, `--bs-primary` | OK |
| Bottom Bar | `--bs-body-bg`, `--bs-body-color`, `--bs-border-color`, `--bs-primary` | OK |
| Sidebar Header | `--bs-body-bg`, `--bs-body-color`, `--bs-border-color`, `--bs-primary-rgb` | OK |
| Grid menu layout | `--bs-border-color`, `--bs-primary-rgb`, `--bs-border-radius-lg` | OK |
| Featured menu layout | `--bs-border-color`, `--bs-primary-rgb`, `--bs-body-bg` | OK |
| Showcase menu layout | `--bs-dark-rgb`, `--bs-light-rgb`, `--bs-light` | OK |
| Gradient-fade sticky | `--bs-dark-rgb`, `--aihl-overlay-opacity`, `--bs-light`, `--bs-body-bg` | OK |
| CTA Footer hero | `--bs-primary-rgb`, `--bs-secondary-rgb`, `--bs-body-bg`, `--bs-border-color` | OK |
| Minimal footer | `--bs-body-color-rgb`, `--bs-primary` | OK |
| Accent color item | `--aihl-item-color` (custom, non SBM) | OK — token locale, non sovrascrive SBM |
| Badge color custom | Attributo `style` inline | OK — solo su singolo badge, non globale |

---

## Gap residui

| Priorita | Gap | Impatto |
|---|---|---|
| P1 | Ridurre `!important` nel bridge (target < 10 usi) | Manutenibilita cascade |
| P2 | Estendere `--sbin-primary-contrast` a bottom-bar CTA, sidebar active state | Contrasto su bg primary custom |
| P2 | Audit mobile offcanvas tutti i 7 layout rich + dark mode SBM | Rendering mobile |
| P3 | Sidebar header: verificare `--bs-body-bg` in dark mode SBM | Dark mode consistency |
| P3 | Pulizia px legacy su componenti secondari (owl, testimonial) | Token compliance marginali |

---

## Source of Truth

1. **SBM** e source of truth per: palette `--bs-*`, tipografia `--bs-body-*`/`--bs-headings-*`/`--sbin-headings-weight`, spacing `--sbin-*`/`--bs-nav-link-padding-*`, effetti `sbin-*`
2. **AI-HTML** definisce solo varianti UI locali: `header_nav_layout`, `header_nav_text_variant`, `header_structure`, `footer_variant`, `--aihl-item-color`
3. **SBS** salva scelte semantiche e classi Bootstrap, non colori raw
4. Se c'e conflitto, **prevale il token runtime SBM**

### Guardrail

- Vietato hardcodare `#0d6efd` o equivalenti nei componenti globali
- Vietato caricare font globali alternativi quando SBM `local`/`off`
- Vietato ridefinire `.btn-primary`, `.text-primary`, `.bg-primary` con colori diretti
- Nuovi componenti devono usare esclusivamente `var(--bs-*)` e `var(--sbin-*)`
- Token nav padding: usare `--bs-navbar-nav-link-padding-x`, NON `--bs-nav-link-padding-x` (Bootstrap 5.3 lo azzera dentro `.navbar-nav`)

---

## Verifica rapida

```powershell
# Colori hardcoded fuori da var()
rg -n "#0d6efd|#6c757d|#212529|#f8f9fa|#dee2e6" AI-html/resource/css/ | rg -v "var\("

# Bootstrap duplicato
rg -n "bootstrap.min.css|bootstrap.bundle|cdn.jsdelivr.net/npm/bootstrap" AI-html/

# Token usati
rg -c "--bs-|--sbin-" AI-html/resource/css/
```
