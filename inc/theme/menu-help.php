<?php
if (!defined('ABSPATH')) {
	exit;
}

/* Menu page registration moved to inc/admin/admin-hub.php (v1.2.0) */

if (!function_exists('aihl_render_rich_menu_help_page')) {
	function aihl_render_rich_menu_help_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('AI-HTML Rich Menu - Guida rapida', AIHL_TEXT_DOMAIN); ?></h1>
			<p><?php esc_html_e('Configura in Aspetto > Menu. Imposta "AIHL Menu Mode = Rich / Mega" sulla voce principale con sottomenu.', AIHL_TEXT_DOMAIN); ?></p>
			<table class="widefat striped" style="max-width: 960px;">
				<thead>
					<tr>
						<th><?php esc_html_e('Campo', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Valori consigliati', AIHL_TEXT_DOMAIN); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Icon Class</strong></td>
						<td><code>fa-solid fa-briefcase</code>, <code>fa-solid fa-chart-line</code>, <code>fa-regular fa-circle-check</code></td>
					</tr>
					<tr>
						<td><strong>Badge</strong></td>
						<td><?php esc_html_e('Testo breve (1-10 caratteri): New, Pro, Hot, 2026', AIHL_TEXT_DOMAIN); ?></td>
					</tr>
					<tr>
						<td><strong>Subtitle</strong></td>
						<td><?php esc_html_e('35-70 caratteri, orientato al beneficio utente', AIHL_TEXT_DOMAIN); ?></td>
					</tr>
					<tr>
						<td><strong>Eyebrow</strong></td>
						<td><?php esc_html_e('Etichetta corta categoria: Solutions, Products, Docs', AIHL_TEXT_DOMAIN); ?></td>
					</tr>
					<tr>
						<td><strong>Image</strong></td>
						<td><?php esc_html_e('Usa "Seleziona da Media Library" oppure URL assoluto remoto. Formati consigliati: WebP/JPG 16:9.', AIHL_TEXT_DOMAIN); ?></td>
					</tr>
					<tr>
						<td><strong>Highlight</strong></td>
						<td><?php esc_html_e('Attiva solo per 1-2 voci per menu, per mantenere gerarchia visiva pulita', AIHL_TEXT_DOMAIN); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
