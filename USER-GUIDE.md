# AI-HTML — Manuale d'uso

Versione: 1.4.0  
Ultimo aggiornamento: 9 giugno 2026

## 1. Scopo

AI-HTML gestisce la shell globale di un sito WordPress:

- identità e logo;
- header, topbar, ricerca e navigazione mobile;
- menu standard e rich/mega menu;
- footer, CTA, contatti, trust bar e sfondi;
- template blog e integrazione con Smart Builder Site.

Le pagine visuali sono normalmente costruite con Smart Builder Site. Palette, tipografia e componenti Bootstrap sono gestiti da Smart Bootstrap Manager.

## 2. Requisiti consigliati

1. Tema AI-HTML attivo.
2. Smart Bootstrap Manager attivo.
3. Smart Customizer Frameworks attivo.
4. Smart Builder Site attivo per le pagine builder.
5. Permesso WordPress `edit_theme_options`.

## 3. Ordine corretto per creare un progetto

1. Definire identità, URL del logo e contatti.
2. Importare le opzioni AI-HTML.
3. Creare o importare i menu.
4. Verificare le assegnazioni alle location.
5. Creare le pagine WordPress.
6. Importare i JSON Smart Builder Site.
7. Configurare i token Smart Bootstrap Manager.
8. Verificare desktop, tablet, mobile e accessibilità.
9. Esportare nuovamente opzioni e menu come backup del progetto.

## 4. Logo via URL

AI-HTML supporta quattro URL:

| Campo | Uso |
|---|---|
| `site_logo_url` | Logo principale |
| `site_logo_transparent_url` | Variante opzionale per header overlay |
| `site_logo_light_url` | Variante opzionale per fondi scuri |
| `footer_logo_url` | Variante opzionale per footer |

Precedenza:

1. URL configurato in AI-HTML.
2. Logo configurato in Smart Builder Site.
3. Custom Logo WordPress.
4. Nome del sito come testo.

Gli URL devono essere assoluti e HTTPS. SVG remoti sono visualizzabili come immagini, ma il server sorgente deve inviare il MIME type corretto.

## 5. Importare opzioni

Aprire:

`AI-HTML → Opzioni JSON`

Incollare un payload:

```json
{
  "format": "aihl-options-json",
  "version": 1,
  "options": {
    "site_logo_url": "https://example.com/logo.png",
    "header_structure": "topbar-nav",
    "footer_variant": "cta-footer"
  }
}
```

Premere **Salva opzioni**. Il sistema comunica quanti campi sono stati applicati e quali sono stati ignorati.

È accettato anche un oggetto piatto senza proprietà `options`.

## 6. Importare menu

Aprire:

`AI-HTML → Menu JSON`

Il file deve contenere:

- `locations`: associazione tra location e vecchio ID menu;
- `menus`: elenco menu;
- `items`: voci;
- `meta`: impostazioni AI-HTML della singola voce.

Per URL indipendenti dal database usare:

```json
{
  "type": "custom",
  "object": "custom",
  "object_id": 0,
  "url": "https://example.com/servizi/"
}
```

Attivare **Sostituisci voci menu esistenti** durante aggiornamenti ripetibili.

## 7. Location menu

| Location | Funzione |
|---|---|
| `topic` | Navigazione principale |
| `naviga` | Topbar |
| `utili` | Link legali/footer |
| `topic_left` | Menu sinistro mega-centered |
| `topic_right` | Menu destro mega-centered |
| `footer_col_1..4` | Colonne mega-footer |

## 8. Media remote

Possono essere configurati via URL:

- logo e varianti;
- immagine di sfondo footer;
- immagini delle voci rich menu;
- immagini e video dei widget SBS.

Requisiti:

- HTTPS;
- URL diretto al file, non a una pagina HTML;
- server con hotlink consentito;
- CORS corretto per asset che lo richiedono;
- immagini con dimensioni adeguate;
- video MP4 H.264, muted e playsinline per autoplay.

Per produzione è preferibile controllare gli asset sul proprio CDN. Gli URL esterni sono utili per demo, provisioning e progetti centralizzati.

## 9. Header

Le strutture disponibili sono:

`standard`, `dualbar`, `centered`, `topbar-nav`, `mega-centered`, `sidebar`, `triple-row`, `stacked-centered`.

Per una configurazione stabile:

- usare `solid` o `blur` per sticky header;
- usare `transparent` solo con hero sufficientemente scura;
- usare `topbar-nav` quando telefono, email o utility devono restare separati;
- evitare più di 6–7 voci principali.

## 10. Footer

Le varianti disponibili sono:

`enterprise`, `futuristic`, `corporate`, `compact`, `mega-footer`, `minimal`, `cta-footer`.

Il footer può includere:

- logo remoto;
- sfondo remoto;
- overlay;
- CTA con due bottoni;
- fino a cinque elementi distintivi con icona Font Awesome 6;
- newsletter;
- contatti;
- menu a colonne.

## 11. Export e trasferimento

Per trasferire un progetto servono almeno:

1. export Opzioni JSON;
2. export Menu JSON;
3. JSON delle pagine SBS;
4. elenco token SBM;
5. elenco URL media;
6. manifest pagine e template.

Il solo export delle opzioni non crea pagine o contenuti SBS.

## 12. Collaudo minimo

- Logo visibile in header desktop e mobile.
- Logo footer visibile.
- Nessun testo menu concatenato.
- Topbar e navbar allineate durante lo scroll.
- CTA e login coerenti con i toggle.
- Mega menu utilizzabile da tastiera.
- Video con poster e fallback.
- Footer background visibile senza compromettere il contrasto.
- Marker dei link footer visibili anche se il font icone non viene caricato.
- Tutte le location menu assegnate.
- Nessun campo JSON rifiutato inaspettatamente.

Usare il progetto:

`demo-projects/remote-media-enterprise/`

come collaudo end-to-end.

## 13. Code Slots

I Code Slots consentono personalizzazioni fuori dai template. Per sostituire completamente l'header:

1. aprire `AI-HTML → Code Slots`;
2. creare o importare uno slot;
3. scegliere `Header completo (override)`;
4. scegliere il tipo `Mixed`;
5. inserire HTML, CSS e JavaScript nei rispettivi campi;
6. salvare e verificare frontend desktop/mobile.

Il progetto pronto è in:

`demo-projects/code-slots-header-validation/`

La guida completa è `CODE-SLOTS-GUIDE.md`.
