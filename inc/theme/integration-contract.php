<?php
/**
 * Runtime contract between AI-HTML, SBS, SBM and AI content generators.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_get_addon_integrations')) {
	function aihl_get_addon_integrations() {
		$integrations = array(
			'contact_form_7' => array(
				'label'       => 'Contact Form 7',
				'active'      => shortcode_exists('contact-form-7'),
				'option'      => 'contactform_contacts',
				'resource_id' => absint(aihtml_option_value('contactform_contacts', 0)),
				'post_type'   => 'wpcf7_contact_form',
			),
			'mailchimp_for_wp' => array(
				'label'       => 'Mailchimp for WordPress',
				'active'      => shortcode_exists('mc4wp_form'),
				'option'      => 'mailchip_footer',
				'resource_id' => absint(aihtml_option_value('mailchip_footer', 0)),
				'post_type'   => 'mc4wp-form',
			),
		);

		foreach ($integrations as $provider => &$integration) {
			$integration['provider'] = $provider;
			$integration['configured'] = $integration['resource_id'] > 0;
			$integration['resources'] = array();
			if (post_type_exists($integration['post_type'])) {
				$posts = get_posts(array(
					'post_type'      => $integration['post_type'],
					'post_status'    => array('publish', 'draft'),
					'posts_per_page' => 100,
					'orderby'        => 'title',
					'order'          => 'ASC',
				));
				foreach ($posts as $post) {
					$integration['resources'][] = array(
						'id'     => (int) $post->ID,
						'title'  => get_the_title($post),
						'status' => $post->post_status,
					);
				}
			}
		}
		unset($integration);

		return apply_filters('aihl_addon_integrations', $integrations);
	}
}

if (!function_exists('aihl_get_theme_integration_manifest')) {
	function aihl_get_theme_integration_manifest() {
		$locations = get_registered_nav_menus();
		$assigned = get_nav_menu_locations();
		$menus = array();
		foreach ($locations as $location => $label) {
			$menu_id = absint($assigned[$location] ?? 0);
			$menu = $menu_id ? wp_get_nav_menu_object($menu_id) : false;
			$menus[$location] = array(
				'label'      => wp_strip_all_tags($label),
				'assigned'   => (bool) $menu,
				'menu_id'    => $menu_id,
				'menu_name'  => $menu ? $menu->name : '',
				'item_count' => $menu ? (int) $menu->count : 0,
			);
		}

		$logos = array();
		foreach (array('default', 'transparent', 'light', 'footer') as $variant) {
			$logo = aihl_get_site_logo_data($variant);
			$logos[$variant] = is_array($logo) ? $logo : array(
				'url'      => '',
				'source'   => 'site-name-fallback',
				'variant'  => $variant,
				'fallback' => get_bloginfo('name'),
			);
		}

		return apply_filters('aihl_theme_integration_manifest', array(
			'contract' => array(
				'name'    => 'Smart Theme Integration Contract',
				'version' => '1.0.0',
				'theme'   => AIHL_THEME_NAME,
				'theme_version' => AIHL_VERSION,
			),
			'site' => array(
				'name'        => get_bloginfo('name'),
				'description' => get_bloginfo('description'),
				'url'         => home_url('/'),
				'language'    => get_bloginfo('language'),
			),
			'resources' => array(
				'logos'    => $logos,
				'menus'    => $menus,
				'social'   => aihl_get_site_builder_social_links(),
				'contacts' => array(
					'phone'   => (string) aihtml_option_value('contatti_telefono', ''),
					'email'   => (string) aihtml_option_value('contatti_email', ''),
					'address' => (string) aihtml_option_value('contatti_indirizzo', ''),
				),
				'addons' => aihl_get_addon_integrations(),
			),
			'runtime_components' => array(
				'smart-logo'    => array('attributes' => array('variant', 'class', 'link')),
				'smart-menu'    => array('attributes' => array('location', 'class', 'depth')),
				'smart-social'  => array('attributes' => array('class')),
				'smart-contact' => array('attributes' => array('field', 'link', 'class')),
				'smart-addon'   => array('attributes' => array('provider', 'id')),
			),
			'policies' => array(
				'content_first' => true,
				'server_side_resources' => true,
				'logo_fallback_order' => array('requested-variant', 'default', 'wordpress-custom-logo', 'site-name'),
				'menu_rule' => 'Use a registered theme location; never hard-code navigation links when a menu is assigned.',
				'ai_canvas_rule' => 'AI Canvas must declare design_mode. Governed consumes SBM tokens; adaptive derives semantic tokens; autonomous remains scoped. WordPress identity and navigation use runtime components.',
				'design_governance' => function_exists('smart_bootstrap_manager_design_governance_api_payload')
					? smart_bootstrap_manager_design_governance_api_payload()
					: array(),
			),
			'apis' => array(
				'manifest' => rest_url('aihtml/v1/ai/integration-manifest'),
				'options'  => rest_url('aihtml/v1/ai/options'),
				'menus'    => rest_url('aihtml/v1/ai/menus'),
				'addons'   => rest_url('aihtml/v1/ai/addons'),
				'sbs'      => rest_url('sbs/v1/ai/widgets'),
				'sbm'      => rest_url('smart-bootstrap-manager/v1/effects'),
				'sbm_design' => rest_url('smart-bootstrap-manager/v1/design-governance'),
			),
		));
	}
}

if (!function_exists('aihl_render_addon_integration')) {
	function aihl_render_addon_integration($provider, $resource_id = 0) {
		$provider = sanitize_key((string) $provider);
		$integrations = aihl_get_addon_integrations();
		if (!isset($integrations[$provider]) || empty($integrations[$provider]['active'])) {
			return '';
		}

		$resource_id = absint($resource_id ?: $integrations[$provider]['resource_id']);
		if ($resource_id < 1) {
			return '';
		}

		if ('contact_form_7' === $provider) {
			return do_shortcode('[contact-form-7 id="' . $resource_id . '"]');
		}
		if ('mailchimp_for_wp' === $provider) {
			return do_shortcode('[mc4wp_form id="' . $resource_id . '"]');
		}

		return (string) apply_filters('aihl_render_addon_integration', '', $provider, $resource_id);
	}
}

if (!function_exists('aihl_dynamic_component_attributes')) {
	function aihl_dynamic_component_attributes($source) {
		$attributes = array();
		if (preg_match_all('/([a-zA-Z0-9_-]+)\s*=\s*(["\'])(.*?)\2/s', (string) $source, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$attributes[sanitize_key($match[1])] = sanitize_text_field(html_entity_decode($match[3], ENT_QUOTES, 'UTF-8'));
			}
		}
		return $attributes;
	}
}

if (!function_exists('aihl_render_dynamic_component')) {
	function aihl_render_dynamic_component($name, array $attributes) {
		$name = sanitize_key((string) $name);
		if ('smart-logo' === $name) {
			$variant = sanitize_key($attributes['variant'] ?? 'default');
			$class = sanitize_text_field($attributes['class'] ?? 'aihl-runtime-logo');
			ob_start();
			$rendered = aihl_render_site_logo($variant, $class);
			$html = (string) ob_get_clean();
			if (!$rendered) {
				$html = '<span class="' . esc_attr($class . ' aihl-runtime-logo-text') . '">' . esc_html(get_bloginfo('name')) . '</span>';
			}
			if (!isset($attributes['link']) || 'false' !== strtolower($attributes['link'])) {
				$html = '<a href="' . esc_url(home_url('/')) . '" class="aihl-runtime-brand" aria-label="' . esc_attr(get_bloginfo('name')) . '">' . $html . '</a>';
			}
			return $html;
		}

		if ('smart-menu' === $name) {
			$location = sanitize_key($attributes['location'] ?? 'topic');
			if (!has_nav_menu($location)) {
				return '';
			}
			return wp_nav_menu(array(
				'theme_location' => $location,
				'menu_class'     => sanitize_text_field($attributes['class'] ?? 'aihl-runtime-menu'),
				'container'      => false,
				'depth'          => max(1, min(5, absint($attributes['depth'] ?? 3))),
				'fallback_cb'    => false,
				'echo'           => false,
			));
		}

		if ('smart-social' === $name) {
			ob_start();
			aihl_render_social_links(sanitize_text_field($attributes['class'] ?? 'aihl-runtime-social-link'));
			return (string) ob_get_clean();
		}

		if ('smart-contact' === $name) {
			$field = sanitize_key($attributes['field'] ?? '');
			$map = array(
				'phone'   => array('contatti_telefono', 'tel:'),
				'email'   => array('contatti_email', 'mailto:'),
				'address' => array('contatti_indirizzo', ''),
			);
			if (!isset($map[$field])) {
				return '';
			}
			$value = trim((string) aihtml_option_value($map[$field][0], ''));
			if ('' === $value) {
				return '';
			}
			if (!empty($attributes['link']) && 'false' !== strtolower($attributes['link']) && '' !== $map[$field][1]) {
				return '<a class="' . esc_attr(sanitize_text_field($attributes['class'] ?? '')) . '" href="' . esc_url($map[$field][1] . $value) . '">' . esc_html($value) . '</a>';
			}
			return esc_html($value);
		}

		if ('smart-addon' === $name) {
			return aihl_render_addon_integration($attributes['provider'] ?? '', absint($attributes['id'] ?? 0));
		}

		return '';
	}
}

if (!function_exists('aihl_expand_dynamic_components')) {
	function aihl_expand_dynamic_components($html) {
		$allowed = 'smart-logo|smart-menu|smart-social|smart-contact|smart-addon';
		$html = preg_replace_callback(
			'/<(' . $allowed . ')\b([^>]*)>(?:\s*<\/\1>)?/i',
			static function ($match) {
				return aihl_render_dynamic_component(strtolower($match[1]), aihl_dynamic_component_attributes($match[2]));
			},
			(string) $html
		);
		return is_string($html) ? $html : '';
	}
}
