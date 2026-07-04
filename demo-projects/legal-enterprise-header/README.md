# Header enterprise per professionista legale

Slot completo progettato per Studio Legale Di Caprio e per la validazione dell'hook `header_full`.

## Importazione

1. Aprire `AI-HTML → Code Slots`.
2. Usare la funzione `Import`.
3. Caricare `legal-enterprise-header-slot.json`.
4. Verificare che lo slot sia `Mixed`, `global`, priorità `10` e attivo.
5. Disattivare eventuali altri slot `header_full`.

## Dati da verificare prima della produzione

- telefono ed email;
- slug delle pagine;
- denominazione professionale;
- sedi nella topbar;
- eventuale indirizzo email professionale sul dominio.

## Scelte SEO e accessibilità

- tutti i collegamenti sono elementi `<a href>` presenti nel DOM iniziale;
- le etichette descrivono chiaramente la destinazione;
- desktop e mobile condividono lo stesso HTML;
- il menu JavaScript modifica soltanto visibilità e attributi ARIA;
- è presente il link per saltare al contenuto `#main`;
- non viene inserito un secondo `<main>` e non viene aggiunto un `<h1>`;
- il logo è testuale, quindi non dipende da immagini remote e resta leggibile;
- focus visibile, chiusura con `Escape` e supporto `prefers-reduced-motion`;
- sticky header e topbar usano un unico contenitore, con offset WordPress admin bar;
- nessuna promessa di risultato o formulazione commerciale aggressiva.

## Dati strutturati

Non sono inseriti nello slot per evitare duplicazioni con Yoast, Rank Math o Smart SEO Dots. Configurare a livello SEO:

- `WebSite` sulla homepage;
- `Organization` o `LegalService` con denominazione, URL, logo, telefono e sedi reali;
- `Person` per il profilo professionale;
- `BreadcrumbList` sulle pagine interne;
- autore e data verificabili per gli approfondimenti.

## Fonti progettuali

Il pattern combina la navigazione orientata a competenze e insight osservata in studi come Linklaters e Clifford Chance con una gerarchia più adatta a una boutique italiana. Le regole tecniche seguono Google Search Central per link scansionabili, mobile-first indexing e requisiti tecnici di indicizzazione.
