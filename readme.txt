=== AI-HTML ===
Contributors: smart-ecommerce
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.14.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-HTML e un tema enterprise AI-ready per siti corporate, magazine e
piattaforme WordPress governate da design system, JSON e REST API.

== Description ==

AI-HTML fornisce la shell WordPress dello Smart eCommerce Stack:

* header, footer, menu, logo, social e contatti governati da WordPress;
* rendering server-side e markup accessibile;
* code slot e Canvas per esperienze generate da AI;
* integrazione con Smart Bootstrap Manager e Smart Builder Site;
* opzioni leggibili e aggiornabili tramite REST API e JSON;
* preset Author Box globali e personali per ogni autore.

== Installation ==

1. Carica il file ZIP da Aspetto > Temi > Aggiungi nuovo > Carica tema.
2. Attiva AI-HTML.
3. Configura identita, navigazione e contenuti dal Customizer e dal pannello AI-HTML.
4. Installa Smart Bootstrap Manager e Smart Builder Site per abilitare l'intero stack.

== Author Box ==

I preset disponibili sono Simple, Compact, Card, Banner, Editorial,
Enterprise, Impact, Signature e Nascosto. Ogni utente puo ereditare il
valore globale oppure selezionare il proprio formato.

== Changelog ==

= 1.14.9 =
* Mostrate nel pannello Code Slots le singole violazioni che impediscono l'attivazione secondo la governance SBM.

= 1.14.8 =
* Corretto il ritorno allo slot Canvas appena salvato e mantenuta la scheda editor attiva.
* Corretto copia/incolla con CodeMirror per evitare perdita del contenuto al submit.

= 1.14.7 =
* Corretto il renderer Sfondo Pagina e il salvataggio delle opacita facoltative.

= 1.14.6 =
* Spostato Sfondo Pagina nell'area principale dell'editor e rese facoltative le opacita locali.

= 1.14.5 =
* UI coerente per Menu JSON, Accesso API, Swagger, Code Slots e Smart Reset.
* Configurazione JSON ora espone un flusso sorgente-risultato piu leggibile.

= 1.14.4 =
* Porta i KPI in apertura Dashboard e dispone a seguire integrazioni, diagnostica Canvas e strumenti.
* Raggruppa la sidebar per Configurazione, Contenuti e menu, Integrazioni, Strumenti avanzati e Governance.
* Migliora la guida Configurazione JSON con conteggio campi e gerarchia visiva più leggibile.

= 1.14.3 =
* Dispone i gruppi dei campi Configurazione JSON in sequenza verticale a tutta larghezza.
* Aumenta font, spaziatura e larghezza delle colonne per rendere leggibili nomi e valori accettati.

= 1.14.2 =
* Semplifica Manifest JSON come singola vista live senza snapshot, cronologia o ripristino.
* Sostituisce l'apertura diretta dell'endpoint protetto con copia URL e indicazioni sull'autenticazione REST.
* Introduce un layout procedurale compatto tra Configurazione JSON e Manifest live.

= 1.14.1 =
* Corregge il contrasto del visualizzatore Manifest JSON usando un pannello di codice in sola lettura stabile rispetto agli stili WordPress.
* Chiarisce la differenza e il collegamento operativo tra Configurazione JSON modificabile e Manifest JSON generato.

= 1.14.0 =
* Aggiunge la pagina Manifest JSON con lettura e copia del contratto live.
* Rinomina Opzioni JSON in Configurazione JSON e porta l'editor a tutta larghezza con guida campi non compressa.

= 1.13.2 =
* Corregge la migrazione governance Canvas: gli slot CSS globali e di supporto restano attivi e renderizzati.
* Mantiene il blocco governance solo sugli override completi header/footer non conformi.
* Ripristina automaticamente gli slot non-Canvas gia sospesi dall'aggiornamento 1.13.1.

= 1.13.1 =
* Migra in modo idempotente gli slot legacy al contratto di governance Canvas, senza riscrivere codice, revisioni o timestamp.
* Sospende gli slot legacy incompatibili con la policy SBM corrente e conserva il contenuto per la correzione, usando il fallback nativo.
* Espone nelle API e nell'Admin Hub lo stato della migrazione e la diagnostica degli asset Bootstrap e motion osservati a runtime.

= 1.13.0 =
* Subordina colori, tipografia, spacing, radius, componenti e motion alla governance di Smart Bootstrap Manager.
* Impedisce agli slot AI Canvas di adottare una modalita piu permissiva della policy SBM globale.
* Espone una verifica REST reale del contratto SBM, degli asset Bootstrap e della classificazione di tutte le 71 opzioni.

= 1.12.4 =
* Aggiunge l'editor interno AI Canvas per gestire codice HTML, CSS e JS degli override header e footer dal pannello Code Slots.
* Espone diagnostica Canvas, fallback nativo e stato menu nella dashboard, nelle API e in OpenAPI.

= 1.12.3 =
* Ripristina i menu di header e footer nei Canvas AI quando le posizioni non sono assegnate, usando alias e fallback semantici non ambigui.

= 1.12.2 =
* Ripristinata la visibilita del pannello principale AI-HTML nel Customizer gerarchico.

= 1.10.8 =
* Aggiunto il contratto REST autenticato per modificare titolo, slug, stato e template delle pagine.

= 1.10.7 =
* Protegge snapshot e credenziali AI, corregge cache updater e asset, completa OpenAPI e pulisce il pacchetto di release.

= 1.10.6 =
* Limita la bottom bar ai viewport mobili e riserva spazio solo quando visibile.

= 1.10.5 =
* Consente il ripristino governato allo stato senza menu tramite import vuoto con sostituzione esplicita.

= 1.10.4 =
* Migra tutte le pagine tema al contratto Smart Admin Panel v2 con sidebar, pathbar e layout mobile coerente.

= 1.10.3 =
* Conserva lo slug richiesto dalla AI durante la creazione delle pagine bozza e lo restituisce nella risposta REST.

= 1.10.2 =
* Mostra il controllo manuale Smart Repository anche nella schermata Temi dei siti WordPress non Multisite.

= 1.8.8 =
* Consumo del contract runtime Smart Bootstrap Manager tramite `smart_bootstrap_manager_consumer_contract('ai-html')`.
* Classi body SBM/AI-HTML per theme mode e stato motion GSAP.
* Contratto tema aggiornato con regole Bootstrap-first e motion governato.

= 1.8.7 =
* Generazione OpenAPI automatica per le API REST AI-HTML.
* Endpoint `/ai/openapi` e `/openapi` per JSON compatibile Swagger.
* Pagina admin Swagger sotto AI-HTML.
* Updater pubblico Smart Repository e workflow release per ZIP tema.

= 1.8.2 =
* Nuova presentazione enterprise nella schermata Temi.
* Metadati di compatibilita aggiornati.
* Registro Author Box completo nel Customizer e nei profili utente.
