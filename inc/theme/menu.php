<?php
/* Register Menu */
add_action('init',function(){register_nav_menus( array(
	'topic'				=> __('Topic', AIHL_TEXT_DOMAIN),
	'utili'				=> __('Utili', AIHL_TEXT_DOMAIN),

	'naviga'			=> __('Naviga', AIHL_TEXT_DOMAIN),
	'footer'			=> __('Footer', AIHL_TEXT_DOMAIN),

	'topic_left'		=> __('Topic Left (Mega Centered)', AIHL_TEXT_DOMAIN),
	'topic_right'		=> __('Topic Right (Mega Centered)', AIHL_TEXT_DOMAIN),
	'footer_col_1'		=> __('Footer Colonna 1', AIHL_TEXT_DOMAIN),
	'footer_col_2'		=> __('Footer Colonna 2', AIHL_TEXT_DOMAIN),
	'footer_col_3'		=> __('Footer Colonna 3', AIHL_TEXT_DOMAIN),
	'footer_col_4'		=> __('Footer Colonna 4', AIHL_TEXT_DOMAIN),
));});

if (!function_exists('aihl_get_nav_menu_fallback_locations')) {
	function aihl_get_nav_menu_fallback_locations($location) {
		$location = sanitize_key((string) $location);
		$fallbacks = array(
			'topic'       => array('topic', 'naviga'),
			'utili'       => array('utili', 'footer'),
			'naviga'      => array('naviga', 'topic'),
			'footer'      => array('footer', 'utili'),
			'topic_left'  => array('topic_left', 'topic'),
			'topic_right' => array('topic_right', 'utili', 'topic'),
		);
		$locations = $fallbacks[$location] ?? array($location);

		return array_values(array_unique(array_filter(array_map('sanitize_key', (array) apply_filters(
			'aihl_nav_menu_fallback_locations',
			$locations,
			$location
		)))));
	}
}

if (!function_exists('aihl_get_nav_menu_keywords')) {
	function aihl_get_nav_menu_keywords($location) {
		$location = sanitize_key((string) $location);
		$footer_locations = array('utili', 'footer');
		$keywords = in_array($location, $footer_locations, true)
			? array('footer', 'utili', 'utility', 'bottom', 'piè', 'pie')
			: array('primary', 'main', 'principale', 'header', 'navigazione', 'navigation', 'menu');

		return (array) apply_filters('aihl_nav_menu_fallback_keywords', $keywords, $location);
	}
}

if (!function_exists('aihl_resolve_nav_menu')) {
	function aihl_resolve_nav_menu($location) {
		$requested_location = sanitize_key((string) $location);
		$empty = array(
			'requested_location' => $requested_location,
			'location'           => '',
			'menu_id'            => 0,
			'menu_name'          => '',
			'source'             => 'unavailable',
		);
		if ('' === $requested_location) {
			return $empty;
		}

		$assigned = get_nav_menu_locations();
		foreach (aihl_get_nav_menu_fallback_locations($requested_location) as $candidate) {
			$menu_id = absint($assigned[$candidate] ?? 0);
			$menu = $menu_id ? wp_get_nav_menu_object($menu_id) : false;
			if (!$menu || (isset($menu->count) && (int) $menu->count < 1)) {
				continue;
			}

			return (array) apply_filters('aihl_resolved_nav_menu', array(
				'requested_location' => $requested_location,
				'location'           => $candidate,
				'menu_id'            => $menu_id,
				'menu_name'          => (string) $menu->name,
				'source'             => $candidate === $requested_location ? 'assigned' : 'location_alias',
			), $requested_location);
		}

		$menus = array_values(array_filter((array) wp_get_nav_menus(array('hide_empty' => true)), static function ($menu) {
			return is_object($menu) && absint($menu->term_id ?? 0) > 0 && (!isset($menu->count) || (int) $menu->count > 0);
		}));
		$keywords = aihl_get_nav_menu_keywords($requested_location);
		$ranked = array();
		foreach ($menus as $menu) {
			$haystack = sanitize_title((string) ($menu->name ?? '') . ' ' . (string) ($menu->slug ?? ''));
			$score = 0;
			foreach ($keywords as $keyword) {
				$needle = sanitize_title((string) $keyword);
				if ('' !== $needle && false !== strpos($haystack, $needle)) {
					$score++;
				}
			}
			if ($score > 0) {
				$ranked[] = array('score' => $score, 'menu' => $menu);
			}
		}

		if ($ranked) {
			usort($ranked, static function ($left, $right) {
				if ($left['score'] === $right['score']) {
					return absint($left['menu']->term_id) <=> absint($right['menu']->term_id);
				}
				return $right['score'] <=> $left['score'];
			});
			if (1 === count($ranked) || $ranked[0]['score'] > $ranked[1]['score']) {
				$menus = array($ranked[0]['menu']);
			} else {
				$menus = array();
			}
		} elseif (1 !== count($menus)) {
			$menus = array();
		}

		if ($menus) {
			$menu = $menus[0];
			return (array) apply_filters('aihl_resolved_nav_menu', array(
				'requested_location' => $requested_location,
				'location'           => '',
				'menu_id'            => absint($menu->term_id),
				'menu_name'          => (string) ($menu->name ?? ''),
				'source'             => 'menu_fallback',
			), $requested_location);
		}

		return (array) apply_filters('aihl_resolved_nav_menu', $empty, $requested_location);
	}
}

if (!function_exists('aihl_resolve_nav_menu_args')) {
	function aihl_resolve_nav_menu_args($location, array $args = array()) {
		$resolved = aihl_resolve_nav_menu($location);
		unset($args['menu'], $args['theme_location']);
		if (!empty($resolved['location'])) {
			$args['theme_location'] = $resolved['location'];
		} elseif (!empty($resolved['menu_id'])) {
			$args['menu'] = absint($resolved['menu_id']);
		} else {
			$args['theme_location'] = sanitize_key((string) $location);
		}
		if (!isset($args['fallback_cb'])) {
			$args['fallback_cb'] = false;
		}

		return $args;
	}
}

if (!function_exists('aihl_has_resolved_nav_menu')) {
	function aihl_has_resolved_nav_menu($location) {
		$resolved = aihl_resolve_nav_menu($location);
		return !empty($resolved['menu_id']);
	}
}
