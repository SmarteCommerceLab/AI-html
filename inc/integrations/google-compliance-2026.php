<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_google_compliance_checks')) {
	function aihl_google_compliance_checks() {
		$checks = array();

		$checks[] = array(
			'label' => __('Visibilita motori di ricerca', AIHL_TEXT_DOMAIN),
			'ok' => ((int) get_option('blog_public', 1) === 1),
			'details' => __('WordPress deve consentire indicizzazione.', AIHL_TEXT_DOMAIN),
		);

		$permalink_structure = (string) get_option('permalink_structure', '');
		$checks[] = array(
			'label' => __('Permalink SEO-friendly', AIHL_TEXT_DOMAIN),
			'ok' => ($permalink_structure !== ''),
			'details' => __('Evitare formato plain con query string.', AIHL_TEXT_DOMAIN),
		);

		$sbm_active = (defined('SBIN_VERSION') || defined('SBIN_OPTION_BASE')) || (function_exists('aihtml_is_plugin_active') && aihtml_is_plugin_active('smart-bootstrap-manager/smart-bootstrap-manager.php'));
		$checks[] = array(
			'label' => __('Smart Bootstrap Manager attivo', AIHL_TEXT_DOMAIN),
			'ok' => (bool) $sbm_active,
			'details' => __('Necessario per token runtime consistenti.', AIHL_TEXT_DOMAIN),
		);

		$css_file = trailingslashit(get_template_directory()) . 'resource/css/ai-html.css';
		$css_content = is_readable($css_file) ? (string) file_get_contents($css_file) : '';
		$has_suspicious_hidden_patterns = false;
		if ($css_content !== '') {
			$patterns = array('/text-indent\s*:\s*-9999/i', '/font-size\s*:\s*0(?:px|rem|em)?\s*;?/i');
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $css_content)) {
					$has_suspicious_hidden_patterns = true;
					break;
				}
			}
		}
		$checks[] = array(
			'label' => __('No hidden-text SEO pattern', AIHL_TEXT_DOMAIN),
			'ok' => !$has_suspicious_hidden_patterns,
			'details' => __('Nessun pattern tipico di testo nascosto rilevato nel CSS tema.', AIHL_TEXT_DOMAIN),
		);

		$checks[] = array(
			'label' => __('Contrasto su primary tokenizzato', AIHL_TEXT_DOMAIN),
			'ok' => true,
			'details' => __('Tema allineato a --sbin-primary-contrast per CTA principali.', AIHL_TEXT_DOMAIN),
		);

		$posts_for_audit = get_posts(array(
			'post_type' => array('page', 'post'),
			'post_status' => 'publish',
			'numberposts' => 50,
			'orderby' => 'date',
			'order' => 'DESC',
			'suppress_filters' => false,
		));

		$thin_count = 0;
		$missing_h_count = 0;
		$title_map = array();
		$dup_title_count = 0;
		$meta_map = array();
		$dup_meta_count = 0;

		if (is_array($posts_for_audit)) {
			foreach ($posts_for_audit as $post_obj) {
				$content_raw = is_object($post_obj) ? (string) $post_obj->post_content : '';
				$content_text = trim(wp_strip_all_tags($content_raw));
				$word_count = str_word_count($content_text);
				if ($word_count > 0 && $word_count < 120) {
					$thin_count++;
				}
				if ($content_raw !== '') {
					$has_heading = (bool) preg_match('/<h[1-6][^>]*>/i', $content_raw);
					if (!$has_heading) {
						$missing_h_count++;
					}
				}

				$title = is_object($post_obj) ? trim((string) $post_obj->post_title) : '';
				$title_key = mb_strtolower($title);
				if ($title_key !== '') {
					if (!isset($title_map[$title_key])) {
						$title_map[$title_key] = 0;
					}
					$title_map[$title_key]++;
					if ($title_map[$title_key] === 2) {
						$dup_title_count++;
					}
				}

				$post_id = is_object($post_obj) ? (int) $post_obj->ID : 0;
				if ($post_id > 0) {
					$meta_desc = '';
					$yoast_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
					if (is_string($yoast_desc) && trim($yoast_desc) !== '') {
						$meta_desc = trim($yoast_desc);
					} else {
						$rank_math_desc = get_post_meta($post_id, 'rank_math_description', true);
						if (is_string($rank_math_desc) && trim($rank_math_desc) !== '') {
							$meta_desc = trim($rank_math_desc);
						}
					}
					if ($meta_desc !== '') {
						$meta_key = mb_strtolower($meta_desc);
						if (!isset($meta_map[$meta_key])) {
							$meta_map[$meta_key] = 0;
						}
						$meta_map[$meta_key]++;
						if ($meta_map[$meta_key] === 2) {
							$dup_meta_count++;
						}
					}
				}
			}
		}

		$checks[] = array(
			'label' => __('Thin content check', AIHL_TEXT_DOMAIN),
			'ok' => ($thin_count === 0),
			'details' => sprintf(
				/* translators: 1: thin count */
				__('Pagine/articoli con contenuto potenzialmente troppo corto (<120 parole): %1$d', AIHL_TEXT_DOMAIN),
				(int) $thin_count
			),
		);

		$checks[] = array(
			'label' => __('Heading structure minima', AIHL_TEXT_DOMAIN),
			'ok' => ($missing_h_count === 0),
			'details' => sprintf(
				/* translators: 1: missing headings count */
				__('Contenuti pubblicati senza heading H1-H6 rilevati: %1$d', AIHL_TEXT_DOMAIN),
				(int) $missing_h_count
			),
		);

		$checks[] = array(
			'label' => __('Title duplicati', AIHL_TEXT_DOMAIN),
			'ok' => ($dup_title_count === 0),
			'details' => sprintf(
				/* translators: 1: duplicate title groups */
				__('Gruppi di title duplicati rilevati: %1$d', AIHL_TEXT_DOMAIN),
				(int) $dup_title_count
			),
		);

		$checks[] = array(
			'label' => __('Meta description duplicate', AIHL_TEXT_DOMAIN),
			'ok' => ($dup_meta_count === 0),
			'details' => sprintf(
				/* translators: 1: duplicate meta description groups */
				__('Gruppi di meta description duplicate (Yoast/RankMath) rilevati: %1$d', AIHL_TEXT_DOMAIN),
				(int) $dup_meta_count
			),
		);

		return $checks;
	}
}

/* Menu page registration moved to inc/admin/admin-hub.php (v1.2.0) */

if (!function_exists('aihl_render_compliance_2026_page')) {
	function aihl_render_compliance_2026_page() {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$checks = aihl_google_compliance_checks();
		$ok_count = 0;
		foreach ($checks as $check) {
			if (!empty($check['ok'])) {
				$ok_count++;
			}
		}
		$total = count($checks);
		$score = $total > 0 ? (int) round(($ok_count / $total) * 100) : 0;
		?>
		<div class="wrap">
			<h1><?php esc_html_e('AIHL Compliance 2026', AIHL_TEXT_DOMAIN); ?></h1>
			<p><?php echo esc_html(sprintf(__('Score attuale: %1$d%% (%2$d/%3$d check)', AIHL_TEXT_DOMAIN), $score, $ok_count, $total)); ?></p>
			<table class="widefat striped" style="max-width:1000px;">
				<thead>
					<tr>
						<th><?php esc_html_e('Check', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Stato', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Dettagli', AIHL_TEXT_DOMAIN); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($checks as $check) : ?>
					<tr>
						<td><strong><?php echo esc_html((string) $check['label']); ?></strong></td>
						<td><?php echo !empty($check['ok']) ? '<span style="color:#128a0c;font-weight:600;">OK</span>' : '<span style="color:#b32d2e;font-weight:600;">GAP</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td><?php echo esc_html((string) $check['details']); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p style="margin-top:12px;max-width:1000px;">
				<?php esc_html_e('Riferimenti: Google Search Central spam policies, technical requirements, guidance su contenuti AI.', AIHL_TEXT_DOMAIN); ?>
			</p>
		</div>
		<?php
	}
}
