=== AI-HTML ===
Contributors: smart-ecommerce
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.10.2
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
