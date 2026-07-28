# AI-HTML / Smart Bootstrap Manager - Compliance Report

Versione tema: 1.13.0

Provider verificato: Smart Bootstrap Manager 1.8.4

Contratto consumer: 1.0.0

Ultimo aggiornamento: 28 luglio 2026

## Regola di ownership

Smart Bootstrap Manager possiede:

- token `--bs-*` e `--sbin-*`;
- palette, tipografia, spacing, radius e componenti;
- Bootstrap CSS/JS;
- GSAP, page transition e runtime motion.

AI-HTML possiede contenuti, markup WordPress, scelte strutturali e valori richiesti dal tema. Il tema non dichiara token `--sbin-*` e non carica asset duplicati quando SBM e attivo.

Il tema accetta esclusivamente il contratto nativo `smart-bootstrap-manager`, major version 1, fornito da SBM 1.8.4 o successivo. Il contratto fallback non e considerato prova di compliance.

## Modalita

| Modalita | Comportamento AI-HTML |
| --- | --- |
| `governed` | I valori visuali effettivi arrivano dai token SBM. |
| `adaptive` | Le specializzazioni sono derivate da token semantici SBM. |
| `autonomous` | I valori del tema sono ammessi nello scope locale autorizzato da SBM. |

Accessibilita, contenuto server-side, ownership Bootstrap e ownership motion restano immutabili in tutte le modalita.

La modalita globale imposta da SBM e un limite non aggirabile: uno slot Canvas puo scegliere una modalita piu restrittiva, ma non puo passare da `governed` ad `adaptive` o `autonomous`, ne da `adaptive` ad `autonomous`.

## Copertura opzioni

Il registro canonico contiene 71 opzioni. L'endpoint:

```text
GET /wp-json/aihtml/v1/ai/compliance
```

restituisce per ogni opzione:

- gruppo e tipo;
- classificazione esplicita `visual` o `content`;
- dominio visuale;
- valore richiesto;
- modalita SBM;
- ereditarieta dal provider;
- stato di compliance.

La copertura e calcolata confrontando il registro corrente con le classificazioni, senza conteggi hardcoded. Un'opzione non classificata riduce il punteggio e viene restituita in `unclassified_options`.

Lo schema e lo stato delle opzioni sono disponibili anche tramite:

```text
GET /wp-json/aihtml/v1/ai/options/schema
GET /wp-json/aihtml/v1/ai/options
```

## Canvas

Ogni nuovo slot salva `design_mode`. Gli override `header_full` e `footer_full` senza dichiarazione esplicita non vengono renderizzati.

Il validatore blocca:

- modalita dello slot piu permissive della governance globale;
- dichiarazioni `--sbin-*`;
- colori, font, scale, spacing e radius raw in modalita governed;
- CSS governed privo di token semantici;
- inizializzazioni autonome WOW, GSAP, ScrollTrigger e Owl Carousel.

Gli slot non conformi sono conservati ma disattivati. Diagnostica e motivazioni sono disponibili nell'Admin Hub, nelle API Code Slots e nel payload Canvas.

## Verifiche bloccanti

- Nessuna dichiarazione `--sbin-*` nei sorgenti frontend del tema.
- Nessun alias colore legacy, colore governato raw o tracking tipografico locale nei CSS verificati.
- Bootstrap del tema solo come fallback standalone.
- Handle e fallback Bootstrap verificati dal contratto e dal loader, senza esiti hardcoded.
- Motion legacy disabilitato con SBM attivo.
- Pattern, ombre, radius, tipografia e componenti basati su token.
- Larghezza articolo subordinata a `--sbin-container-max-width`.
- Colori menu e background subordinati alla palette SBM.
- Test contrattuali PHP per governance opzioni e Canvas.
