# AI-HTML API Management Contract

Base REST: `/wp-json/aihtml/v1/ai`

## Principio

Ogni stato operativo governato da AI-HTML deve essere leggibile e, quando modificabile, aggiornabile via REST. Il catalogo macchina e disponibile su:

- `GET /management`
- `GET /capabilities`
- `GET /introspection`
- `GET /openapi`

L'unica eccezione intenzionale e il bootstrap delle credenziali: creazione e revoca delle Smart AI Key richiedono una sessione amministrativa WordPress, per evitare che una chiave possa creare o revocare altre credenziali.

## Copertura

| Dominio | Route principale | Gestione |
|---|---|---|
| Opzioni tema | `/options` | lettura e aggiornamento |
| Impostazioni sito | `/site/settings` | lettura e aggiornamento |
| Pagine | `/pages` | CRUD, stato, restore e contenuto |
| Sfondo pagina | `/pages/{id}/background` | lettura, aggiornamento, rimozione |
| Presentazione contenuto | `/content/{id}/presentation` | occhiello editoriale |
| Menu | `/menus` | export/import completo incluse proprieta avanzate |
| Canvas | `/canvas` | sorgente e slot selezionato per header/footer |
| Code Slot | `/code-slots` | CRUD, toggle, rollback, import/export |
| Componenti runtime | `/runtime-components/render` | rendering controllato |
| Profilo autore | `/author-profile` | lettura e stile autore |
| Dipendenze | `/dependencies` | stato e discovery API lifecycle WordPress |
| Compliance | `/compliance` | audit e score |
| Deploy | `/deploy` | configurazione progetto completa |
| Reset | `/reset/execute` | dry-run, reset e snapshot preventivo |
| Snapshot reset | `/reset/snapshots/{token}` | lettura autenticata temporanea |
| Aggiornamenti | `/update` | stato, refresh e upgrade |
| Integrazioni | `/integration-manifest` | risorse e contratti runtime |

## Autenticazione

- `X-Smart-AI-Key`: permessi `read`, `write`, `publish`.
- `X-WP-Nonce`: sessione WordPress.
- Application Password: autenticazione HTTP Basic WordPress.

L'installazione di un aggiornamento tema richiede `update_themes` e quindi nonce amministrativo o Application Password. Una Smart AI Key non riceve implicitamente tale capability.

## Verifica

La copertura e bloccante per la release:

- ogni opzione letta dal runtime deve essere presente nel registro canonico;
- ogni dominio deve comparire nel catalogo di gestione;
- ogni route operativa deve avere metadati OpenAPI;
- i payload di scrittura principali devono avere schemi OpenAPI concreti;
- test API, lint PHP e validazione pacchetto devono terminare senza errori.
