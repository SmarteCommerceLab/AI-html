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

		// Sanitizzazione codice per tipo
		$code = $slot['code'] ?? '';
		$css = $slot['css'] ?? '';
		$js = $slot['js'] ?? '';

		// CSS: rimuovi tag <style> e expression()
		$css = preg_replace('/<\/?style[^>]*>/i', '', $css);
		$css = preg_replace('/expression\s*\(/i', '/* blocked */(', $css);

		// Salva storico per rollback (ultima versione)
		$previous_code = $existing['code'] ?? '';

		$clean = array(
			'id'            => $id,
			'hook'          => sanitize_key($slot['hook']),
			'type'          => $type,
			'code'          => $code,
			'css'           => $css,
			'js'            => $js,
			'context'       => $slot['context'] ?? 'global',
			'priority'      => isset($slot['priority']) ? max(1, min(999, (int) $slot['priority'])) : 10,
			'active'        => isset($slot['active']) ? (bool) $slot['active'] : true,
			'label'         => sanitize_text_field($slot['label'] ?? $id),
			'author'        => sanitize_text_field($slot['author'] ?? (wp_get_current_user()->user_login ?: 'system')),
			'version'       => $version,
			'previous_code' => $previous_code,
			'created'       => $existing['created'] ?? current_time('mysql'),
			'updated'       => current_time('mysql'),
		);

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
		if (empty($slot['previous_code'])) {
			return new WP_Error('no_previous', __('Nessuna versione precedente disponibile.', AIHL_TEXT_DOMAIN));
		}
		$slots[$id]['code'] = $slot['previous_code'];
		$slots[$id]['previous_code'] = $slot['code'];
		$slots[$id]['version'] = (int) ($slot['version'] ?? 0) + 1;
		$slots[$id]['updated'] = current_time('mysql');
		update_option(AIHL_CODE_SLOTS_OPTION, $slots, false);
		return $slots[$id];
	}
}

/* ============================================================================
 * 3b. Override check — verifica se un hook override ha slot attivi
 * ============================================================================ */

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
		$slots = aihl_code_slots_get_all();
		if (empty($slots)) {
			return false;
		}
		foreach ($slots as $slot) {
			if ($slot['hook'] === $hook && !empty($slot['active'])) {
				if (aihl_code_slot_context_matches($slot['context'] ?? 'global')) {
					return true;
				}
			}
		}
		return false;
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

		// Filtra slot per questo hook, attivi e con context match
		$active = array();
		foreach ($slots as $slot) {
			if ($slot['hook'] !== $hook || empty($slot['active'])) {
				continue;
			}
			if (!aihl_code_slot_context_matches($slot['context'] ?? 'global')) {
				continue;
			}
			$active[] = $slot;
		}

		if (empty($active)) {
			return;
		}

		// Ordina per priorità
		usort($active, function ($a, $b) {
			return ($a['priority'] ?? 10) - ($b['priority'] ?? 10);
		});

		foreach ($active as $slot) {
			$type = $slot['type'] ?? 'html';

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
				return rest_ensure_response(array(
					'count' => count($slots),
					'slots' => array_values($slots),
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
				return rest_ensure_response($result);
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
				return rest_ensure_response($slot);
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
				return rest_ensure_response($result);
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
				unset($slot['previous_code']); // Non esportare storico
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
				'description' => 'Aggiorna opzioni tema. Body JSON con coppie chiave:valore. Usa /ai/options/schema per i campi disponibili.',
				'example'     => '{"header_structure":"dualbar","footer_variant":"futuristic","header_cta_label":"Richiedi demo"}',
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
			'workflow'   => array(
				'1_discover'    => 'GET /ai/capabilities — stai leggendo questo',
				'2_understand'  => 'GET /ai/introspection + /ai/integration-manifest — leggi stato e risorse runtime',
				'3_configure'   => 'POST /ai/options — modifica header, footer, CTA, contatti',
				'4_structure'   => 'POST /ai/menus + POST /ai/pages — crea menu e pagine',
				'5_customize'   => 'POST /ai/code-slots — inietta HTML/CSS/JS in qualsiasi punto',
				'6_override'    => 'POST /ai/code-slots con hook=header_full o footer_full — sostituisci intero header/footer',
				'7_deploy'      => 'POST /ai/deploy — deploy completo da project.json',
				'8_reset'       => 'POST /ai/reset — resetta e ricomincia',
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

		// Handle POST save
		if (isset($_POST['aihl_code_slot_save']) && check_admin_referer('aihl_code_slots_nonce')) {
			$slot_data = array(
				'id'       => sanitize_key(wp_unslash($_POST['slot_id'] ?? '')),
				'label'    => sanitize_text_field(wp_unslash($_POST['slot_label'] ?? '')),
				'hook'     => sanitize_key(wp_unslash($_POST['slot_hook'] ?? '')),
				'type'     => sanitize_key(wp_unslash($_POST['slot_type'] ?? 'html')),
				'context'  => sanitize_text_field(wp_unslash($_POST['slot_context'] ?? 'global')),
				'priority' => (int) ($_POST['slot_priority'] ?? 10),
				'active'   => !empty($_POST['slot_active']),
				'code'     => wp_unslash($_POST['slot_code'] ?? ''),
				'css'      => wp_unslash($_POST['slot_css'] ?? ''),
				'js'       => wp_unslash($_POST['slot_js'] ?? ''),
				'author'   => 'admin',
			);
			$save_result = aihl_code_slots_save($slot_data);
			if (!is_wp_error($save_result)) {
				$slots = aihl_code_slots_get_all(); // Refresh
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
			aihl_code_slots_toggle($tog_id, $tog_active);
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
			$s = $edit_slot ?: array('id' => '', 'label' => '', 'hook' => 'before_header', 'type' => 'html', 'context' => 'global', 'priority' => 10, 'active' => true, 'code' => '', 'css' => '', 'js' => '');
		?>
			<!-- Editor singolo slot -->
			<form method="post">
				<?php wp_nonce_field('aihl_code_slots_nonce'); ?>
				<input type="hidden" name="slot_id" value="<?php echo esc_attr($s['id']); ?>">

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
						<th><?php esc_html_e('Priorita', AIHL_TEXT_DOMAIN); ?></th>
						<td><input type="number" name="slot_priority" value="<?php echo (int) $s['priority']; ?>" min="1" max="999" style="width:80px"></td>
					</tr>
					<tr>
						<th><?php esc_html_e('Attivo', AIHL_TEXT_DOMAIN); ?></th>
						<td><label><input type="checkbox" name="slot_active" value="1" <?php checked($s['active']); ?>> <?php esc_html_e('Abilita questo slot', AIHL_TEXT_DOMAIN); ?></label></td>
					</tr>
					<tr class="aihl-slot-field-code">
						<th><?php esc_html_e('Codice', AIHL_TEXT_DOMAIN); ?></th>
						<td><textarea name="slot_code" rows="12" class="large-text code" style="font-family:monospace;font-size:13px;"><?php echo esc_textarea($s['code']); ?></textarea></td>
					</tr>
					<tr class="aihl-slot-field-css" style="display:none;">
						<th><?php esc_html_e('CSS', AIHL_TEXT_DOMAIN); ?></th>
						<td><textarea name="slot_css" rows="8" class="large-text code" style="font-family:monospace;font-size:13px;"><?php echo esc_textarea($s['css'] ?? ''); ?></textarea></td>
					</tr>
					<tr class="aihl-slot-field-js" style="display:none;">
						<th><?php esc_html_e('JavaScript', AIHL_TEXT_DOMAIN); ?></th>
						<td><textarea name="slot_js" rows="8" class="large-text code" style="font-family:monospace;font-size:13px;"><?php echo esc_textarea($s['js'] ?? ''); ?></textarea></td>
					</tr>
				</table>

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

				// Toggle campi mixed
				var radios=document.querySelectorAll('[name=slot_type]');
				function togFields(){
					var t=document.querySelector('[name=slot_type]:checked').value;
					document.querySelector('.aihl-slot-field-code').style.display=(t==='css'||t==='js')?'none':'';
					document.querySelector('.aihl-slot-field-css').style.display=(t==='mixed'||t==='css')?'':'none';
					document.querySelector('.aihl-slot-field-js').style.display=(t==='mixed'||t==='js')?'':'none';
					// Per css/js puri, usa il campo code come textarea principale
					if(t==='css'){
						document.querySelector('.aihl-slot-field-css').style.display='';
						document.querySelector('[name=slot_css]').setAttribute('name','slot_code');
					}else if(t==='js'){
						document.querySelector('.aihl-slot-field-js').style.display='';
						document.querySelector('[name=slot_js]').setAttribute('name','slot_code');
					}
				}
				radios.forEach(function(r){r.addEventListener('change',togFields);});
				togFields();
			})();
			</script>

		<?php else : ?>
			<!-- Lista slot -->
			<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
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
					<?php foreach ($slots as $slot) : ?>
						<div class="aihl-slot-card <?php echo empty($slot['active']) ? 'aihl-slot-inactive' : ''; ?>">
							<div class="aihl-slot-card-header">
								<div class="aihl-slot-card-title">
									<span class="aihl-slot-status <?php echo empty($slot['active']) ? 'aihl-slot-status-off' : 'aihl-slot-status-on'; ?>"></span>
									<strong><?php echo esc_html($slot['label']); ?></strong>
									<code class="aihl-slot-id"><?php echo esc_html($slot['id']); ?></code>
								</div>
								<div class="aihl-slot-badges">
									<span class="aihl-slot-badge aihl-sbadge-hook"><?php echo esc_html($slot['hook']); ?></span>
									<span class="aihl-slot-badge aihl-sbadge-type"><?php echo esc_html(strtoupper($slot['type'] ?? 'html')); ?></span>
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
			<h3 style="margin-top:28px;"><?php esc_html_e('API Code Slots', AIHL_TEXT_DOMAIN); ?></h3>
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
	$css = <<<'CSS'
.aihl-slots-empty{text-align:center;padding:60px 20px;background:#f6f7f7;border:1px dashed #dcdcde;border-radius:8px}
.aihl-slots-empty p{margin:4px 0;color:#646970}
.aihl-slots-list{display:flex;flex-direction:column;gap:10px}
.aihl-slot-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px;transition:border-color .15s}
.aihl-slot-card:hover{border-color:#2271b1}
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
CSS;
	wp_add_inline_style('wp-admin', $css);
});
