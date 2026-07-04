# Navigazione mobile AI-HTML — Documentazione tecnica

Versione tema: 1.4.0  
Ultimo aggiornamento: 15 giugno 2026

## 1. Obiettivo

La navigazione mobile è separata dal menu desktop. Questa separazione impedisce che Bootstrap dropdown, rich menu, hover desktop o filtri WordPress modifichino struttura e comportamento dell’offcanvas.

Il sistema garantisce:

- visualizzazione di tutte le voci principali;
- submenu verticali nel flusso del documento;
- supporto fino a tre livelli;
- logo remoto, SBS o Custom Logo WordPress;
- ricerca mobile dedicata;
- target touch di almeno 42–48 px;
- assenza di overflow orizzontale;
- funzionamento senza Smart Bootstrap Manager;
- attributi ARIA e stato accordion sincronizzati.

## 2. Componenti

| File | Responsabilità |
|---|---|
| `header.php` | Render separato di menu desktop, menu mobile, brand e ricerca |
| `inc/theme/menu-walker.php` | Walker desktop `AIHL_Nav_Menu_Walker` e walker mobile `AIHL_Mobile_Nav_Menu_Walker` |
| `inc/theme/utilities.php` | Resolver logo e brand offcanvas |
| `resource/css/ai-html.css` | Layout, contrasto, UI, responsive e stati del menu |
| `resource/js/main.js` | Controller accordion mobile |
| `tests/mobile-submenu-accordion-test.php` | Contratto strutturale walker/CSS/JS |
| `tests/mobile-offcanvas-css-test.php` | Contratto viewport, contrasto e componenti UI |
| `tests/fixtures/mobile-submenu-accordion.html` | Fixture visuale riproducibile |

## 3. Architettura del rendering

La location WordPress `topic` viene renderizzata due volte:

1. menu desktop con `AIHL_Nav_Menu_Walker`;
2. menu mobile con `AIHL_Mobile_Nav_Menu_Walker`.

Il menu desktop usa:

```text
navbar-nav aihl-desktop-menu d-none d-lg-flex
```

Il menu mobile usa:

```text
aihl-mobile-menu list-unstyled d-lg-none
```

Non riutilizzare lo stesso markup per desktop e mobile. I due sistemi hanno requisiti incompatibili:

- desktop: dropdown Bootstrap, mega menu, hover, pannelli rich;
- mobile: accordion verticale, touch, scorrimento e gerarchia lineare.

## 4. Contratto HTML mobile

```html
<ul class="aihl-mobile-menu">
  <li class="aihl-mobile-menu-item has-children">
    <div class="aihl-mobile-menu-row">
      <a class="aihl-mobile-menu-link" href="/area/">
        <span class="aihl-mobile-menu-text">Area</span>
      </a>
      <button
        class="aihl-mobile-submenu-toggle"
        type="button"
        aria-expanded="false"
        aria-controls="aihl-mobile-submenu-123"
      ></button>
    </div>
    <ul class="aihl-mobile-submenu" id="aihl-mobile-submenu-123" hidden></ul>
  </li>
</ul>
```

La classe `aihl-mobile-menu-link` viene aggiunta dopo il filtro WordPress `nav_menu_link_attributes`. Questo è necessario perché plugin o codice progetto possono sostituire la classe originale con `nav-link`.

Il CSS include anche il selettore strutturale:

```css
#offcanvasNavbar .aihl-mobile-menu-row > a
```

Il rendering rimane quindi leggibile anche quando un filtro esterno modifica la classe del link.

## 5. Accordion JavaScript

`initMobileMenuAccordion()`:

1. individua il menu dentro `#offcanvasNavbar`;
2. usa event delegation su `.aihl-mobile-submenu-toggle`;
3. apre il submenu impostando `hidden = false`;
4. aggiunge `is-open` alla voce parent;
5. aggiorna `aria-expanded`;
6. chiude i sibling dello stesso livello;
7. ripristina i submenu alla chiusura dell’offcanvas.

Il link parent resta navigabile. Il pulsante separato controlla esclusivamente il submenu.

Non aggiungere `data-bs-toggle="dropdown"` ai link mobile.

## 6. Logo offcanvas

Il brand viene generato da:

```php
aihl_render_mobile_offcanvas_brand($aihl_header_logo_variant);
```

Ordine di risoluzione:

1. URL logo AI-HTML;
2. logo Smart Builder Site;
3. Custom Logo WordPress;
4. nome sito testuale.

Il progetto Di Caprio usa un logo bianco. Per questo la testata mobile usa uno sfondo scuro indipendente dal pannello contenuti.

Se il logo non appare:

1. verificare `src`, `naturalWidth` e `naturalHeight`;
2. controllare che il file non sia bianco su sfondo bianco;
3. verificare MIME type e risposta HTTP;
4. controllare `site_logo_url`, variante header e fallback SBS;
5. svuotare cache dopo la sostituzione.

## 7. UI e accessibilità

Il pannello usa:

- testata scura per identità e contrasto;
- area contenuti chiara;
- separatori tra le voci;
- indicatore verticale per voce corrente;
- pulsante submenu indipendente;
- submenu con rientro e bordo laterale;
- campo ricerca in superficie elevata;
- `100dvh` con fallback `100vh`;
- `overscroll-behavior: contain`;
- supporto `prefers-reduced-motion`.

Requisiti:

- contrasto WCAG AA;
- target touch non inferiori a 42 px;
- focus visibile;
- ordine DOM uguale all’ordine visuale;
- nessun overflow orizzontale.

## 8. Interazioni con filtri e plugin

Il menu mobile deve funzionare anche con tutti i plugin disattivati.

Possibili interferenze:

- filtro `nav_menu_link_attributes`;
- CSS globale su `.nav-link`;
- CSS del builder su `.navbar-nav`;
- cache CSS aggregata;
- ottimizzatori che combinano file;
- vecchia copia del tema ancora attiva;
- Code Slot `header_full`, che sostituisce l’header nativo.

Quando `header_full` è attivo, il menu descritto in questo documento non viene renderizzato.

## 9. Diagnosi rapida

### Voci presenti nel DOM ma invisibili

Controllare colore calcolato, `opacity`, `visibility`, `font-size`, classe effettiva del link, selettori globali `.nav-link` e cache CSS.

### Solo Home visibile

Se tutte le righe esistono nel DOM, la causa è CSS. Se non esistono, verificare location `topic`, assegnazione menu, walker mobile, profondità e filtri `wp_nav_menu_args` o `wp_nav_menu_objects`.

### Logo assente

Verificare se l’immagine esiste nel DOM. Se esiste ma non è visibile, controllare il contrasto tra file e sfondo.

### Submenu laterale

Il markup mobile non deve contenere `.dropdown-menu` né dipendere dal posizionamento Bootstrap. Deve usare `.aihl-mobile-submenu` nel flusso verticale.

## 10. Test

```powershell
php AI-html\tests\mobile-offcanvas-css-test.php
php AI-html\tests\mobile-submenu-accordion-test.php
node --check AI-html\resource\js\main.js
```

Suite completa:

```powershell
$tests = Get-ChildItem AI-html\tests -Filter '*-test.php'
foreach ($test in $tests) {
    php $test.FullName
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}
```

Collaudo visuale:

1. viewport 390 × 844;
2. logo leggibile;
3. tutte le voci principali visibili;
4. apertura verticale del submenu;
5. voci successive raggiungibili;
6. ricerca visibile;
7. `scrollWidth === clientWidth`;
8. reset dei submenu alla chiusura.

## 11. Cache busting e rilascio

Ogni modifica CSS o JavaScript deve aggiornare `AIHL_UNICODE` in `functions.php`.

Dopo il caricamento:

1. sostituire la cartella del tema;
2. svuotare cache WordPress;
3. svuotare cache CDN/proxy;
4. rigenerare CSS aggregato;
5. eseguire hard refresh;
6. verificare il nuovo `AIHL_UNICODE`;
7. ripetere il collaudo mobile.

## 12. Regole per modifiche future

- Non reinserire controlli mobile nel walker desktop.
- Non usare Bootstrap dropdown per i submenu mobile.
- Non dipendere solo da classi modificabili dai filtri WordPress.
- Non nascondere il link parent per trasformarlo nel toggle.
- Non correggere regressioni mobile con regole sul mega menu desktop.
- Aggiornare fixture, test e questo documento dopo modifiche strutturali.

## 13. Ownership del rail mobile

Il rail non dipende dal rilevamento della hero e viene renderizzato da `inc/theme/mobile-navigation.php` quando:

- `mobile_nav_style` vale `rail`;
- `mobile_rail_enable` e attivo;
- non e presente un override Code Slot `header_full`.

Le regole di posizione e visibilita appartengono esclusivamente a `resource/css/ai-html.css`. Il bridge SBM puo applicare token grafici, ma non deve contenere selettori strutturali `.aihl-mobile-rail`.
