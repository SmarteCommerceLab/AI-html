# Test report — Remote Media Enterprise

Data: 9 giugno 2026

## Test automatici superati

- Sintassi PHP dei file modificati.
- Whitelist Opzioni JSON: 60 campi.
- Sanitizzazione enum, float, URL e campi sconosciuti.
- Import in memoria delle opzioni.
- Export successivo e conservazione dei valori.
- Validità JSON di `theme-options.json`.
- Validità JSON di `menus.json`.
- Validità esterna e interna di `sbs-home.json`.
- Decodifica UTF-8 di tutta la documentazione Markdown.
- Risposta HTTP degli URL remoti usati da logo, immagini, poster e video.

Comando principale:

```powershell
php AI-html/tests/options-json-test.php
```

Risultato:

```text
OK AI-HTML options JSON: 60 campi verificati
```

## Test visuale da eseguire su WordPress

1. Installare il pacchetto tema 1.4.0.
2. Importare `theme-options.json`.
3. Verificare che nessun campo sia rifiutato.
4. Importare `menus.json` con sostituzione.
5. Importare `sbs-home.json` nella homepage.
6. Aprire frontend desktop a 1440px.
7. Verificare logo, topbar, navbar, mega menu e footer.
8. Scorrere la pagina e controllare sticky/scroll-away.
9. Aprire viewport 390×844 e controllare bottom bar/offcanvas.
10. Simulare `prefers-reduced-motion`.
11. Navigare menu e CTA solo con tastiera.
12. Verificare Network: nessun asset remoto 404.

## Criteri di accettazione

- Logo remoto visibile in header e footer.
- Nessuna dipendenza obbligatoria dalla Media Library.
- Nessuna voce menu concatenata.
- Topbar e navbar senza offset sospesi.
- Video hero riprodotto muted con poster di fallback.
- Background footer remoto leggibile con overlay.
- Menu rich apribile con mouse e tastiera.
- Nessun errore PHP o JavaScript in console.

## Limite del test corrente

La workspace non contiene un'installazione WordPress eseguibile. Il rendering finale non può essere certificato senza importare il progetto in un ambiente WordPress con AI-HTML, SBM, SCF e SBS attivi.
