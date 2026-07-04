# Studio Legale Di Caprio - Menu JSON

File pronto: `menus.json`.

## Import da admin WordPress

1. Apri `AI-HTML > Menu JSON`.
2. Sezione `Importa JSON`.
3. Carica `menus.json` oppure incolla il contenuto nel textarea.
4. Attiva `Sostituisci voci menu esistenti quando il menu è già presente`.
5. Premi `Importa JSON`.

Il payload crea e assegna:

- `topic` -> `Studio Legale Di Caprio - Menu Principale`.
- `utili` -> `Studio Legale Di Caprio - Menu Footer Utili`.
- `footer_col_1` -> `Footer - Lo Studio`.
- `footer_col_2` -> `Footer - Aree di pratica`.
- `footer_col_3` -> `Footer - Contatti`.

## Import via REST API

Endpoint:

```http
POST /wp-json/aihtml/v1/ai/menus
X-Smart-AI-Key: smart_ai_...
Content-Type: application/json; charset=utf-8
```

Body:

```json
{
  "replace_existing": true,
  "payload": { ...contenuto di menus.json... }
}
```

Nota: l'endpoint AI-HTML usa il sistema `smart_ai_*`, quindi richiede una chiave con permesso `write`. La sola WordPress Application Password può non bastare quando il sistema AI API Key è attivo.

## Scelta UI

`Aree di pratica` usa:

- `_aihl_menu_mode`: `dropdown`.
- `_aihl_menu_rich_layout`: `directory`.

Con le ultime regole del tema, se il menu è piccolo diventa un pannello compatto; se viene esteso con categorie annidate, si comporta da mega-menu directory.
