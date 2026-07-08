# Smart Theme Integration Contract

Versione contratto: 1.0.0  
Tema minimo: AI-HTML 1.6.0  
SBS minimo: 1.14.0

## Scopo

Il contratto dichiara a SBS, SBM e ai motori AI quali risorse WordPress sono disponibili. Un AI Canvas puo definire liberamente struttura e UI, ma non deve duplicare dati dinamici come menu, logo, social e contatti.

Endpoint:

- `GET /wp-json/aihtml/v1/ai/integration-manifest`
- `GET /wp-json/aihtml/v1/ai/addons`
- `GET /wp-json/aihtml/v1/ai/introspection`

## Contratto Smart Bootstrap Manager

Quando Smart Bootstrap Manager e attivo, AI-HTML consuma il contract runtime SBM tramite:

```php
smart_bootstrap_manager_consumer_contract('ai-html')
```

Endpoint equivalente per agenti e strumenti autorizzati:

```text
GET /wp-json/smart-bootstrap-manager/v1/ai/consumer-contract?consumer=ai-html
```

AI-HTML deve usare il contract per:

- leggere theme mode Bootstrap `light`, `dark` o `auto`;
- aggiungere le classi body consumer dichiarate da SBM;
- consumare variabili `--bs-*` e `--sbin-*`;
- evitare il caricamento duplicato di Bootstrap;
- trattare GSAP come motion runtime governato da SBM, non come dipendenza propria del tema.

Le classi body attese sono `sbin-consumer`, `sbin-consumer-ai-html`, `sbin-theme-{mode}` e una classe AI-HTML di stato motion tra `aihl-sbm-motion-gsap` e `aihl-sbm-motion-static`.

## Componenti runtime

```html
<smart-logo variant="transparent" class="site-logo"></smart-logo>
<smart-menu location="naviga" class="site-menu" depth="3"></smart-menu>
<smart-social class="site-social-link"></smart-social>
<smart-contact field="email" link="true"></smart-contact>
<smart-addon provider="contact_form_7" id="123"></smart-addon>
```

I componenti sono espansi server-side prima dell'output. I crawler ricevono HTML reale e non dipendono da JavaScript.

## Politica logo

Ordine di fallback:

1. variante richiesta: `default`, `transparent`, `light` o `footer`;
2. logo principale AI-HTML;
3. logo SBS, se disponibile;
4. logo personalizzato WordPress;
5. nome del sito come testo accessibile.

L'AI puo non usare un logo quando il progetto visuale non lo richiede. Se rappresenta l'identita del sito, deve usare `smart-logo`.

## Menu

Gli slot `header_full` e `footer_full` devono usare `smart-menu` con una location registrata. Non devono copiare link statici quando WordPress dispone di un menu assegnato.

## Sorgente header e footer

Le opzioni `header_render_mode` e `footer_render_mode` accettano:

- `native`: usa le strutture configurate dal tema;
- `canvas`: usa rispettivamente gli slot `header_full` e `footer_full`.

Uno slot Canvas deve essere attivo e compatibile con il contesto corrente. Se manca, il
tema renderizza la struttura nativa. Lo stato attivo dello slot non decide piu da solo la
sorgente della struttura.

Location dichiarate da AI-HTML:

- `topic`: navigazione principale standard;
- `naviga`: navigazione generale alternativa;
- `utili`: link utili del footer;
- `footer`: navigazione footer;
- `topic_left` e `topic_right`: header mega-centered;
- `footer_col_1` ... `footer_col_4`: colonne del mega-footer.

Il manifest indica per ogni location se un menu e assegnato, ID, nome e numero di voci. Una UI generata deve usare una location assegnata. Sul progetto Smart eCommerce la location attiva e `naviga`; le altre risultano disponibili ma non assegnate.

## Add-on

Il manifest dichiara provider, disponibilita, ID configurato, risorse selezionabili e stato. Il widget SBS associato e `addon_integration`.
