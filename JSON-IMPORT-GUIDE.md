# AI-HTML — Guida import JSON

## Pacchetti distinti

AI-HTML non usa un singolo JSON universale. Un progetto completo è composto da:

| Pacchetto | Proprietario | Contenuto |
|---|---|---|
| Opzioni tema | AI-HTML | Header, footer, logo, contatti |
| Menu | AI-HTML/WordPress | Voci, gerarchie, location, rich menu |
| Pagine | WordPress | Titolo, slug, template |
| Builder | Smart Builder Site | Widget e contenuti pagina |
| Design tokens | Smart Bootstrap Manager | Colori, font, spacing, componenti |

## Schema opzioni

Lo schema aggiornato è disponibile anche via:

`GET /wp-json/aihtml/v1/ai/options/schema`

Gruppi accettati:

- `sito`;
- `media`;
- `header`;
- `mobile`;
- `footer`;
- `contatti`;
- `integrazioni`.

Tipi:

- `enum`: uno dei valori dichiarati;
- `bool`: booleano JSON o valore equivalente;
- `int`: intero limitato al range;
- `float`: numero decimale limitato al range;
- `url`: URL assoluto sanitizzato;
- `email`: indirizzo email valido;
- `text`: testo senza markup;
- `maps_html`: embed mappa filtrato.

## Esempio completo

```json
{
  "format": "aihl-options-json",
  "version": 1,
  "theme": "AI-HTML",
  "options": {
    "sito_descrizione": "Descrizione breve del progetto.",
    "site_logo_url": "https://cdn.example.com/logo-dark.png",
    "site_logo_light_url": "https://cdn.example.com/logo-light.png",
    "footer_logo_url": "https://cdn.example.com/logo-light.png",
    "header_structure": "topbar-nav",
    "header_nav_layout": "underline",
    "header_overlay_mode": "auto",
    "header_sticky_style": "solid",
    "header_show_logo": true,
    "header_show_cta": true,
    "header_show_login": false,
    "header_cta_label": "Contattaci",
    "header_cta_url": "/contatti/",
    "mobile_nav_style": "bottom-bar",
    "footer_variant": "cta-footer",
    "footer_background_enable": true,
    "footer_background_remote_url": "https://cdn.example.com/footer.jpg",
    "footer_background_opacity": 0.12,
    "footer_cta_title": "Costruiamo il prossimo progetto",
    "footer_cta_btn_label": "Inizia",
    "footer_cta_btn_url": "/contatti/",
    "contatti_telefono": "+39 000 0000000",
    "contatti_email": "info@example.com"
  }
}
```

## Import ripetibile

1. Esportare la configurazione corrente.
2. Conservare il file in controllo versione.
3. Modificare solo campi inclusi nello schema.
4. Importare opzioni.
5. Importare menu con sostituzione.
6. Importare pagine SBS.
7. Riesportare e confrontare il risultato.

## Errori comuni

| Errore | Causa | Soluzione |
|---|---|---|
| Campo ignorato | Non presente in whitelist | Consultare lo schema endpoint |
| Logo testuale | URL non valido o asset non raggiungibile | Verificare URL diretto e HTTPS |
| Menu senza voci | `type/object/object_id` incoerenti | Usare custom/custom/0 |
| Location non assegnata | ID in `locations` non presente nei menu | Usare gli stessi `term_id` simbolici |
| Video non parte | Codec, autoplay o audio | MP4 H.264, muted, playsinline |
| Footer senza sfondo | `footer_background_enable` falso | Impostarlo a true |

## Limiti

- AI-HTML non scarica automaticamente i media remoti.
- Gli URL restano dipendenti dal server sorgente.
- I token SBM hanno un proprio sistema di importazione.
- I JSON SBS devono essere importati nelle rispettive pagine.
- La creazione delle pagine via API non assegna automaticamente il JSON builder.
