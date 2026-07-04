# Smart eCommerce Footer Code Slot

Slot `footer_full` per AI-HTML, progettato come footer enterprise/futuristic per Smart eCommerce.

## File

- `smart-ecommerce-footer-slot.json`: payload importabile dal pannello Code Slots o da REST API `POST /wp-json/aihtml/v1/ai/code-slots/import`.

## Contratto

- Non contiene `<html>`, `<head>`, `<body>`, `wp_head()` o `wp_footer()`.
- Usa HTML semantico con `<footer>`, `<section>` e `<nav>`.
- CSS scoped su `.sec-footer`.
- JS opzionale: usa `window.gsap` solo se gia caricato da Smart Bootstrap Manager.
- Nessuna libreria esterna viene caricata dallo slot.

## Stato remoto

Il sito `https://smartecommerce.it` ha risposto `404` sugli endpoint `aihtml/v1/ai/code-slots/*` con autenticazione, quindi il tema/API Code Slots non risulta ancora disponibile sul remoto. Importare questo file richiede prima deploy del tema AI-HTML aggiornato.
