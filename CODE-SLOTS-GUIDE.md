# AI-HTML — Code Slots

Versione: 1.4.0

## Scopo

I Code Slots permettono di inserire HTML, CSS e JavaScript nei punti controllati del tema senza modificare i template. Gli slot sono salvati nell'opzione WordPress `aihl_code_slots`.

## Dove si gestiscono

- Admin WordPress: `AI-HTML > Code Slots` (`admin.php?page=aihl-code-slots`).
- API JSON: `GET/POST /wp-json/aihtml/v1/ai/code-slots`.
- Export completo: `GET /wp-json/aihtml/v1/ai/code-slots/export`.
- Il Customizer governa risorse e opzioni visuali, non il codice HTML/CSS/JS degli slot.

I Canvas `header_full` e `footer_full` appartengono al tema AI-HTML, non a SBS. SBS fornisce logo e social tramite `<smart-logo>` e `<smart-social>`, evitando copie dei dati.

## Override e injection

- `header_full` e `footer_full` sono override: sostituiscono completamente il componente nativo.
- Gli altri hook aggiungono contenuto senza sostituire il tema.
- Più slot sullo stesso hook vengono ordinati per priorità crescente.

## Hook header

| Hook | Posizione |
|---|---|
| `before_header` | Prima dell'header |
| `header_full` | Sostituzione completa |
| `topbar_end` | Lato destro della topbar nativa |
| `header_start` | Prima della navbar nativa |
| `header_end` | Dopo la navbar nativa |
| `after_header` | Dopo l'intero header |

## Tipi

- `html`: usa `code`.
- `css`: usa `code`, renderizzato in `<style>`.
- `js`: usa `code`, renderizzato in `<script>`.
- `mixed`: usa `code` per HTML, `css` per CSS e `js` per JavaScript.

Per un override completo utilizzare `mixed`.

## Contesti

Valori supportati:

`global`, `front_page`, `home`, `single`, `archive`, `search`, `404`, `logged_in`, `page:{slug}`, `page:{id}`, `post_type:{type}`, `template:{name}`, `category:{slug}`, `tag:{slug}`.

La negazione usa `!`, per esempio `!logged_in`.

Più contesti separati da virgola funzionano come OR:

`front_page, page:landing`

## Header completo

Uno slot `header_full` deve includere autonomamente:

- tag semantico `<header>`;
- logo e link homepage;
- navigazione desktop;
- navigazione mobile;
- CTA;
- gestione accessibile del pulsante menu;
- CSS responsive;
- offset per la WordPress admin bar.

Non deve includere:

- `<html>`, `<head>` o `<body>`;
- `wp_head()` o `wp_footer()`;
- PHP;
- un secondo elemento `<main>`.

## Sicurezza

Il codice è salvabile solo da utenti autorizzati o API autenticate. HTML e JavaScript sono intenzionalmente liberi. Non importare slot da fonti non attendibili.

Il CSS rimuove tag `<style>` e blocca la sintassi legacy `expression()`.

## API

- `GET /wp-json/aihtml/v1/ai/code-slots`
- `POST /wp-json/aihtml/v1/ai/code-slots`
- `POST /wp-json/aihtml/v1/ai/code-slots/import`
- `GET /wp-json/aihtml/v1/ai/code-slots/export`
- `GET /wp-json/aihtml/v1/ai/code-slots/hooks`
- `GET /wp-json/aihtml/v1/ai/introspection`

## Progetto di validazione

Usare:

`demo-projects/code-slots-header-validation/`

Il progetto contiene sorgenti separati e `header-slot.json` pronto per l'importazione.

Per un esempio verticale destinato a uno studio legale usare:

`demo-projects/legal-enterprise-header/`

Il progetto include un header `header_full` responsive con topbar contatti, navigazione per competenze, pannello approfondito, CTA, gestione accessibile da tastiera e JSON pronto per l'importazione.
