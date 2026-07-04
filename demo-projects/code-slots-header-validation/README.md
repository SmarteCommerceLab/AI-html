# Header Code Slot — Progetto di validazione

Questo progetto verifica l'override completo `header_full` del sistema AI-HTML Code Slots.

## Importazione rapida

1. Aprire `AI-HTML → Code Slots`.
2. Premere `Import`.
3. Selezionare `header-slot.json`.
4. Aprire il frontend e verificare l'header.

In alternativa creare manualmente uno slot:

| Campo | Valore |
|---|---|
| Label | Header validation enterprise |
| Hook Point | Header completo (override) |
| Tipo | Mixed |
| Contesto | global |
| Priorità | 10 |
| Attivo | sì |
| Codice | contenuto di `header.html` |
| CSS | contenuto di `header.css` |
| JavaScript | contenuto di `header.js` |

## Cosa valida

- sostituzione completa dell'header nativo;
- rendering separato HTML/CSS/JS;
- logo caricato via URL;
- topbar e navbar sticky coordinate;
- offset della WordPress admin bar;
- voci menu correttamente spaziate;
- menu mobile senza dipendenza da Bootstrap JS;
- navigazione da tastiera;
- chiusura con `Escape`;
- rispetto di `prefers-reduced-motion`;
- uso dei token `--bs-*` e `--sbin-*`.

## Attenzione

Uno slot `header_full` attivo sostituisce menu WordPress, topbar, ricerca, CTA e navigazione mobile del tema. Disattivando lo slot, AI-HTML torna immediatamente all'header nativo.

Gli URL e i testi sono dimostrativi. Sostituire logo, link e contatti prima della produzione.
