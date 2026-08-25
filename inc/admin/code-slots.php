<?php
/**
 * AI-HTML Code Slots System
 *
 * Permette di iniettare codice libero (HTML/CSS/JS) in punti specifici
 * del tema tramite API, JSON e pannello admin. Progettato per dare
 * massima flessibilita agli agenti AI nella personalizzazione del tema.
 *
 * Storage: wp_option 'aihl_code_slots' (array di slot)
 *
 * @since 1.4.0
 */
if (!defined('ABSPATH')) {
	exit;
}

/* ============================================================================
 * 1. Hook Points — definizione di tutti i punti di aggancio disponibili
 * ============================================================================ */

if (!function_exists('aihl_code_slots_hooks')) {
	function aihl_code_slots_hooks() {
		return array(
			// Head
			'head_start'      => array('group' => 'head',    'label' => __('Head — inizio', AIHL_TEXT_DOMAIN),         'description' => __('Dentro <head>, subito dopo charset. Per meta, preload, early CSS.', AIHL_TEXT_DOMAIN)),
			'head_end'        => array('group' => 'head',    'label' => __('Head — fine', AIHL_TEXT_DOMAIN),            'description' => __('Dentro <head>, prima di wp_head(). Per analytics, fonts, late CSS.', AIHL_TEXT_DOMAIN)),
			// Body
			'body_start'      => array('group' => 'body',    'label' => __('Body — inizio', AIHL_TEXT_DOMAIN),          'description' => __('Subito dopo <body>. Per overlay, loader, GTM noscript.', AIHL_TEXT_DOMAIN)),
			'body_end'        => array('group' => 'body',    'label' => __('Body — fine', AIHL_TEXT_DOMAIN),            'description' => __('Prima di </body>. Per script, tracking, modal.', AIHL_TEXT_DOMAIN)),
			// Header — override completo
			'header_full'     => array('group' => 'header',  'label' => __('Header completo (override)', AIHL_TEXT_DOMAIN), 'description' => __('SOSTITUISCE l\'intero header nativo del tema. Topbar, logo, nav, CTA — tutto viene rimpiazzato dal codice di questo slot.', AIHL_TEXT_DOMAIN), 'override' => true),
			// Header — injection
			'before_header'   => array('group' => 'header',  'label' => __('Prima dell\'header', AIHL_TEXT_DOMAIN),     'description' => __('Prima del blocco header. Per announcement bar, promo banner.', AIHL_TEXT_DOMAIN)),
			'after_header'    => array('group' => 'header',  'label' => __('Dopo l\'header', AIHL_TEXT_DOMAIN),         'description' => __('Dopo l\'header completo. Per breadcrumb, hero, sub-nav.', AIHL_TEXT_DOMAIN)),
			'topbar_end'      => array('group' => 'header',  'label' => __('Topbar — fine', AIHL_TEXT_DOMAIN),          'description' => __('Dentro la topbar, lato destro. Per badge, link extra.', AIHL_TEXT_DOMAIN)),
			'header_start'    => array('group' => 'header',  'label' => __('Header — inizio', AIHL_TEXT_DOMAIN),       'description' => __('Dentro l\'area header, dopo topbar/brand-bar, prima della navbar. Per promo strip, sub-nav.', AIHL_TEXT_DOMAIN)),
			'header_end'      => array('group' => 'header',  'label' => __('Header — fine', AIHL_TEXT_DOMAIN),         'description' => __('Dentro l\'area header, dopo la navbar chiusa. Per ticker, second nav, widget header.', AIHL_TEXT_DOMAIN)),
			// Content
			'before_content'  => array('group' => 'content', 'label' => __('Prima del contenuto', AIHL_TEXT_DOMAIN),    'description' => __('Prima del main content. Per banner, filtri, intro.', AIHL_TEXT_DOMAIN)),
			'after_content'   => array('group' => 'content', 'label' => __('Dopo il contenuto', AIHL_TEXT_DOMAIN),      'description' => __('Dopo il main content. Per related, newsletter, CTA.', AIHL_TEXT_DOMAIN)),
			// Footer — override completo
			'footer_full'     => array('group' => 'footer',  'label' => __('Footer completo (override)', AIHL_TEXT_DOMAIN), 'description' => __('SOSTITUISCE l\'intero footer nativo del tema. Widget, menu, social, copyright — tutto viene rimpiazzato dal codice di questo slot.', AIHL_TEXT_DOMAIN), 'override' => true),
			// Footer — injection
			'before_footer'   => array('group' => 'footer',  'label' => __('Prima del footer', AIHL_TEXT_DOMAIN),       'description' => __('Prima del blocco footer. Per pre-footer CTA, mappa.', AIHL_TEXT_DOMAIN)),
			'footer_start'    => array('group' => 'footer',  'label' => __('Footer — inizio', AIHL_TEXT_DOMAIN),        'description' => __('Dentro il tag <footer>, subito dopo apertura. Per widget top footer, mappa.', AIHL_TEXT_DOMAIN)),
			'footer_end'      => array('group' => 'footer',  'label' => __('Footer — fine', AIHL_TEXT_DOMAIN),          'description' => __('Dentro il tag <footer>, prima della chiusura. Per credits extra, badge, legal.', AIHL_TEXT_DOMAIN)),
			'after_footer'    => array('group' => 'footer',  'label' => __('Dopo il footer', AIHL_TEXT_DOMAIN),          'description' => __('Dopo il footer. Per cookie bar, chat widget.', AIHL_TEXT_DOMAIN)),
			// Globali (iniettati via wp_head/wp_footer)
			'global_css'      => array('group' => 'global',  'label' => __('CSS globale', AIHL_TEXT_DOMAIN),             'description' => __('Foglio CSS aggiuntivo, iniettato in <head>.', AIHL_TEXT_DOMAIN)),
			'global_js'       => array('group' => 'global',  'label' => __('JS globale', AIHL_TEXT_DOMAIN),              'description' => __('Script globale, iniettato prima di </body>.', AIHL_TEXT_DOMAIN)),
		);
	}
}

/* ============================================================================
 * 2. Context System — valuta se uno slot è attivo nella pagina corrente
 * ============================================================================ */

if (!function_exists('aihl_code_slot_context_matches')) {
	function aihl_code_slot_context_matches($context) {
		// Global = sempre attivo
		if ('global' === $context || empty($context)) {
			return true;
		}

		// Array di contesti: basta che uno matchi
		if (is_array($context)) {
			foreach ($context as $ctx) {
				if (aihl_code_slot_context_matches($ctx)) {
					return true;
				}
			}
			return false;
		}

		$context = (string) $context;

		// Lista separata da virgole: basta che un contesto corrisponda.
		if (false !== strpos($context, ',')) {
			$contexts = array_filter(array_map('trim', explode(',', $context)));
			return aihl_code_slot_context_matches($contexts);
		}

		// Negazione
		if (0 === strpos($context, '!')) {
			return !aihl_code_slot_context_matches(substr($context, 1));
		}

		// Contesti specifici
		switch ($context) {
			case 'front_page':
				return is_front_page();
			case 'home':
				return is_home();
			case 'single':
				return is_single();
			case 'archive':
				return is_archive();
			case 'search':
				return is_search();
			case '404':
				return is_404();
			case 'logged_in':
				return is_user_logged_in();
		}

		// page:{slug} o page:{id}
		if (0 === strpos($context, 'page:')) {
			$val = substr($context, 5);
			if (is_numeric($val)) {
				return is_page((int) $val);
			}
			return is_page($val);
		}

		// post_type:{type}
		if (0 === strpos($context, 'post_type:')) {
			return is_singular(substr($context, 10));
		}

		// template:{name}
		if (0 === strpos($context, 'template:')) {
			$tpl = substr($context, 9);
			return is_page_template($tpl) || is_page_template($tpl . '.php');
		}

		// category:{slug}
		if (0 === strpos($context, 'category:')) {
			return is_category(substr($context, 9));
		}

		// tag:{slug}
		if (0 === strpos($context, 'tag:')) {
			return is_tag(substr($context, 4));
		}

		return false;
	}
}

/* ============================================================================
 * 3. Storage — CRUD sugli slot salvati in wp_option
 * ============================================================================ */

define('AIHL_CODE_SLOTS_OPTION', 'aihl_code_slots');
define('AIHL_CODE_SLOTS_GOVERNANCE_MIGRATION_OPTION', 'aihl_code_slots_governance_migration_1131');
define('AIHL_CODE_SLOTS_GOVERNANCE_REPAIR_OPTION', 'aihl_code_slots_governance_repair_1132');

if (!function_exists('aihl_code_slots_get_all')) {
	function aihl_code_slots_get_all() {
		$slots = get_option(AIHL_CODE_SLOTS_OPTION, array());
		return is_array($slots) ? $slots : array();
	}
}

if (!function_exists('aihl_code_slots_get')) {
	function aihl_code_slots_get(string $id) {
		$slots = aihl_code_slots_get_all();
		return $slots[$id] ?? null;
	}
}

if (!function_exists('aihl_code_slot_is_canvas_override')) {
	function aihl_code_slot_is_canvas_override(array $slot): bool {
		return in_array((string) ($slot['hook'] ?? ''), array('header_full', 'footer_full'), true);
	}
}

if (!function_exists('aihl_code_slots_governance_migration_report')) {
	function aihl_code_slots_governance_migration_report(): array {
		$report = get_option(AIHL_CODE_SLOTS_GOVERNANCE_MIGRATION_OPTION, array());
		return is_array($report) ? $report : array();
	}
}

if (!function_exists('aihl_migrate_legacy_code_slot_governance')) {
	/**
	 * Adds governance metadata to pre-1.13 Canvas slots without rewriting them.
	 */
	function aihl_migrate_legacy_code_slot_governance(): array {
		$existing_report = aihl_code_slots_governance_migration_report();
		if (!empty($existing_report['completed'])) {
			return $existing_report;
		}

		$slots = get_option(AIHL_CODE_SLOTS_OPTION, array());
		$slots = is_array($slots) ? $slots : array();
		$design_mode = function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous';
		if (!in_array($design_mode, array('governed', 'adaptive', 'autonomous'), true)) {
			$design_mode = 'autonomous';
		}

		$migrated = array();
		$deactivated = array();
		$issues = array();

		foreach ($slots as $id => $slot) {
			if (!is_array($slot)) {
				continue;
			}
			$declared_mode = sanitize_key((string) ($slot['design_mode'] ?? ''));
			if (in_array($declared_mode, array('governed', 'adaptive', 'autonomous'), true)) {
				continue;
			}

			$slot['design_mode'] = $design_mode;
			$migrated[] = (string) $id;

			if (aihl_code_slot_is_canvas_override($slot) && !empty($slot['active']) && function_exists('aihl_code_slot_governance_report')) {
				$governance = aihl_code_slot_governance_report($slot);
				if (empty($governance['valid'])) {
					$slot['active'] = false;
					$deactivated[] = (string) $id;
					$issues[(string) $id] = array_values($governance['issues'] ?? array());
				}
			}

			$slots[$id] = $slot;
		}

		if ($migrated) {
			update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);
		}

		$report = array(
			'completed' => true,
			'target_version' => '1.13.1',
			'completed_at' => current_time('mysql'),
			'design_mode' => $design_mode,
			'migrated_count' => count($migrated),
			'migrated_slot_ids' => $migrated,
			'deactivated_count' => count($deactivated),
			'deactivated_slot_ids' => $deactivated,
			'issues' => $issues,
		);
		update_option(AIHL_CODE_SLOTS_GOVERNANCE_MIGRATION_OPTION, $report, false);

		return $report;
	}
}
add_action('after_setup_theme', 'aihl_migrate_legacy_code_slot_governance', 60);

if (!function_exists('aihl_repair_non_canvas_slot_governance')) {
	/**
	 * Restores non-Canvas slots incorrectly suspended by the 1.13.1 migration.
	 */
	function aihl_repair_non_canvas_slot_governance(): array {
		$existing_report = get_option(AIHL_CODE_SLOTS_GOVERNANCE_REPAIR_OPTION, array());
		if (is_array($existing_report) && !empty($existing_report['completed'])) {
			return $existing_report;
		}

		$migration = aihl_code_slots_governance_migration_report();
		$slots = aihl_code_slots_get_all();
		$restored = array();
		$candidates = isset($migration['deactivated_slot_ids']) && is_array($migration['deactivated_slot_ids'])
			? $migration['deactivated_slot_ids']
			: array();

		foreach ($candidates as $id) {
			$id = sanitize_key((string) $id);
			if ($id === '' || !isset($slots[$id]) || !is_array($slots[$id])) {
				continue;
			}
			if (aihl_code_slot_is_canvas_override($slots[$id]) || !empty($slots[$id]['active'])) {
				continue;
			}
			$slots[$id]['active'] = true;
			$restored[] = $id;
		}

		if ($restored) {
			update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);
		}

		$report = array(
			'completed' => true,
			'target_version' => '1.13.2',
			'completed_at' => current_time('mysql'),
			'restored_count' => count($restored),
			'restored_slot_ids' => $restored,
		);
		update_option(AIHL_CODE_SLOTS_GOVERNANCE_REPAIR_OPTION, $report, false);

		return $report;
	}
}
add_action('after_setup_theme', 'aihl_repair_non_canvas_slot_governance', 61);

if (!function_exists('aihl_code_slots_save')) {
	/**
	 * Salva o aggiorna uno slot.
	 *
	 * @param array $slot Dati dello slot.
	 * @return array|WP_Error Lo slot salvato con versioning, o errore.
	 */
	function aihl_code_slots_save(array $slot) {
		$hooks = aihl_code_slots_hooks();

		// Validazione obbligatori
		if (empty($slot['hook']) || !isset($hooks[$slot['hook']])) {
			return new WP_Error('invalid_hook', __('Hook point non valido.', AIHL_TEXT_DOMAIN));
		}
		$allowed_types = array('html', 'css', 'js', 'mixed');
		$type = isset($slot['type']) && in_array($slot['type'], $allowed_types, true) ? $slot['type'] : 'html';

		// Genera ID se mancante
		$id = !empty($slot['id']) ? sanitize_key($slot['id']) : sanitize_key(($slot['label'] ?? 'slot') . '-' . wp_generate_password(6, false));

		$slots = aihl_code_slots_get_all();
		$existing = $slots[$id] ?? null;
		$version = $existing ? (int) ($existing['version'] ?? 0) + 1 : 1;
		$design_mode = sanitize_key((string) ($slot['design_mode'] ?? ($existing['design_mode'] ?? '')));
		if (!in_array($design_mode, array('governed', 'adaptive', 'autonomous'), true)) {
			$design_mode = function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous';
		}

		// Sanitizzazione codice per tipo
		$code = $slot['code'] ?? '';
		$css = $slot['css'] ?? '';
		$js = $slot['js'] ?? '';

		// CSS: rimuovi tag <style> e expression()
		$css = preg_replace('/<\/?style[^>]*>/i', '', $css);
		$css = preg_replace('/expression\s*\(/i', '/* blocked */(', $css);

		// Conserva una revisione completa: un rollback deve ripristinare anche
		// hook, contesto, priorita e asset CSS/JS.
		$previous_revision = null;
		if (is_array($existing)) {
			$previous_revision = $existing;
			unset($previous_revision['previous_revision'], $previous_revision['previous_code']);
		}

		$clean = array(
			'id'            => $id,
			'hook'          => sanitize_key($slot['hook']),
			'type'          => $type,
			'code'          => $code,
			'css'           => $css,
			'js'            => $js,
			'context'       => $slot['context'] ?? 'global',
			'design_mode'   => $design_mode,
			'priority'      => isset($slot['priority']) ? max(1, min(999, (int) $slot['priority'])) : 10,
			'active'        => isset($slot['active']) ? (bool) $slot['active'] : true,
			'label'         => sanitize_text_field($slot['label'] ?? $id),
			'author'        => sanitize_text_field($slot['author'] ?? (wp_get_current_user()->user_login ?: 'system')),
			'version'       => $version,
			'previous_revision' => $previous_revision,
			'previous_code' => $existing['code'] ?? '', // Compatibilita con export precedenti.
			'created'       => $existing['created'] ?? current_time('mysql'),
			'updated'       => current_time('mysql'),
		);

		if (aihl_code_slot_is_canvas_override($clean) && !empty($clean['active']) && function_exists('aihl_code_slot_governance_report')) {
			$governance_report = aihl_code_slot_governance_report($clean);
			if (!$governance_report['valid']) {
				$clean['active'] = false;
			}
		}

		$slots[$id] = $clean;
		update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);

		return $clean;
	}
}

if (!function_exists('aihl_code_slots_delete')) {
	function aihl_code_slots_delete(string $id) {
		$slots = aihl_code_slots_get_all();
		if (!isset($slots[$id])) {
			return new WP_Error('not_found', __('Slot non trovato.', AIHL_TEXT_DOMAIN));
		}
		$removed = $slots[$id];
		unset($slots[$id]);
		update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);
		return $removed;
	}
}

if (!function_exists('aihl_code_slots_toggle')) {
	function aihl_code_slots_toggle(string $id, bool $active) {
		$slots = aihl_code_slots_get_all();
		if (!isset($slots[$id])) {
			return new WP_Error('not_found', __('Slot non trovato.', AIHL_TEXT_DOMAIN));
		}
		if ($active && aihl_code_slot_is_canvas_override($slots[$id]) && function_exists('aihl_code_slot_governance_report')) {
			$governance_report = aihl_code_slot_governance_report($slots[$id]);
			if (!$governance_report['valid']) {
				return new WP_Error(
					'aihl_slot_governance_failed',
					__('Lo slot non puo essere attivato finche non supera la governance SBM.', AIHL_TEXT_DOMAIN),
					array('status' => 409, 'governance' => $governance_report)
				);
			}
		}
		$slots[$id]['active'] = $active;
		$slots[$id]['updated'] = current_time('mysql');
		update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);
		return $slots[$id];
	}
}

if (!function_exists('aihl_code_slots_rollback')) {
	function aihl_code_slots_rollback(string $id) {
		$slots = aihl_code_slots_get_all();
		if (!isset($slots[$id])) {
			return new WP_Error('not_found', __('Slot non trovato.', AIHL_TEXT_DOMAIN));
		}
		$slot = $slots[$id];
		$previous = isset($slot['previous_revision']) && is_array($slot['previous_revision'])
			? $slot['previous_revision']
			: null;
		if (!$previous && !empty($slot['previous_code'])) {
			$previous = $slot;
			$previous['code'] = $slot['previous_code'];
			$previous['previous_code'] = $slot['code'] ?? '';
			unset($previous['previous_revision']);
		}
		if (!$previous) {
			return new WP_Error('no_previous', __('Nessuna versione precedente disponibile.', AIHL_TEXT_DOMAIN));
		}
		$current = $slot;
		unset($current['previous_revision'], $current['previous_code']);
		$previous['id'] = $id;
		$previous['previous_revision'] = $current;
		$previous['previous_code'] = $current['code'] ?? '';
		$previous['version'] = (int) ($slot['version'] ?? 0) + 1;
		$previous['updated'] = current_time('mysql');
		$slots[$id] = $previous;
		update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);
		return $slots[$id];
	}
}

/* ============================================================================
 * 3b. Override check — verifica se un hook override ha slot attivi
 * ============================================================================ */

if (!function_exists('aihl_code_slots_get_admin_canvas_slot')) {
	/**
	 * Restituisce lo slot Canvas piu rilevante per l'editor admin.
	 * Non valuta il contesto pubblico: qui serve aprire il codice salvato.
	 */
	function aihl_code_slots_get_admin_canvas_slot(string $area): ?array {
		$area = in_array($area, array('header', 'footer'), true) ? $area : 'header';
		$hook = $area . '_full';
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		$selected_id = is_array($options) ? sanitize_key((string) ($options[$area . '_canvas_slot_id'] ?? '')) : '';
		$matches = array();

		foreach (aihl_code_slots_get_all() as $id => $slot) {
			if (($slot['hook'] ?? '') !== $hook) {
				continue;
			}
			$slot['id'] = (string) ($slot['id'] ?? $id);
			if ($selected_id !== '' && $slot['id'] === $selected_id) {
				return $slot;
			}
			$matches[] = $slot;
		}

		if (!$matches) {
			return null;
		}

		usort($matches, static function (array $a, array $b): int {
			$active = (int) !empty($b['active']) <=> (int) !empty($a['active']);
			if ($active !== 0) {
				return $active;
			}
			$priority = ((int) ($a['priority'] ?? 10)) <=> ((int) ($b['priority'] ?? 10));
			return $priority !== 0 ? $priority : strcmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
		});

		return $matches[0];
	}
}

if (!function_exists('aihl_code_slot_governance_report')) {
	/**
	 * Validate a slot against SBM design and motion ownership.
	 *
	 * @return array{valid:bool,mode:string,declared:bool,issues:array<int,array<string,string>>}
	 */
	function aihl_code_slot_governance_report(array $slot): array {
		$declared_mode = sanitize_key((string) ($slot['design_mode'] ?? ''));
		$declared = in_array($declared_mode, array('governed', 'adaptive', 'autonomous'), true);
		$requested_mode = $declared
			? $declared_mode
			: (function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous');
		$constraint = function_exists('aihl_sbm_constrain_design_mode')
			? aihl_sbm_constrain_design_mode($requested_mode)
			: array(
				'requested' => $requested_mode,
				'global' => $requested_mode,
				'effective' => $requested_mode,
				'allowed' => true,
			);
		$mode = (string) $constraint['effective'];
		$hook = sanitize_key((string) ($slot['hook'] ?? ''));
		$type = sanitize_key((string) ($slot['type'] ?? 'html'));
		$code = (string) ($slot['code'] ?? '');
		$css = 'css' === $type ? $code : (string) ($slot['css'] ?? '');
		$js = 'js' === $type ? $code : (string) ($slot['js'] ?? '');
		$issues = array();

		if (in_array($hook, array('header_full', 'footer_full'), true) && !$declared) {
			$issues[] = array(
				'code' => 'design_mode_missing',
				'severity' => 'error',
				'message' => __('Il Canvas deve dichiarare design_mode.', AIHL_TEXT_DOMAIN),
			);
		}

		if (empty($constraint['allowed'])) {
			$issues[] = array(
				'code' => 'design_mode_exceeds_global_policy',
				'severity' => 'error',
				'message' => sprintf(
					/* translators: 1: requested mode, 2: global SBM mode. */
					__('La modalita Canvas %1$s e piu permissiva della governance SBM globale %2$s.', AIHL_TEXT_DOMAIN),
					(string) $constraint['requested'],
					(string) $constraint['global']
				),
			);
		}

		$style_sources = $css . "\n";
		if (preg_match_all('/\bstyle\s*=\s*(["\'])(.*?)\1/is', $code, $inline_matches)) {
			$style_sources .= implode(";\n", $inline_matches[2]);
		}
		if (preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $code, $style_matches)) {
			$style_sources .= "\n" . implode("\n", $style_matches[1]);
		}

		if (preg_match('/--sbin-[a-z0-9-]+\s*:/i', $style_sources)) {
			$issues[] = array(
				'code' => 'sbm_namespace_override',
				'severity' => 'error',
				'message' => __('Lo slot dichiara token --sbin-* riservati a Smart Bootstrap Manager.', AIHL_TEXT_DOMAIN),
			);
		}

		$css_without_tokens = preg_replace('/var\([^;{}]+\)/i', 'var(--governed-token)', $style_sources);
		$css_without_tokens = preg_replace('/url\([^)]*\)/i', 'url(asset)', (string) $css_without_tokens);
		$has_raw_color = (bool) preg_match(
			'/(?:^|[;{])\s*(?:color|background(?:-color)?|border(?:-[a-z-]+)?-color|fill|stroke)\s*:\s*(?:#[0-9a-f]{3,8}\b|rgba?\(|hsla?\()/im',
			(string) $css_without_tokens
		);
		$has_raw_font = (bool) preg_match('/font-family\s*:\s*(?!var\()/i', (string) $css_without_tokens);
		$has_raw_radius = (bool) preg_match('/border-radius\s*:\s*(?!var\(|0\b|50%)[^;}{]+/i', (string) $css_without_tokens);
		$has_raw_spacing = (bool) preg_match(
			'/(?:padding|margin|gap|row-gap|column-gap)\s*:\s*(?!var\(|calc\([^;]*var\()[^;}{]*(?:px|rem|em)\b/i',
			(string) $css_without_tokens
		);
		$has_raw_type_scale = (bool) preg_match(
			'/(?:font-size|line-height|letter-spacing)\s*:\s*(?!var\(|calc\([^;]*var\()[^;}{]+/i',
			(string) $css_without_tokens
		);
		$uses_semantic_tokens = (bool) preg_match('/var\(--(?:bs|sbin|canvas)-/i', $style_sources);

		if ('governed' === $mode && ($has_raw_color || $has_raw_font || $has_raw_radius || $has_raw_spacing || $has_raw_type_scale)) {
			$issues[] = array(
				'code' => 'governed_raw_visual_value',
				'severity' => 'error',
				'message' => __('Il CSS governed contiene colori, tipografia, spaziature o radius non derivati dai token SBM.', AIHL_TEXT_DOMAIN),
			);
		} elseif ('adaptive' === $mode && ($has_raw_color || $has_raw_font || $has_raw_radius || $has_raw_spacing || $has_raw_type_scale)) {
			$issues[] = array(
				'code' => 'adaptive_raw_visual_value',
				'severity' => 'warning',
				'message' => __('Il CSS adaptive contiene valori visuali non derivati da token semantici.', AIHL_TEXT_DOMAIN),
			);
		}

		if ('governed' === $mode && trim($style_sources) !== '' && !$uses_semantic_tokens) {
			$issues[] = array(
				'code' => 'governed_tokens_missing',
				'severity' => 'error',
				'message' => __('Il CSS governed deve consumare token --bs-*, --sbin-* o --canvas-*.', AIHL_TEXT_DOMAIN),
			);
		}

		if (preg_match('/\b(?:new\s+WOW\s*\(|gsap\s*\.|ScrollTrigger\b|owlCarousel\s*\()/i', $js . "\n" . $code)) {
			$issues[] = array(
				'code' => 'motion_runtime_override',
				'severity' => 'error',
				'message' => __('Motion e carousel runtime devono essere richiesti tramite SBM o componenti Bootstrap.', AIHL_TEXT_DOMAIN),
			);
		}

		$valid = !(bool) array_filter($issues, static function(array $issue): bool {
			return 'error' === ($issue['severity'] ?? '');
		});

		return array(
			'valid' => $valid,
			'mode' => $mode,
			'requested_mode' => (string) $constraint['requested'],
			'global_mode' => (string) $constraint['global'],
			'declared' => $declared,
			'issues' => array_values($issues),
		);
	}
}

if (!function_exists('aihl_code_slot_api_payload')) {
	function aihl_code_slot_api_payload(array $slot): array {
		$slot['governance'] = aihl_code_slot_governance_report($slot);
		return $slot;
	}
}

if (!function_exists('aihl_canvas_health_report')) {
	/**
	 * Restituisce una diagnosi stabile e serializzabile della sorgente Canvas.
	 */
	function aihl_canvas_health_report(string $area): array {
		$area = in_array($area, array('header', 'footer'), true) ? $area : 'header';
		$hook = $area . '_full';
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		$options = is_array($options) ? $options : array();
		$mode = sanitize_key((string) ($options[$area . '_render_mode'] ?? 'native'));
		$mode = in_array($mode, array('native', 'canvas'), true) ? $mode : 'native';
		$selected_id = sanitize_key((string) ($options[$area . '_canvas_slot_id'] ?? ''));
		$selected = $selected_id !== '' ? aihl_code_slots_get($selected_id) : null;
		$editor_slot = aihl_code_slots_get_admin_canvas_slot($area);
		$resolved = function_exists('aihl_get_canvas_override_slot') ? aihl_get_canvas_override_slot($area) : null;
		$issues = array();
		$slots_total = 0;
		$slots_active = 0;

		foreach (aihl_code_slots_get_all() as $slot) {
			if (($slot['hook'] ?? '') !== $hook) {
				continue;
			}
			$slots_total++;
			if (!empty($slot['active'])) {
				$slots_active++;
			}
		}

		if ($selected_id !== '') {
			if (!is_array($selected)) {
				$issues[] = array('code' => 'selected_slot_missing', 'severity' => 'error', 'message' => __('Lo slot Canvas selezionato non esiste.', AIHL_TEXT_DOMAIN));
			} elseif (($selected['hook'] ?? '') !== $hook) {
				$issues[] = array('code' => 'selected_slot_wrong_area', 'severity' => 'error', 'message' => __('Lo slot selezionato appartiene a un\'altra area.', AIHL_TEXT_DOMAIN));
			} elseif (empty($selected['active'])) {
				$issues[] = array('code' => 'selected_slot_inactive', 'severity' => 'error', 'message' => __('Lo slot Canvas selezionato non e attivo.', AIHL_TEXT_DOMAIN));
			}
		}

		if ('canvas' === $mode && !is_array($resolved)) {
			$issues[] = array(
				'code' => 'canvas_fallback_native',
				'severity' => 'error',
				'message' => __('Nessuno slot Canvas valido nel contesto corrente: verra usata la struttura nativa.', AIHL_TEXT_DOMAIN),
			);
		}

		$diagnostic_slot = is_array($selected) && ($selected['hook'] ?? '') === $hook ? $selected : $editor_slot;
		if (is_array($diagnostic_slot)) {
			$governance_report = aihl_code_slot_governance_report($diagnostic_slot);
			$issues = array_merge($issues, $governance_report['issues']);
			$markup = trim((string) ($diagnostic_slot['code'] ?? ''));
			if ($markup === '') {
				$issues[] = array('code' => 'canvas_markup_empty', 'severity' => 'error', 'message' => __('Lo slot Canvas non contiene markup HTML.', AIHL_TEXT_DOMAIN));
			} elseif (false === stripos($markup, '<smart-menu')) {
				$issues[] = array(
					'code' => 'navigation_component_missing',
					'severity' => 'warning',
					'message' => __('Il Canvas non dichiara un componente smart-menu; verificare la navigazione.', AIHL_TEXT_DOMAIN),
				);
			} elseif (function_exists('aihl_resolve_nav_menu')) {
				$location = 'header' === $area ? 'topic' : 'footer';
				if (preg_match('/<smart-menu\b([^>]*)>/i', $markup, $menu_match)
					&& preg_match('/\blocation\s*=\s*(["\'])(.*?)\1/i', $menu_match[1], $location_match)) {
					$location = sanitize_key((string) $location_match[2]);
				}
				$menu = aihl_resolve_nav_menu($location);
				if (empty($menu['menu_id'])) {
					$issues[] = array(
						'code' => 'navigation_unresolved',
						'severity' => 'error',
						'message' => sprintf(__('Nessun menu risolvibile per la posizione %s.', AIHL_TEXT_DOMAIN), $location),
					);
				}
			}
		}

		$has_error = (bool) array_filter($issues, static function (array $issue): bool {
			return 'error' === ($issue['severity'] ?? '');
		});
		$has_warning = (bool) array_filter($issues, static function (array $issue): bool {
			return 'warning' === ($issue['severity'] ?? '');
		});
		$status = 'native' === $mode ? 'inactive' : ($has_error ? 'error' : ($has_warning ? 'warning' : 'ok'));
		$editor_id = is_array($editor_slot) ? sanitize_key((string) ($editor_slot['id'] ?? '')) : '';
		$editor_query = $editor_id !== ''
			? 'admin.php?page=aihl-code-slots&edit=' . rawurlencode($editor_id)
			: 'admin.php?page=aihl-code-slots&new=1&canvas=' . $area;

		return array(
			'area' => $area,
			'mode' => $mode,
			'status' => $status,
			'selected_slot_id' => $selected_id,
			'resolved_slot_id' => is_array($resolved) ? (string) ($resolved['id'] ?? '') : '',
			'editor_slot_id' => $editor_id,
			'slots_total' => $slots_total,
			'slots_active' => $slots_active,
			'fallback_native' => 'canvas' === $mode && !is_array($resolved),
			'design_mode' => isset($governance_report) ? $governance_report['mode'] : '',
			'requested_design_mode' => isset($governance_report) ? $governance_report['requested_mode'] : '',
			'global_design_mode' => isset($governance_report) ? $governance_report['global_mode'] : (function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous'),
			'design_mode_declared' => isset($governance_report) ? $governance_report['declared'] : false,
			'issues' => array_values($issues),
			'editor_url' => function_exists('admin_url') ? admin_url($editor_query) : $editor_query,
		);
	}
}

if (!function_exists('aihl_get_canvas_override_slot')) {
	/**
	 * Restituisce l'unico slot Canvas vincitore per l'area corrente.
	 * Una selezione esplicita ha precedenza; altrimenti vince priorita + ID.
	 */
	function aihl_get_canvas_override_slot(string $area): ?array {
		$area = in_array($area, array('header', 'footer'), true) ? $area : 'header';
		$hook = $area . '_full';
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		$selected_id = is_array($options) ? sanitize_key((string) ($options[$area . '_canvas_slot_id'] ?? '')) : '';
		$matches = array();

		foreach (aihl_code_slots_get_all() as $id => $slot) {
			if (($slot['hook'] ?? '') !== $hook || empty($slot['active'])) {
				continue;
			}
			if (!aihl_code_slot_context_matches($slot['context'] ?? 'global')) {
				continue;
			}
			$governance_report = aihl_code_slot_governance_report($slot);
			if (!$governance_report['valid']) {
				continue;
			}
			$slot['id'] = (string) ($slot['id'] ?? $id);
			$matches[] = $slot;
		}
		if (!$matches) {
			return null;
		}

		if ($selected_id !== '') {
			foreach ($matches as $slot) {
				if ($slot['id'] === $selected_id) {
					return $slot;
				}
			}
			return null;
		}

		usort($matches, static function (array $a, array $b): int {
			$priority = ((int) ($a['priority'] ?? 10)) <=> ((int) ($b['priority'] ?? 10));
			return $priority !== 0 ? $priority : strcmp((string) $a['id'], (string) $b['id']);
		});
		return $matches[0];
	}
}

if (!function_exists('aihl_code_slot_has_override')) {
	/**
	 * Controlla se esiste almeno uno slot attivo per un hook override
	 * e che il contesto corrente corrisponda.
	 *
	 * Usato nei template per decidere se saltare il rendering nativo.
	 *
	 * @param string $hook Nome dell'hook (es. 'header_full', 'footer_full').
	 * @return bool True se l'override è attivo e va renderizzato.
	 */
	function aihl_code_slot_has_override(string $hook) {
		$area = 'header_full' === $hook ? 'header' : ('footer_full' === $hook ? 'footer' : '');
		return $area !== '' && null !== aihl_get_canvas_override_slot($area);
	}
}

/* ============================================================================
 * 4. Renderer — output degli slot nei template
 * ============================================================================ */

if (!function_exists('aihl_get_structure_render_mode')) {
	function aihl_get_structure_render_mode(string $area): string {
		$area = in_array($area, array('header', 'footer'), true) ? $area : 'header';
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		$mode = is_array($options) ? sanitize_key((string) ($options[$area . '_render_mode'] ?? '')) : '';
		return in_array($mode, array('native', 'canvas'), true) ? $mode : 'native';
	}
}

if (!function_exists('aihl_should_render_canvas_structure')) {
	function aihl_should_render_canvas_structure(string $area): bool {
		$area = in_array($area, array('header', 'footer'), true) ? $area : 'header';
		return 'canvas' === aihl_get_structure_render_mode($area)
			&& aihl_code_slot_has_override($area . '_full');
	}
}

if (!function_exists('aihl_migrate_structure_render_modes')) {
	function aihl_migrate_structure_render_modes(): void {
		$options = get_option(AIHL_OPTION_BASE . '_general', array());
		$options = is_array($options) ? $options : array();
		$slots = aihl_code_slots_get_all();
		$changed = false;

		foreach (array('header', 'footer') as $area) {
			$key = $area . '_render_mode';
			if (isset($options[$key]) && in_array($options[$key], array('native', 'canvas'), true)) {
				continue;
			}
			$hook = $area . '_full';
			$has_active_slot = false;
			foreach ($slots as $slot) {
				if (($slot['hook'] ?? '') === $hook && !empty($slot['active'])) {
					$has_active_slot = true;
					break;
				}
			}
			$options[$key] = $has_active_slot ? 'canvas' : 'native';
			$changed = true;
		}

		if ($changed) {
			update_option(AIHL_OPTION_BASE . '_general', $options, false);
		}
	}
	add_action('after_setup_theme', 'aihl_migrate_structure_render_modes', 20);
}

if (!function_exists('aihl_render_code_slot')) {
	/**
	 * Renderizza tutti gli slot attivi per un dato hook point.
	 *
	 * @param string $hook Il nome dell'hook point.
	 */
	function aihl_render_code_slot(string $hook) {
		$slots = aihl_code_slots_get_all();
		if (empty($slots)) {
			return;
		}

		// Gli override completi devono produrre una sola struttura DOM.
		if (in_array($hook, array('header_full', 'footer_full'), true)) {
			$winner = aihl_get_canvas_override_slot('header_full' === $hook ? 'header' : 'footer');
			$slots = $winner ? array($winner['id'] => $winner) : array();
		}

		// Filtra slot per questo hook, attivi e con context match.
		$active = array();
		foreach ($slots as $slot) {
			if ($slot['hook'] !== $hook || empty($slot['active'])) {
				continue;
			}
			if (!aihl_code_slot_context_matches($slot['context'] ?? 'global')) {
				continue;
			}
			$is_canvas_override = aihl_code_slot_is_canvas_override($slot);
			$governance_report = aihl_code_slot_governance_report($slot);
			if ($is_canvas_override && !$governance_report['valid']) {
				continue;
			}
			$active[] = $slot;
		}

		if (empty($active)) {
			return;
		}

		// Ordina per priorità
		usort($active, function ($a, $b) {
			$priority = ((int) ($a['priority'] ?? 10)) <=> ((int) ($b['priority'] ?? 10));
			return $priority !== 0 ? $priority : strcmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
		});

		foreach ($active as $slot) {
			$type = $slot['type'] ?? 'html';
			$is_canvas_override = in_array($hook, array('header_full', 'footer_full'), true);
			if ($is_canvas_override) {
				$governance_report = aihl_code_slot_governance_report($slot);
				$governance = function_exists('aihl_sbm_design_governance') ? aihl_sbm_design_governance() : array();
				$inherit_attribute = static function(string $domain) use ($governance): string {
					$key = 'smart_bootstrap_option_design_inherit_' . $domain;
					return !empty($governance[$key]) ? 'on' : 'off';
				};
				echo '<div class="sbs-ai-canvas aihl-ai-canvas aihl-ai-canvas-' . esc_attr('header_full' === $hook ? 'header' : 'footer') . '"';
				echo ' data-sbs-design-mode="' . esc_attr($governance_report['mode']) . '"';
				echo ' data-sbs-inherit-colors="' . esc_attr($inherit_attribute('colors')) . '"';
				echo ' data-sbs-inherit-typography="' . esc_attr($inherit_attribute('typography')) . '"';
				echo ' data-sbs-inherit-spacing="' . esc_attr($inherit_attribute('spacing')) . '"';
				echo ' data-sbs-inherit-radius="' . esc_attr($inherit_attribute('radius')) . '"';
				echo ' data-sbs-inherit-components="' . esc_attr($inherit_attribute('components')) . '"';
				echo '>';
			}

			switch ($type) {
				case 'css':
					echo '<style data-aihl-slot="' . esc_attr($slot['id']) . '">' . "\n";
					echo $slot['code'] . "\n"; // phpcs:ignore -- CSS output, sanitized on save
					echo '</style>' . "\n";
					break;

				case 'js':
					echo '<script data-aihl-slot="' . esc_attr($slot['id']) . '">' . "\n";
					echo $slot['code'] . "\n"; // phpcs:ignore -- JS output, admin-only save
					echo '</script>' . "\n";
					break;

				case 'mixed':
					// CSS
					if (!empty($slot['css'])) {
						echo '<style data-aihl-slot="' . esc_attr($slot['id']) . '-css">' . "\n";
						echo $slot['css'] . "\n";
						echo '</style>' . "\n";
					}
					// HTML
					if (!empty($slot['code'])) {
						echo '<!-- aihl-slot: ' . esc_attr($slot['id']) . ' -->' . "\n";
						echo function_exists('aihl_expand_dynamic_components') ? aihl_expand_dynamic_components($slot['code']) : $slot['code'];
						echo "\n";
						echo '<!-- /aihl-slot -->' . "\n";
					}
					// JS
					if (!empty($slot['js'])) {
						echo '<script data-aihl-slot="' . esc_attr($slot['id']) . '-js">' . "\n";
						echo $slot['js'] . "\n";
						echo '</script>' . "\n";
					}
					break;

				default: // html
					echo '<!-- aihl-slot: ' . esc_attr($slot['id']) . ' -->' . "\n";
					echo function_exists('aihl_expand_dynamic_components') ? aihl_expand_dynamic_components($slot['code']) : $slot['code'];
					echo "\n";
					echo '<!-- /aihl-slot -->' . "\n";
					break;
			}
			if ($is_canvas_override) {
				echo '</div>';
			}
		}
	}
}

/* ============================================================================
 * 5. WordPress Hooks — aggancia i global_css e global_js a wp_head/wp_footer
 * ============================================================================ */

add_action('wp_head', function () {
	aihl_render_code_slot('global_css');
}, 99);

add_action('wp_body_open', function () {
	aihl_render_code_slot('body_start');
}, 1);

add_action('wp_footer', function () {
	aihl_render_code_slot('global_js');
	aihl_render_code_slot('body_end');
}, 99);

// Hook specifici del tema — aggancio a do_action esistenti
add_action('aihl/header/topbar/right', function () {
	aihl_render_code_slot('topbar_end');
});

/* ============================================================================
 * 6. REST API — CRUD + introspection completa
 * ============================================================================ */

add_action('rest_api_init', function () {
	$ns = 'aihtml/v1';

	$can_read = function (WP_REST_Request $request) {
		if (function_exists('smart_ai_can_read')) {
			return smart_ai_can_read($request);
		}
		return current_user_can('edit_theme_options');
	};

	$can_write = function (WP_REST_Request $request) {
		if (function_exists('smart_ai_can_write')) {
			return smart_ai_can_write($request);
		}
		return current_user_can('manage_options');
	};

	// ── GET /ai/code-slots — lista tutti gli slot ──
	register_rest_route($ns, '/ai/code-slots', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => $can_read,
			'callback'            => function () {
				$slots = aihl_code_slots_get_all();
				$payload = array_map('aihl_code_slot_api_payload', array_values($slots));
				return rest_ensure_response(array(
					'count' => count($slots),
					'slots' => $payload,
				));
			},
		),
		// ── POST /ai/code-slots — crea/aggiorna slot ──
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => $can_write,
			'callback'            => function (WP_REST_Request $request) {
				$body = $request->get_json_params();
				if (!is_array($body)) {
					return new WP_Error('invalid_json', 'JSON non valido.', array('status' => 400));
				}
				$result = aihl_code_slots_save($body);
				if (is_wp_error($result)) {
					return $result;
				}
				return rest_ensure_response(aihl_code_slot_api_payload($result));
			},
		),
	));

	// ── GET/PUT/DELETE /ai/code-slots/{id} ──
	register_rest_route($ns, '/ai/code-slots/(?P<slot_id>[a-z0-9_-]+)', array(
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => $can_read,
			'callback'            => function (WP_REST_Request $request) {
				$slot = aihl_code_slots_get($request['slot_id']);
				if (!$slot) {
					return new WP_Error('not_found', 'Slot non trovato.', array('status' => 404));
				}
				return rest_ensure_response(aihl_code_slot_api_payload($slot));
			},
		),
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'permission_callback' => $can_write,
			'callback'            => function (WP_REST_Request $request) {
				$body = $request->get_json_params();
				if (!is_array($body)) {
					return new WP_Error('invalid_json', 'JSON non valido.', array('status' => 400));
				}
				$body['id'] = $request['slot_id'];
				$result = aihl_code_slots_save($body);
				if (is_wp_error($result)) {
					return $result;
				}
				return rest_ensure_response(aihl_code_slot_api_payload($result));
			},
		),
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'permission_callback' => $can_write,
			'callback'            => function (WP_REST_Request $request) {
				$result = aihl_code_slots_delete($request['slot_id']);
				if (is_wp_error($result)) {
					return $result;
				}
				return rest_ensure_response(array('deleted' => true, 'slot' => $result));
			},
		),
	));

	// ── POST /ai/code-slots/{id}/toggle ──
	register_rest_route($ns, '/ai/code-slots/(?P<slot_id>[a-z0-9_-]+)/toggle', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => $can_write,
		'callback'            => function (WP_REST_Request $request) {
			$body = $request->get_json_params();
			$active = isset($body['active']) ? (bool) $body['active'] : true;
			$result = aihl_code_slots_toggle($request['slot_id'], $active);
			if (is_wp_error($result)) {
				return $result;
			}
			return rest_ensure_response($result);
		},
	));

	// ── POST /ai/code-slots/{id}/rollback ──
	register_rest_route($ns, '/ai/code-slots/(?P<slot_id>[a-z0-9_-]+)/rollback', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => $can_write,
		'callback'            => function (WP_REST_Request $request) {
			$result = aihl_code_slots_rollback($request['slot_id']);
			if (is_wp_error($result)) {
				return $result;
			}
			return rest_ensure_response($result);
		},
	));

	// ── POST /ai/code-slots/import — import bulk ──
	register_rest_route($ns, '/ai/code-slots/import', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => $can_write,
		'callback'            => function (WP_REST_Request $request) {
			$body = $request->get_json_params();
			if (empty($body['slots']) || !is_array($body['slots'])) {
				return new WP_Error('invalid_format', 'Atteso {"slots":[...]}', array('status' => 400));
			}
			$results = array();
			foreach ($body['slots'] as $slot_data) {
				$r = aihl_code_slots_save($slot_data);
				$results[] = is_wp_error($r)
					? array('id' => $slot_data['id'] ?? '?', 'error' => $r->get_error_message())
					: array('id' => $r['id'], 'status' => 'saved');
			}
			return rest_ensure_response(array('count' => count($results), 'results' => $results));
		},
	));

	// ── GET /ai/code-slots/export ──
	register_rest_route($ns, '/ai/code-slots/export', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => $can_read,
		'callback'            => function () {
			$slots = aihl_code_slots_get_all();
			$export = array();
			foreach ($slots as $slot) {
				unset($slot['previous_code'], $slot['previous_revision']); // Non esportare storico
				$export[] = $slot;
			}
			return rest_ensure_response(array(
				'format'  => 'aihl-code-slots',
				'version' => 1,
				'count'   => count($export),
				'slots'   => $export,
			));
		},
	));

	// ── GET /ai/code-slots/hooks — lista hook disponibili ──
	register_rest_route($ns, '/ai/code-slots/hooks', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => $can_read,
		'callback'            => function () {
			return rest_ensure_response(aihl_code_slots_hooks());
		},
	));

	// ── GET /ai/introspection — visione completa del tema per l'AI ──
	register_rest_route($ns, '/ai/introspection', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => $can_read,
		'callback'            => 'aihl_rest_introspection',
	));

	// ── GET /ai/capabilities — onboarding endpoint per agenti AI ──
	register_rest_route($ns, '/ai/capabilities', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => $can_read,
		'callback'            => 'aihl_rest_capabilities',
	));
});

/* ============================================================================
 * 7. Introspection endpoint — l'AI legge tutto lo stato del tema
 * ============================================================================ */

if (!function_exists('aihl_rest_introspection')) {
	function aihl_rest_introspection() {
		$options = get_option(AIHL_OPTION_BASE . '_general', array());

		// Schema opzioni
		$schema = array();
		if (function_exists('aihl_ai_options_whitelist')) {
			$schema = aihl_ai_options_whitelist();
		}

		// Menu
		$locations = get_registered_nav_menus();
		$assigned = get_nav_menu_locations();
		$menus_info = array();
		foreach ($assigned as $loc => $menu_id) {
			if ($menu_id) {
				$menu_obj = wp_get_nav_menu_object($menu_id);
				$menus_info[$loc] = array(
					'id'    => $menu_id,
					'name'  => $menu_obj ? $menu_obj->name : '',
					'count' => $menu_obj ? $menu_obj->count : 0,
				);
			}
		}

		// Pagine
		$pages = get_pages(array('post_status' => 'publish', 'number' => 50));
		$pages_info = array();
		$front_page_id = (int) get_option('page_on_front');
		$blog_page_id = (int) get_option('page_for_posts');
		foreach ($pages as $page) {
			$pages_info[] = array(
				'id'       => $page->ID,
				'title'    => $page->post_title,
				'slug'     => $page->post_name,
				'template' => get_page_template_slug($page->ID) ?: 'default',
				'is_front' => $page->ID === $front_page_id,
				'is_blog'  => $page->ID === $blog_page_id,
			);
		}

		// Code Slots
		$slots = aihl_code_slots_get_all();
		$active_slots = array_filter($slots, function ($s) {
			return !empty($s['active']);
		});
		$hooks_used = array_unique(array_column($active_slots, 'hook'));

		// Plugin
		$plugins_info = array(
			'sbs_active'   => function_exists('sbs_ai_import_builder_data') || defined('SBS_OPTION_BASE'),
			'sbs_version'  => defined('SBS_VERSION') ? SBS_VERSION : (defined('SBS_PLUGIN_VERSION') ? SBS_PLUGIN_VERSION : null),
			'sbm_active'   => defined('SBIN_OPTION_BASE'),
			'sbm_version'  => defined('SBIN_VERSION') ? SBIN_VERSION : null,
			'sslpp_active' => defined('SSLPP_OPTION_NAME'),
			'sslpp_version'=> defined('SSLPP_VERSION') ? SSLPP_VERSION : null,
		);

		return rest_ensure_response(array(
			'theme' => array(
				'name'    => AIHL_THEME_NAME,
				'version' => AIHL_VERSION,
			),
			'options'        => $options,
			'options_schema' => $schema,
			'menus' => array(
				'registered_locations' => array_keys($locations),
				'assigned_menus'       => $menus_info,
			),
			'pages'       => $pages_info,
			'code_slots'  => array(
				'total'       => count($slots),
				'active'      => count($active_slots),
				'hooks_used'  => array_values($hooks_used),
				'slots'       => array_values($slots),
			),
			'available_hooks'    => aihl_code_slots_hooks(),
			'available_contexts' => array(
				'global', 'front_page', 'home', 'single', 'archive', 'search', '404', 'logged_in',
				'page:{slug}', 'page:{id}', 'post_type:{type}', 'template:{name}', 'category:{slug}', 'tag:{slug}',
				'!{any_context}',
			),
			'plugins'   => $plugins_info,
			'wordpress' => array(
				'version'             => get_bloginfo('version'),
				'show_on_front'       => get_option('show_on_front'),
				'page_on_front'       => $front_page_id,
				'page_for_posts'      => $blog_page_id,
				'permalink_structure' => get_option('permalink_structure'),
				'locale'              => get_locale(),
				'blogname'            => get_bloginfo('name'),
				'blogdescription'     => get_bloginfo('description'),
				'site_url'            => get_site_url(),
				'home_url'            => get_home_url(),
			),
			'reset_registry' => array_keys(function_exists('aihl_get_smart_reset_registry') ? aihl_get_smart_reset_registry() : array()),
			'management_catalog' => function_exists('aihl_api_management_catalog') ? aihl_api_management_catalog() : array(),
		));
	}
}

/* ============================================================================
 * 7b. Capabilities — onboarding per agenti AI
 * ============================================================================ */

if (!function_exists('aihl_rest_capabilities')) {
	function aihl_rest_capabilities() {
		$base_url = rest_url('aihtml/v1/ai');
		$sbs_url = rest_url('sbs/v1/ai');
		$has_sbs = function_exists('sbs_get_widget_registry') || defined('SBS_OPTION_BASE');

		$endpoints = array(
			// Discovery
			array(
				'method'      => 'GET',
				'path'        => '/ai/capabilities',
				'description' => 'Questo endpoint. Lista completa delle capacita e degli endpoint disponibili.',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/introspection',
				'description' => 'Stato completo del tema: opzioni, menu, pagine, code slots, plugin, info WordPress. Chiamalo per primo per capire lo stato attuale del sito.',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/integration-manifest',
				'description' => 'Contratto runtime: loghi, menu, social, contatti, add-on, fallback e componenti dinamici per AI Canvas.',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/addons',
				'description' => 'Elenca gli add-on rilevati e le risorse selezionabili per Add-on Integration.',
			),
			// Options
			array(
				'method'      => 'GET',
				'path'        => '/ai/options',
				'description' => 'Legge tutte le opzioni tema (header, footer, contatti, CTA, ecc.).',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/options',
				'description' => 'Aggiorna opzioni tema. Il body usa l envelope options; consulta /ai/options/schema.',
				'example'     => '{"options":{"header_structure":"dualbar","footer_variant":"futuristic","header_cta_label":"Richiedi demo"}}',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/options/schema',
				'description' => 'Schema delle 60+ opzioni modificabili con tipo e valori accettati.',
			),
			// Context
			array(
				'method'      => 'GET',
				'path'        => '/ai/context',
				'description' => 'Info sito: nome, tagline, URL, tema attivo.',
			),
			// Menus
			array(
				'method'      => 'GET',
				'path'        => '/ai/menus',
				'description' => 'Lista menu WordPress con location e voci.',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/menus',
				'description' => 'Importa menu da JSON. Crea menu e assegna a location.',
				'example'     => '{"menus":{"topic":{"name":"Menu Principale","items":[{"title":"Home","url":"/"}]}}}',
			),
			// Pages
			array(
				'method'      => 'GET',
				'path'        => '/ai/pages',
				'description' => 'Lista pagine pubblicate con id, slug, template.',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/pages',
				'description' => 'Crea una pagina WordPress.',
				'example'     => '{"title":"Chi Siamo","slug":"chi-siamo","content":"<h2>...</h2>","template":"page-template-full.php"}',
			),
			// Code Slots
			array(
				'method'      => 'GET',
				'path'        => '/ai/code-slots',
				'description' => 'Lista tutti i code slot (HTML/CSS/JS iniettati nel tema).',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/code-slots',
				'description' => 'Crea o aggiorna un code slot. Questo e lo strumento piu potente: permette di iniettare qualsiasi codice HTML/CSS/JS in 20 punti del tema.',
				'example'     => '{"label":"Banner promo","hook":"before_header","type":"mixed","context":"front_page","code":"<div class=\"banner\">Offerta!</div>","css":".banner{background:#e91e8c;color:#fff;text-align:center;padding:10px}","active":true}',
			),
			array(
				'method'      => 'PUT',
				'path'        => '/ai/code-slots/{id}',
				'description' => 'Aggiorna uno slot esistente per ID.',
			),
			array(
				'method'      => 'DELETE',
				'path'        => '/ai/code-slots/{id}',
				'description' => 'Elimina uno slot.',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/code-slots/{id}/toggle',
				'description' => 'Attiva/disattiva uno slot senza eliminarlo.',
				'example'     => '{"active":false}',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/code-slots/{id}/rollback',
				'description' => 'Ripristina il codice alla versione precedente.',
			),
			array(
				'method'      => 'POST',
				'path'        => '/ai/code-slots/import',
				'description' => 'Importa piu slot in una volta.',
				'example'     => '{"slots":[{...},{...}]}',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/code-slots/export',
				'description' => 'Esporta tutti gli slot in formato JSON.',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/code-slots/hooks',
				'description' => 'Lista dei 20 hook point disponibili dove iniettare codice.',
			),
			// Deploy
			array(
				'method'      => 'POST',
				'path'        => '/ai/deploy',
				'description' => 'Deploy one-click: invia un project.json completo per configurare tutto in una volta (opzioni, menu, pagine, builder, code slots).',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/deploy/projects',
				'description' => 'Lista progetti demo disponibili nel tema.',
			),
			// Reset
			array(
				'method'      => 'POST',
				'path'        => '/ai/reset/execute',
				'description' => 'Resetta solo componenti AI-HTML. Ogni plugin ha endpoint reset autonomi.',
				'example'     => '{"components":["aihl:options","aihl:menus","aihl:pages","aihl:code-slots"]}',
			),
			array(
				'method'      => 'GET',
				'path'        => '/ai/reset/registry',
				'description' => 'Lista componenti resettabili governati da AI-HTML.',
			),
		);

		$hooks = aihl_code_slots_hooks();
		$override_hooks = array();
		$injection_hooks = array();
		foreach ($hooks as $key => $hook) {
			if (!empty($hook['override'])) {
				$override_hooks[$key] = $hook['label'] . ' — ' . $hook['description'];
			} else {
				$injection_hooks[$key] = $hook['label'] . ' — ' . $hook['description'];
			}
		}

		return rest_ensure_response(array(
			'name'    => 'AI-HTML Theme API',
			'version' => defined('AIHL_VERSION') ? AIHL_VERSION : '1.4.0',
			'auth'    => array(
				'method'  => 'API Key',
				'header'  => 'X-Smart-AI-Key',
				'docs'    => 'Genera una chiave in WordPress > Impostazioni > Smart AI API Keys',
			),
			'base_url'  => $base_url,
			'endpoints' => $endpoints,
			'management_catalog' => function_exists('aihl_api_management_catalog') ? aihl_api_management_catalog() : array(),
			'workflow'   => array(
				'1_discover'    => 'GET /ai/capabilities — stai leggendo questo',
				'2_understand'  => 'GET /ai/introspection + /ai/integration-manifest — leggi stato e risorse runtime',
				'3_configure'   => 'POST /ai/options con envelope options - modifica header, footer, CTA, contatti',
				'4_structure'   => 'POST /ai/menus + POST /ai/pages — crea menu e pagine',
				'5_customize'   => 'POST /ai/code-slots — inietta HTML/CSS/JS in qualsiasi punto',
				'6_override'    => 'POST /ai/code-slots con hook=header_full o footer_full — sostituisci intero header/footer',
				'7_deploy'      => 'POST /ai/deploy — deploy completo da project.json',
				'8_reset'       => 'POST /ai/reset/execute - reset selettivo con snapshot preventivo',
			),
			'code_slots' => array(
				'override_hooks'  => $override_hooks,
				'injection_hooks' => $injection_hooks,
				'slot_types'      => array('html', 'css', 'js', 'mixed'),
				'contexts'        => array(
					'global', 'front_page', 'home', 'single', 'archive', 'search', '404', 'logged_in',
					'page:{slug}', 'page:{id}', 'post_type:{type}', 'template:{name}',
					'category:{slug}', 'tag:{slug}', '!{any_context}',
				),
			),
			'sbs_api'    => $has_sbs ? array(
				'active'   => true,
				'base_url' => $sbs_url,
				'note'     => 'Smart Builder Site ha API separate per widget e builder. Stessa API Key.',
			) : array('active' => false),
		));
	}
}

/* ============================================================================
 * 9. Deploy integration — Step 6 nel sistema deploy
 * ============================================================================ */

add_filter('aihl_deploy_extra_steps', function (array $steps, array $project, array $created_pages) {
	if (empty($project['code_slots']) || !is_array($project['code_slots'])) {
		$steps['code_slots'] = array('skipped' => true, 'reason' => 'Nessun code slot nel progetto.');
		return $steps;
	}
	$saved = 0;
	$errors = array();
	foreach ($project['code_slots'] as $slot_data) {
		$result = aihl_code_slots_save($slot_data);
		if (is_wp_error($result)) {
			$errors[] = $result->get_error_message();
		} else {
			$saved++;
		}
	}
	$steps['code_slots'] = array(
		'saved'  => $saved,
		'errors' => $errors,
		'count'  => $saved,
	);
	return $steps;
}, 10, 3);

/* ============================================================================
 * 10. Admin Page — Code Slots UI
 * ============================================================================ */

if (!function_exists('aihl_render_code_slots_page')) {
	function aihl_render_code_slots_page() {
		$slots = aihl_code_slots_get_all();
		$hooks = aihl_code_slots_hooks();
		$edit_slot = null;
		$save_result = null;
		$submitted_editor_tab = '';

		// Handle POST save
		if (isset($_POST['aihl_code_slot_save']) && check_admin_referer('aihl_code_slots_nonce')) {
			$submitted_editor_tab = sanitize_key(wp_unslash($_POST['slot_editor_tab'] ?? ''));
			if (!in_array($submitted_editor_tab, array('html', 'css', 'js'), true)) {
				$submitted_editor_tab = '';
			}
			$slot_data = array(
				'id'       => sanitize_key(wp_unslash($_POST['slot_id'] ?? '')),
				'label'    => sanitize_text_field(wp_unslash($_POST['slot_label'] ?? '')),
				'hook'     => sanitize_key(wp_unslash($_POST['slot_hook'] ?? '')),
				'type'     => sanitize_key(wp_unslash($_POST['slot_type'] ?? 'html')),
				'context'  => sanitize_text_field(wp_unslash($_POST['slot_context'] ?? 'global')),
				'design_mode' => sanitize_key(wp_unslash($_POST['slot_design_mode'] ?? '')),
				'priority' => (int) ($_POST['slot_priority'] ?? 10),
				'active'   => !empty($_POST['slot_active']),
				'code'     => wp_unslash($_POST['slot_code'] ?? ''),
				'css'      => wp_unslash($_POST['slot_css'] ?? ''),
				'js'       => wp_unslash($_POST['slot_js'] ?? ''),
				'author'   => 'admin',
			);
			if ('css' === $slot_data['type']) {
				$slot_data['code'] = $slot_data['css'];
			}
			if ('js' === $slot_data['type']) {
				$slot_data['code'] = $slot_data['js'];
			}
			$save_result = aihl_code_slots_save($slot_data);
			if (!is_wp_error($save_result)) {
				$slots = aihl_code_slots_get_all(); // Refresh
				$edit_slot = $save_result;
			}
		}

		// Handle POST delete
		if (isset($_POST['aihl_code_slot_delete']) && check_admin_referer('aihl_code_slots_nonce')) {
			$del_id = sanitize_key(wp_unslash($_POST['aihl_code_slot_delete']));
			aihl_code_slots_delete($del_id);
			$slots = aihl_code_slots_get_all();
		}

		// Handle POST toggle
		if (isset($_POST['aihl_code_slot_toggle']) && check_admin_referer('aihl_code_slots_nonce')) {
			$tog_id = sanitize_key(wp_unslash($_POST['aihl_code_slot_toggle']));
			$tog_active = !empty($_POST['aihl_toggle_to']);
			$toggle_result = aihl_code_slots_toggle($tog_id, $tog_active);
			if (is_wp_error($toggle_result)) {
				$save_result = $toggle_result;
			}
			$slots = aihl_code_slots_get_all();
		}

		// Handle POST import JSON
		if (isset($_FILES['aihl_slots_file']) && check_admin_referer('aihl_code_slots_nonce')) {
			$file = $_FILES['aihl_slots_file'];
			if (!empty($file['tmp_name']) && UPLOAD_ERR_OK === $file['error']) {
				$raw = file_get_contents($file['tmp_name']);
				$data = json_decode($raw, true);
				if (is_array($data) && !empty($data['slots'])) {
					$imported = 0;
					foreach ($data['slots'] as $s) {
						$r = aihl_code_slots_save($s);
						if (!is_wp_error($r)) {
							$imported++;
						}
					}
					$save_result = array('imported' => $imported);
					$slots = aihl_code_slots_get_all();
				}
			}
		}

		// Editing mode?
		if (isset($_GET['edit'])) {
			$edit_slot = aihl_code_slots_get(sanitize_key($_GET['edit']));
		}
		$is_new = isset($_GET['new']);
		$canvas_area = isset($_GET['canvas']) ? sanitize_key((string) $_GET['canvas']) : '';
		$canvas_area = in_array($canvas_area, array('header', 'footer'), true) ? $canvas_area : '';

		?>
		<?php if ($save_result && !is_wp_error($save_result)) : ?>
			<div class="notice notice-success"><p>
				<?php if (isset($save_result['imported'])) : ?>
					<strong><?php printf(esc_html__('%d slot importati.', AIHL_TEXT_DOMAIN), $save_result['imported']); ?></strong>
				<?php else : ?>
					<strong><?php printf(esc_html__('Slot "%s" salvato (v%d).', AIHL_TEXT_DOMAIN), esc_html($save_result['label']), $save_result['version']); ?></strong>
				<?php endif; ?>
			</p></div>
		<?php elseif (is_wp_error($save_result)) : ?>
			<div class="notice notice-error"><p><strong><?php echo esc_html($save_result->get_error_message()); ?></strong></p></div>
		<?php endif; ?>

		<?php if ($edit_slot || $is_new) :
			$s = $edit_slot ?: array(
				'id' => '',
				'label' => $canvas_area ? sprintf('AI Canvas %s', ucfirst($canvas_area)) : '',
				'hook' => $canvas_area ? $canvas_area . '_full' : 'before_header',
				'type' => $canvas_area ? 'mixed' : 'html',
				'context' => 'global',
				'design_mode' => function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous',
				'priority' => 10,
				'active' => true,
				'code' => '',
				'css' => '',
				'js' => '',
			);
			$initial_editor_tab = $submitted_editor_tab;
			if ('' === $initial_editor_tab) {
				$initial_editor_tab = '' !== trim((string) ($s['code'] ?? ''))
					? 'html'
					: ('' !== trim((string) ($s['css'] ?? '')) ? 'css' : ('' !== trim((string) ($s['js'] ?? '')) ? 'js' : 'html'));
			}
		?>
			<!-- Editor singolo slot -->
			<form method="post">
				<?php wp_nonce_field('aihl_code_slots_nonce'); ?>
				<input type="hidden" name="slot_id" value="<?php echo esc_attr($s['id']); ?>">
				<input type="hidden" name="slot_editor_tab" value="<?php echo esc_attr($initial_editor_tab); ?>" data-aihl-editor-tab-input>

				<table class="form-table">
					<tr>
						<th><?php esc_html_e('Label', AIHL_TEXT_DOMAIN); ?></th>
						<td><input type="text" name="slot_label" value="<?php echo esc_attr($s['label']); ?>" class="regular-text" required></td>
					</tr>
					<tr>
						<th><?php esc_html_e('Hook Point', AIHL_TEXT_DOMAIN); ?></th>
						<td>
							<select name="slot_hook">
								<?php foreach ($hooks as $hk => $hinfo) : ?>
									<option value="<?php echo esc_attr($hk); ?>" <?php selected($s['hook'], $hk); ?>>
										<?php echo esc_html($hinfo['label']); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description" id="aihl-hook-desc"></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e('Tipo', AIHL_TEXT_DOMAIN); ?></th>
						<td>
							<label><input type="radio" name="slot_type" value="html" <?php checked($s['type'], 'html'); ?>> HTML</label>&nbsp;
							<label><input type="radio" name="slot_type" value="css" <?php checked($s['type'], 'css'); ?>> CSS</label>&nbsp;
							<label><input type="radio" name="slot_type" value="js" <?php checked($s['type'], 'js'); ?>> JS</label>&nbsp;
							<label><input type="radio" name="slot_type" value="mixed" <?php checked($s['type'], 'mixed'); ?>> Mixed</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e('Contesto', AIHL_TEXT_DOMAIN); ?></th>
						<td>
							<input type="text" name="slot_context" value="<?php echo esc_attr(is_array($s['context']) ? implode(', ', $s['context']) : $s['context']); ?>" class="regular-text">
							<p class="description"><?php esc_html_e('Valori: global, front_page, page:{slug}, template:{name}, category:{slug}, logged_in, !logged_in, 404, search. Separa con virgola per combinare.', AIHL_TEXT_DOMAIN); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e('Governance design', AIHL_TEXT_DOMAIN); ?></th>
						<td>
							<select name="slot_design_mode">
								<?php
								$global_design_mode = function_exists('aihl_sbm_design_mode') ? aihl_sbm_design_mode() : 'autonomous';
								foreach (array('governed' => 'Governed', 'adaptive' => 'Adaptive', 'autonomous' => 'Autonomous') as $mode_key => $mode_label) :
									if (function_exists('aihl_sbm_design_mode_rank') && aihl_sbm_design_mode_rank($mode_key) > aihl_sbm_design_mode_rank($global_design_mode)) {
										continue;
									}
								?>
									<option value="<?php echo esc_attr($mode_key); ?>" <?php selected($s['design_mode'] ?? $global_design_mode, $mode_key); ?>><?php echo esc_html($mode_label); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php echo esc_html(sprintf(__('La modalita dello slot non puo essere piu permissiva della governance SBM globale (%s).', AIHL_TEXT_DOMAIN), $global_design_mode)); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e('Priorita', AIHL_TEXT_DOMAIN); ?></th>
						<td><input type="number" name="slot_priority" value="<?php echo (int) $s['priority']; ?>" min="1" max="999" style="width:80px"></td>
					</tr>
					<tr>
						<th><?php esc_html_e('Attivo', AIHL_TEXT_DOMAIN); ?></th>
						<td><label><input type="checkbox" name="slot_active" value="1" <?php checked($s['active']); ?>> <?php esc_html_e('Abilita questo slot', AIHL_TEXT_DOMAIN); ?></label></td>
					</tr>
				</table>

				<div class="aihl-code-editor-shell" data-aihl-code-editor>
					<div class="aihl-code-editor-head">
						<div>
							<strong><?php esc_html_e('Editor AI Canvas', AIHL_TEXT_DOMAIN); ?></strong>
							<span><?php esc_html_e('Gestione separata di markup, stile e script dello slot.', AIHL_TEXT_DOMAIN); ?></span>
						</div>
						<div class="aihl-code-editor-actions">
							<button type="button" class="button button-small" data-aihl-copy-active title="<?php esc_attr_e('Copia sezione attiva', AIHL_TEXT_DOMAIN); ?>"><i class="fa-regular fa-copy"></i></button>
							<button type="button" class="button button-small" data-aihl-paste-active title="<?php esc_attr_e('Incolla dagli appunti', AIHL_TEXT_DOMAIN); ?>"><i class="fa-regular fa-clipboard"></i></button>
						</div>
					</div>
					<div class="aihl-code-editor-tabs" role="tablist" aria-label="<?php esc_attr_e('Sezioni codice slot', AIHL_TEXT_DOMAIN); ?>">
						<button type="button" class="button button-small" data-aihl-editor-tab="html">HTML</button>
						<button type="button" class="button button-small" data-aihl-editor-tab="css">CSS</button>
						<button type="button" class="button button-small" data-aihl-editor-tab="js">JS</button>
					</div>
					<div class="aihl-code-editor-pane" data-aihl-editor-pane="html">
						<textarea id="aihl-slot-code" name="slot_code" rows="18" class="large-text code aihl-code-textarea" spellcheck="false"><?php echo esc_textarea($s['code']); ?></textarea>
					</div>
					<div class="aihl-code-editor-pane" data-aihl-editor-pane="css">
						<textarea id="aihl-slot-css" name="slot_css" rows="18" class="large-text code aihl-code-textarea" spellcheck="false"><?php echo esc_textarea($s['css'] ?? ''); ?></textarea>
					</div>
					<div class="aihl-code-editor-pane" data-aihl-editor-pane="js">
						<textarea id="aihl-slot-js" name="slot_js" rows="18" class="large-text code aihl-code-textarea" spellcheck="false"><?php echo esc_textarea($s['js'] ?? ''); ?></textarea>
					</div>
				</div>

				<p>
					<button type="submit" name="aihl_code_slot_save" value="1" class="button button-primary">
						<i class="fa-solid fa-floppy-disk"></i> <?php esc_html_e('Salva', AIHL_TEXT_DOMAIN); ?>
					</button>
					<a href="<?php echo esc_url(admin_url('admin.php?page=aihl-code-slots')); ?>" class="button"><?php esc_html_e('Annulla', AIHL_TEXT_DOMAIN); ?></a>
				</p>
			</form>

			<script>
			(function(){
				var hooks=<?php echo wp_json_encode(array_map(function($h){return $h['description'];}, $hooks)); ?>;
				var sel=document.querySelector('[name=slot_hook]');
				var desc=document.getElementById('aihl-hook-desc');
				function upd(){desc.textContent=hooks[sel.value]||'';}
				sel.addEventListener('change',upd);upd();

				// Toggle editor sections according to the selected slot type.
				var radios=document.querySelectorAll('[name=slot_type]');
				function togFields(){
					var t=document.querySelector('[name=slot_type]:checked').value;
					var allowed=t==='mixed'?['html','css','js']:(t==='css'?['css']:(t==='js'?['js']:['html']));
					document.querySelectorAll('[data-aihl-editor-tab]').forEach(function(tab){
						var show=allowed.indexOf(tab.getAttribute('data-aihl-editor-tab'))!==-1;
						tab.style.display=show?'':'none';
						if(!show){tab.classList.remove('is-active');}
					});
					document.querySelectorAll('[data-aihl-editor-pane]').forEach(function(pane){
						var show=allowed.indexOf(pane.getAttribute('data-aihl-editor-pane'))!==-1;
						pane.style.display=show?'':'none';
						if(!show){pane.classList.remove('is-active');}
					});
					if(!document.querySelector('[data-aihl-editor-tab].is-active')){
						var first=null;
						document.querySelectorAll('[data-aihl-editor-tab]').forEach(function(tab){if(!first&&tab.style.display!=='none'){first=tab;}});
						if(first){activateTab(first.getAttribute('data-aihl-editor-tab'));}
					}
				}
				function activateTab(name){
					document.querySelectorAll('[data-aihl-editor-tab]').forEach(function(tab){tab.classList.toggle('is-active',tab.getAttribute('data-aihl-editor-tab')===name);});
					document.querySelectorAll('[data-aihl-editor-pane]').forEach(function(pane){pane.classList.toggle('is-active',pane.getAttribute('data-aihl-editor-pane')===name);});
					var input=document.querySelector('[data-aihl-editor-tab-input]');
					if(input){input.value=name;}
					if(window.aihlSlotEditors&&window.aihlSlotEditors[name]){setTimeout(function(){window.aihlSlotEditors[name].refresh();window.aihlSlotEditors[name].focus();},30);}
				}
				document.querySelectorAll('[data-aihl-editor-tab]').forEach(function(tab){
					tab.addEventListener('click',function(){activateTab(tab.getAttribute('data-aihl-editor-tab'));});
				});
				function activeSection(){
					var pane=document.querySelector('[data-aihl-editor-pane].is-active');
					if(!pane){return null;}
					var name=pane.getAttribute('data-aihl-editor-pane');
					return {name:name,textarea:pane.querySelector('textarea'),editor:window.aihlSlotEditors&&window.aihlSlotEditors[name]?window.aihlSlotEditors[name]:null};
				}
				var copy=document.querySelector('[data-aihl-copy-active]');
				var paste=document.querySelector('[data-aihl-paste-active]');
				if(copy&&navigator.clipboard){copy.addEventListener('click',function(){var section=activeSection();if(section){navigator.clipboard.writeText(section.editor?section.editor.getValue():section.textarea.value);}});}
				if(paste&&navigator.clipboard){paste.addEventListener('click',function(){var section=activeSection();if(section){navigator.clipboard.readText().then(function(text){if(section.editor){section.editor.setValue(text);section.editor.focus();}else{section.textarea.value=text;section.textarea.dispatchEvent(new Event('change',{bubbles:true}));}});}});}
				if(window.wp&&wp.codeEditor&&window.aihlCodeEditorSettings){
					window.aihlSlotEditors=window.aihlSlotEditors||{};
					[['html','aihl-slot-code'],['css','aihl-slot-css'],['js','aihl-slot-js']].forEach(function(item){
						var textarea=document.getElementById(item[1]);
						var settings=window.aihlCodeEditorSettings[item[0]];
						if(textarea&&settings){
							window.aihlSlotEditors[item[0]]=wp.codeEditor.initialize(textarea,settings).codemirror;
						}
					});
					var form=document.querySelector('[data-aihl-code-editor]').closest('form');
					if(form){form.addEventListener('submit',function(){Object.keys(window.aihlSlotEditors).forEach(function(key){window.aihlSlotEditors[key].save();});});}
				}
				radios.forEach(function(r){r.addEventListener('change',togFields);});
				activateTab(<?php echo wp_json_encode($initial_editor_tab); ?>);
				togFields();
			})();
			</script>

		<?php else : ?>
			<!-- Lista slot -->
			<div class="aihl-slots-overview"><div><strong><?php echo esc_html((string) count($slots)); ?></strong><span><?php esc_html_e('Slot configurati', AIHL_TEXT_DOMAIN); ?></span></div><div><strong><?php echo esc_html((string) count(array_filter($slots, function($slot){ return !empty($slot['active']); }))); ?></strong><span><?php esc_html_e('Slot attivi', AIHL_TEXT_DOMAIN); ?></span></div><div><strong>2</strong><span><?php esc_html_e('Aree Canvas principali', AIHL_TEXT_DOMAIN); ?></span></div></div>
			<div class="aihl-canvas-manager">
				<div class="aihl-canvas-manager-head">
					<h3><?php esc_html_e('AI Canvas Header e Footer', AIHL_TEXT_DOMAIN); ?></h3>
					<p><?php esc_html_e('Accesso diretto agli override completi usati dal tema quando la sorgente struttura e impostata su Canvas.', AIHL_TEXT_DOMAIN); ?></p>
				</div>
				<div class="aihl-canvas-grid">
					<?php foreach (array('header' => __('Header', AIHL_TEXT_DOMAIN), 'footer' => __('Footer', AIHL_TEXT_DOMAIN)) as $area => $label) :
						$canvas_slot = aihl_code_slots_get_admin_canvas_slot($area);
						$edit_url = $canvas_slot
							? admin_url('admin.php?page=aihl-code-slots&edit=' . rawurlencode((string) $canvas_slot['id']))
							: admin_url('admin.php?page=aihl-code-slots&new=1&canvas=' . $area);
						?>
						<div class="aihl-canvas-card">
							<div>
								<strong><?php echo esc_html($label); ?></strong>
								<span><?php echo esc_html($area . '_full'); ?></span>
								<?php if ($canvas_slot) : ?>
									<code><?php echo esc_html($canvas_slot['label'] ?? $canvas_slot['id']); ?></code>
								<?php else : ?>
									<code><?php esc_html_e('Nessuno slot Canvas', AIHL_TEXT_DOMAIN); ?></code>
								<?php endif; ?>
							</div>
							<a href="<?php echo esc_url($edit_url); ?>" class="button button-small" title="<?php esc_attr_e('Apri editor Canvas', AIHL_TEXT_DOMAIN); ?>">
								<i class="fa-solid fa-code"></i> <?php echo $canvas_slot ? esc_html__('Apri editor', AIHL_TEXT_DOMAIN) : esc_html__('Crea Canvas', AIHL_TEXT_DOMAIN); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="aihl-slots-toolbar">
				<a href="<?php echo esc_url(admin_url('admin.php?page=aihl-code-slots&new=1')); ?>" class="button button-primary">
					<i class="fa-solid fa-plus"></i> <?php esc_html_e('Nuovo Slot', AIHL_TEXT_DOMAIN); ?>
				</a>
				<form method="post" enctype="multipart/form-data" style="display:inline-flex;gap:6px;align-items:center;">
					<?php wp_nonce_field('aihl_code_slots_nonce'); ?>
					<input type="file" name="aihl_slots_file" accept=".json" style="font-size:12px;">
					<button type="submit" class="button"><i class="fa-solid fa-upload"></i> <?php esc_html_e('Import', AIHL_TEXT_DOMAIN); ?></button>
				</form>
				<a href="<?php echo esc_url(rest_url('aihtml/v1/ai/code-slots/export')); ?>" class="button" target="_blank">
					<i class="fa-solid fa-download"></i> <?php esc_html_e('Export JSON', AIHL_TEXT_DOMAIN); ?>
				</a>
			</div>

			<?php if (empty($slots)) : ?>
				<div class="aihl-slots-empty">
					<i class="fa-solid fa-code" style="font-size:40px;color:#dcdcde;margin-bottom:12px;"></i>
					<p><?php esc_html_e('Nessun code slot creato. Crea il primo per iniettare HTML/CSS/JS personalizzato nel tema.', AIHL_TEXT_DOMAIN); ?></p>
					<p class="description"><?php esc_html_e('Gli slot possono essere creati anche via API: POST /aihtml/v1/ai/code-slots', AIHL_TEXT_DOMAIN); ?></p>
				</div>
			<?php else : ?>
				<div class="aihl-slots-list">
					<?php foreach ($slots as $slot) :
						$slot_governance = aihl_code_slot_governance_report($slot);
						?>
						<div class="aihl-slot-card <?php echo empty($slot['active']) ? 'aihl-slot-inactive' : ''; ?> <?php echo empty($slot_governance['valid']) ? 'aihl-slot-invalid' : ''; ?>">
							<div class="aihl-slot-card-header">
								<div class="aihl-slot-card-title">
									<span class="aihl-slot-status <?php echo empty($slot['active']) ? 'aihl-slot-status-off' : 'aihl-slot-status-on'; ?>"></span>
									<strong><?php echo esc_html($slot['label']); ?></strong>
									<code class="aihl-slot-id"><?php echo esc_html($slot['id']); ?></code>
								</div>
								<div class="aihl-slot-badges">
									<span class="aihl-slot-badge aihl-sbadge-hook"><?php echo esc_html($slot['hook']); ?></span>
									<span class="aihl-slot-badge aihl-sbadge-type"><?php echo esc_html(strtoupper($slot['type'] ?? 'html')); ?></span>
									<span class="aihl-slot-badge aihl-sbadge-mode"><?php echo esc_html(strtoupper($slot_governance['mode'])); ?></span>
									<span class="aihl-slot-badge aihl-sbadge-ctx"><?php echo esc_html(is_array($slot['context']) ? implode(', ', $slot['context']) : ($slot['context'] ?? 'global')); ?></span>
									<?php if (!empty($slot['version'])) : ?>
										<span class="aihl-slot-badge aihl-sbadge-ver">v<?php echo (int) $slot['version']; ?></span>
									<?php endif; ?>
								</div>
							</div>
							<div class="aihl-slot-card-preview">
								<code><?php echo esc_html(mb_substr($slot['code'] ?? '', 0, 120)); ?><?php echo mb_strlen($slot['code'] ?? '') > 120 ? '...' : ''; ?></code>
							</div>
							<div class="aihl-slot-card-actions">
								<form method="post" style="display:inline;">
									<?php wp_nonce_field('aihl_code_slots_nonce'); ?>
									<a href="<?php echo esc_url(admin_url('admin.php?page=aihl-code-slots&edit=' . $slot['id'])); ?>" class="button button-small">
										<i class="fa-solid fa-pen"></i> <?php esc_html_e('Modifica', AIHL_TEXT_DOMAIN); ?>
									</a>
									<button type="submit" name="aihl_code_slot_toggle" value="<?php echo esc_attr($slot['id']); ?>" class="button button-small">
										<input type="hidden" name="aihl_toggle_to" value="<?php echo empty($slot['active']) ? '1' : ''; ?>">
										<?php echo empty($slot['active']) ? '<i class="fa-solid fa-toggle-on"></i> ' . esc_html__('Attiva', AIHL_TEXT_DOMAIN) : '<i class="fa-solid fa-toggle-off"></i> ' . esc_html__('Disattiva', AIHL_TEXT_DOMAIN); ?>
									</button>
									<button type="submit" name="aihl_code_slot_delete" value="<?php echo esc_attr($slot['id']); ?>" class="button button-small aihl-btn-slot-delete"
										onclick="return confirm('<?php echo esc_js(__('Eliminare questo slot?', AIHL_TEXT_DOMAIN)); ?>');">
										<i class="fa-solid fa-trash"></i>
									</button>
								</form>
								<span class="aihl-slot-meta">
									<?php echo esc_html($slot['author'] ?? ''); ?> · <?php echo esc_html($slot['updated'] ?? ''); ?>
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- API Reference -->
			<details class="aihl-slots-api"><summary><strong><?php esc_html_e('Riferimento API Code Slots', AIHL_TEXT_DOMAIN); ?></strong></summary>
			<table class="form-table aihl-api-ref">
				<tr><th>GET</th><td><code>/aihtml/v1/ai/code-slots</code> — <?php esc_html_e('Lista tutti gli slot', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>POST</th><td><code>/aihtml/v1/ai/code-slots</code> — <?php esc_html_e('Crea/aggiorna slot', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>GET</th><td><code>/aihtml/v1/ai/code-slots/{id}</code> — <?php esc_html_e('Dettaglio slot', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>PUT</th><td><code>/aihtml/v1/ai/code-slots/{id}</code> — <?php esc_html_e('Aggiorna slot', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>DELETE</th><td><code>/aihtml/v1/ai/code-slots/{id}</code> — <?php esc_html_e('Elimina slot', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>POST</th><td><code>/aihtml/v1/ai/code-slots/{id}/toggle</code> — <?php esc_html_e('Attiva/disattiva', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>POST</th><td><code>/aihtml/v1/ai/code-slots/{id}/rollback</code> — <?php esc_html_e('Ripristina versione precedente', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>POST</th><td><code>/aihtml/v1/ai/code-slots/import</code> — <?php esc_html_e('Import bulk', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>GET</th><td><code>/aihtml/v1/ai/code-slots/export</code> — <?php esc_html_e('Export JSON', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>GET</th><td><code>/aihtml/v1/ai/code-slots/hooks</code> — <?php esc_html_e('Hook disponibili', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>GET</th><td><code>/aihtml/v1/ai/introspection</code> — <?php esc_html_e('Stato completo del tema (opzioni, menu, pagine, slot, plugin)', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>GET</th><td><code>/aihtml/v1/ai/integration-manifest</code> — <?php esc_html_e('Risorse runtime per tema, SBS, SBM e motori AI', AIHL_TEXT_DOMAIN); ?></td></tr>
				<tr><th>GET</th><td><code>/aihtml/v1/ai/addons</code> — <?php esc_html_e('Add-on e risorse integrabili', AIHL_TEXT_DOMAIN); ?></td></tr>
			</table>
			</details>
		<?php endif; ?>
		<?php
	}
}

/* ============================================================================
 * 11. CSS Admin per la pagina Code Slots
 * ============================================================================ */

add_action('admin_enqueue_scripts', function ($hook) {
	if (strpos($hook, 'aihl-code-slots') === false) {
		return;
	}
	if (function_exists('wp_enqueue_code_editor')) {
		$settings = array(
			'html' => wp_enqueue_code_editor(array('type' => 'text/html')),
			'css'  => wp_enqueue_code_editor(array('type' => 'text/css')),
			'js'   => wp_enqueue_code_editor(array('type' => 'application/javascript')),
		);
		if (function_exists('wp_add_inline_script')) {
			wp_add_inline_script(
				'code-editor',
				'window.aihlCodeEditorSettings=' . wp_json_encode($settings) . ';',
				'before'
			);
		}
	}
	$css = <<<'CSS'
.aihl-slots-empty{text-align:center;padding:60px 20px;background:#f6f7f7;border:1px dashed #dcdcde;border-radius:8px}
.aihl-slots-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border:1px solid #dcdcde;margin-bottom:18px}.aihl-slots-overview>div{display:flex;flex-direction:column;gap:3px;padding:14px 16px;border-right:1px solid #dcdcde}.aihl-slots-overview>div:last-child{border-right:0}.aihl-slots-overview strong{font-size:20px}.aihl-slots-overview span{color:#646970}.aihl-slots-toolbar{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center;padding:12px 0;border-bottom:1px solid #dcdcde}.aihl-slots-api{margin-top:24px;padding:16px;border:1px solid #dcdcde;background:#fff}.aihl-slots-api summary{cursor:pointer;font-size:14px}
.aihl-slots-empty p{margin:4px 0;color:#646970}
.aihl-canvas-manager{border:1px solid #dcdcde;background:#fff;border-radius:6px;margin:0 0 18px}
.aihl-canvas-manager-head{padding:14px 16px;border-bottom:1px solid #f0f0f1}
.aihl-canvas-manager-head h3{margin:0 0 3px;font-size:15px}
.aihl-canvas-manager-head p{margin:0;color:#646970;font-size:12px}
.aihl-canvas-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0;border-top:0}
.aihl-canvas-card{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-right:1px solid #f0f0f1}
.aihl-canvas-card:last-child{border-right:0}
.aihl-canvas-card strong,.aihl-canvas-card span,.aihl-canvas-card code{display:block}
.aihl-canvas-card strong{font-size:14px;color:#1d2327}
.aihl-canvas-card span{font-size:11px;color:#646970;text-transform:uppercase;font-weight:600}
.aihl-canvas-card code{margin-top:5px;font-size:11px;white-space:normal}
.aihl-code-editor-shell{border:1px solid #dcdcde;background:#fff;border-radius:6px;margin:12px 0 14px;overflow:hidden}
.aihl-code-editor-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border-bottom:1px solid #f0f0f1;background:#f6f7f7}
.aihl-code-editor-head strong,.aihl-code-editor-head span{display:block}
.aihl-code-editor-head strong{font-size:13px;color:#1d2327}
.aihl-code-editor-head span{font-size:11px;color:#646970}
.aihl-code-editor-actions{display:flex;align-items:center;gap:5px}
.aihl-code-editor-tabs{display:flex;align-items:center;gap:4px;padding:8px 10px;border-bottom:1px solid #f0f0f1;background:#fff}
.aihl-code-editor-tabs .button{min-height:28px;padding:0 10px}
.aihl-code-editor-tabs .button.is-active{border-color:#2271b1;color:#0a4b78;box-shadow:inset 0 2px 0 #2271b1}
.aihl-code-editor-pane{display:none}
.aihl-code-editor-pane.is-active{display:block}
.aihl-code-textarea{width:100%;min-height:420px;border:0;box-shadow:none;font-family:Consolas,Monaco,monospace;font-size:13px;line-height:1.5}
.aihl-code-editor-shell .CodeMirror{min-height:420px;border:0;font-size:13px;line-height:1.5}
.aihl-slots-list{display:flex;flex-direction:column;gap:10px}
.aihl-slot-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px;transition:border-color .15s}
.aihl-slot-card:hover{border-color:#2271b1}
.aihl-slot-card.aihl-slot-invalid{border-color:#d63638}
.aihl-slot-inactive{opacity:.6}
.aihl-slot-card-header{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap}
.aihl-slot-card-title{display:flex;align-items:center;gap:8px}
.aihl-slot-card-title strong{font-size:14px;color:#1d2327}
.aihl-slot-status{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.aihl-slot-status-on{background:#16a34a}
.aihl-slot-status-off{background:#dc3545}
.aihl-slot-id{font-size:10px;color:#646970;background:#f0f0f1;padding:1px 6px;border-radius:4px}
.aihl-slot-badges{display:flex;gap:4px;flex-wrap:wrap}
.aihl-slot-badge{font-size:10px;padding:2px 7px;border-radius:6px;font-weight:600;letter-spacing:.02em}
.aihl-sbadge-hook{background:#eff6ff;color:#1e40af}
.aihl-sbadge-type{background:#fdf2f8;color:#9d174d}
.aihl-sbadge-mode{background:#fef3c7;color:#92400e}
.aihl-sbadge-ctx{background:#f0fdf4;color:#166534}
.aihl-sbadge-ver{background:#f0f0f1;color:#646970}
.aihl-slot-card-preview{margin-top:8px}
.aihl-slot-card-preview code{font-size:11px;color:#646970;background:#f6f7f7;padding:6px 10px;border-radius:4px;display:block;white-space:pre-wrap;word-break:break-all;max-height:60px;overflow:hidden;line-height:1.5}
.aihl-slot-card-actions{margin-top:10px;display:flex;align-items:center;justify-content:space-between;gap:6px}
.aihl-slot-meta{font-size:10px;color:#646970}
.aihl-btn-slot-delete{color:#dc3545!important;border-color:#dc3545!important}
.aihl-btn-slot-delete:hover{background:#fef2f2!important}
.aihl-api-ref th{font-size:11px;font-weight:700;color:#9d174d;width:60px;padding:6px 8px}
.aihl-api-ref td{padding:6px 8px;font-size:12px}
.aihl-api-ref code{font-size:11px}
@media (max-width:782px){.aihl-canvas-grid{grid-template-columns:1fr}.aihl-canvas-card{border-right:0;border-bottom:1px solid #f0f0f1}.aihl-canvas-card:last-child{border-bottom:0}.aihl-code-editor-head{align-items:flex-start;flex-direction:column}.aihl-code-textarea,.aihl-code-editor-shell .CodeMirror{min-height:320px}}
CSS;
	wp_add_inline_style('wp-admin', $css);
});
