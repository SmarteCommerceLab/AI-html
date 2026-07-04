# Legacy Wrapper Directory

**Non usare questa cartella per nuovo codice.**

`lib/` e un layer di compatibilita. I file in questa cartella sono wrapper che fanno forward ai moduli attivi sotto:

- `inc/theme/*`
- `inc/integrations/*`

Per nuovo sviluppo usare esclusivamente `inc/*` e aggiornare `inc/core/bootstrap.php`.

Questi wrapper saranno rimossi in una major version futura dopo audit delle dipendenze esterne.

Ultimo aggiornamento: v1.4.0 — 9 giugno 2026
