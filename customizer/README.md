# Legacy Customizer Wrappers

**Non usare questa cartella per nuovo codice.**

`customizer/` contiene file wrapper per retrocompatibilita. L'implementazione attiva risiede in:

- `inc/customizer/panel.php` — panel principale
- `inc/customizer/section.php` — sezioni e controlli (50+ controlli: header, footer, sito, articoli, contatti, mailchimp)
- `inc/customizer/reset.php` — reset opzioni

Per nuova logica Customizer lavorare solo in `inc/customizer/`.

Questi wrapper saranno rimossi in una major version futura dopo audit delle dipendenze esterne.

Ultimo aggiornamento: v1.4.0 — 9 giugno 2026
