# Smart eCommerce Shell

Header e footer AI-HTML ricostruiti dalla home pubblica `smartecommerce.it` e resi portabili tramite Code Slots.

## Importazione

1. Eseguire `php build-slots.php` dopo ogni modifica ai sorgenti.
2. Aprire `AI-HTML > Code Slots` e importare `smart-ecommerce-shell-slots.json`.
3. Impostare Header e Footer su modalita Canvas.
4. Verificare che il menu WordPress sia assegnato alla posizione `topic` e che logo, contatti e social siano configurati nel tema.

## Contratto

- I contenuti dinamici provengono da `smart-logo`, `smart-menu`, `smart-social` e `smart-contact`.
- Nessun URL social o logo viene duplicato nello slot.
- CSS limitato alle classi `sec-shell-header` e `sec-shell-footer`.
- Menu mobile accessibile da tastiera, chiusura con Escape e supporto alla reinizializzazione AI-HTML.
- Colori, tipografia, raggi e superfici derivano dai token Bootstrap/SBM con fallback.
