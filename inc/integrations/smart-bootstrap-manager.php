<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_is_bootstrap_manager_active')) {
	function aihl_is_bootstrap_manager_active() {
		if (defined('SBIN_VERSION') || defined('SBIN_OPTION_BASE')) {
			return true;
		}

		if (function_exists('aihtml_is_plugin_active')) {
			return aihtml_is_plugin_active('smart-bootstrap-manager/smart-bootstrap-manager.php');
		}

		return false;
	}
}

if (!function_exists('aihl_sbm_consumer_contract')) {
	function aihl_sbm_consumer_contract() {
		static $contract = null;

		if (is_array($contract)) {
			return $contract;
		}

		if (!aihl_is_bootstrap_manager_active()) {
			return array();
		}

		if (function_exists('smart_bootstrap_manager_consumer_contract')) {
			$provider_contract = smart_bootstrap_manager_consumer_contract('ai-html');
			$contract = is_array($provider_contract) ? $provider_contract : array();
			return $contract;
		}

		$contract = array(
			'contract_version' => 'fallback',
			'provider' => 'smart-bootstrap-manager',
			'consumer' => 'ai-html',
			'bootstrap' => array(
				'theme_mode' => 'light',
				'resolved_theme_mode' => 'light',
				'css_handle' => 'smart-bootstrap',
				'js_handle' => 'smart-bootstrap',
				'body_classes' => array('sbin-consumer', 'sbin-consumer-ai-html', 'sbin-theme-light'),
			),
			'motion' => array(
				'engine' => 'gsap',
				'available' => false,
				'policy' => 'settings-authorized-markup-requested-reduced-motion-safe',
			),
			'usage_rules' => array(
				'bootstrap_first_markup' => true,
				'do_not_enqueue_bootstrap_twice' => true,
				'consume_css_variables_do_not_duplicate_tokens' => true,
			),
		);

		return $contract;
	}
}

if (!function_exists('aihl_sbm_contract_value')) {
	function aihl_sbm_contract_value($path, $default = '') {
		$contract = aihl_sbm_consumer_contract();
		$value = $contract;
		foreach ((array) $path as $segment) {
			if (!is_array($value) || !array_key_exists($segment, $value)) {
				return $default;
			}
			$value = $value[$segment];
		}
		return $value;
	}
}

if (!function_exists('aihl_sbm_design_governance')) {
	/**
	 * Normalized design-governance state. Without SBM the theme is autonomous.
	 *
	 * @return array<string,mixed>
	 */
	function aihl_sbm_design_governance() {
		$defaults = array(
			'smart_bootstrap_option_design_mode' => 'autonomous',
			'smart_bootstrap_option_design_inherit_colors' => false,
			'smart_bootstrap_option_design_inherit_typography' => false,
			'smart_bootstrap_option_design_inherit_spacing' => false,
			'smart_bootstrap_option_design_inherit_radius' => false,
			'smart_bootstrap_option_design_inherit_components' => false,
			'smart_bootstrap_option_design_inherit_motion' => false,
		);

		if (!aihl_is_bootstrap_manager_active()) {
			return $defaults;
		}

		if (function_exists('smart_bootstrap_manager_get_design_governance')) {
			$provider = smart_bootstrap_manager_get_design_governance();
		} elseif (function_exists('smart_bootstrap_manager_design_governance_api_payload')) {
			$payload = smart_bootstrap_manager_design_governance_api_payload();
			$provider = is_array($payload) ? ($payload['options'] ?? array()) : array();
		} else {
			$provider = array();
		}

		$governance = array_merge($defaults, is_array($provider) ? $provider : array());
		$mode = sanitize_key((string) $governance['smart_bootstrap_option_design_mode']);
		$governance['smart_bootstrap_option_design_mode'] = in_array($mode, array('governed', 'adaptive', 'autonomous'), true)
			? $mode
			: 'governed';

		foreach (array_keys($defaults) as $key) {
			if ('smart_bootstrap_option_design_mode' !== $key) {
				$governance[$key] = (bool) $governance[$key];
			}
		}

		return $governance;
	}
}

if (!function_exists('aihl_sbm_design_mode')) {
	function aihl_sbm_design_mode() {
		$governance = aihl_sbm_design_governance();
		return (string) $governance['smart_bootstrap_option_design_mode'];
	}
}

if (!function_exists('aihl_sbm_design_mode_rank')) {
	/**
	 * Lower ranks are stricter and cannot be relaxed by a consumer.
	 */
	function aihl_sbm_design_mode_rank($mode) {
		$ranks = array(
			'governed' => 0,
			'adaptive' => 1,
			'autonomous' => 2,
		);
		$mode = sanitize_key((string) $mode);
		return $ranks[$mode] ?? $ranks['governed'];
	}
}

if (!function_exists('aihl_sbm_constrain_design_mode')) {
	/**
	 * Resolve a consumer mode without allowing it to weaken global SBM policy.
	 *
	 * @return array{requested:string,global:string,effective:string,allowed:bool}
	 */
	function aihl_sbm_constrain_design_mode($requested) {
		$requested = sanitize_key((string) $requested);
		if (!in_array($requested, array('governed', 'adaptive', 'autonomous'), true)) {
			$requested = aihl_sbm_design_mode();
		}
		$global = aihl_is_bootstrap_manager_active() ? aihl_sbm_design_mode() : 'autonomous';
		$allowed = aihl_sbm_design_mode_rank($requested) <= aihl_sbm_design_mode_rank($global);

		return array(
			'requested' => $requested,
			'global' => $global,
			'effective' => $allowed ? $requested : $global,
			'allowed' => $allowed,
		);
	}
}

if (!function_exists('aihl_sbm_inherits_design_domain')) {
	function aihl_sbm_inherits_design_domain($domain) {
		if (!aihl_is_bootstrap_manager_active()) {
			return false;
		}

		$mode = aihl_sbm_design_mode();
		if ('governed' === $mode) {
			return true;
		}
		if ('autonomous' === $mode) {
			return false;
		}

		$domain = sanitize_key((string) $domain);
		$governance = aihl_sbm_design_governance();
		$key = 'smart_bootstrap_option_design_inherit_' . $domain;
		return !empty($governance[$key]);
	}
}

if (!function_exists('aihl_sbm_effective_css_value')) {
	/**
	 * Resolve a theme request without allowing it to outrank an inherited SBM domain.
	 */
	function aihl_sbm_effective_css_value($domain, $requested, $governed, $adaptive = '') {
		if (!aihl_sbm_inherits_design_domain($domain)) {
			return (string) $requested;
		}
		if ('adaptive' === aihl_sbm_design_mode() && '' !== (string) $adaptive) {
			return (string) $adaptive;
		}
		return (string) $governed;
	}
}

add_filter('body_class', function($classes) {
	if (!aihl_is_bootstrap_manager_active()) {
		return $classes;
	}

	$contract_classes = aihl_sbm_contract_value(array('bootstrap', 'body_classes'), array());
	foreach ((array) $contract_classes as $class) {
		$class = sanitize_html_class((string) $class);
		if ('' !== $class) {
			$classes[] = $class;
		}
	}

	$motion_available = aihl_sbm_contract_value(array('motion', 'available'), false);
	$classes[] = $motion_available ? 'aihl-sbm-motion-gsap' : 'aihl-sbm-motion-static';
	$classes[] = 'aihl-sbm-contract';
	$classes[] = 'aihl-governance-' . aihl_sbm_design_mode();
	foreach (array('colors', 'typography', 'spacing', 'radius', 'components', 'motion') as $domain) {
		if (aihl_sbm_inherits_design_domain($domain)) {
			$classes[] = 'aihl-inherit-' . $domain;
		}
	}

	return array_values(array_unique($classes));
}, 20);

if (!function_exists('aihl_build_bootstrap_bridge_css')) {
	function aihl_build_bootstrap_bridge_css() {
		$contract_version = sanitize_key((string) aihl_sbm_contract_value(array('contract_version'), 'fallback'));
		$theme_mode = sanitize_key((string) aihl_sbm_contract_value(array('bootstrap', 'theme_mode'), 'light'));
		$resolved_theme_mode = sanitize_key((string) aihl_sbm_contract_value(array('bootstrap', 'resolved_theme_mode'), 'light'));
		$motion_available = aihl_sbm_contract_value(array('motion', 'available'), false) ? '1' : '0';

		$css = ':root{';
		$css .= '--aihl-sbm-contract-version:' . $contract_version . ';--aihl-sbm-theme-mode:' . $theme_mode . ';--aihl-sbm-resolved-theme-mode:' . $resolved_theme_mode . ';--aihl-sbm-motion-available:' . $motion_available . ';';
		$css .= '}';

		$css .= 'h1,h2,h3,h4,h5,h6,.fw-bold{font-weight:var(--sbin-headings-weight,500)!important;font-family:var(--bs-headings-font-family,var(--bs-body-font-family,inherit));line-height:var(--bs-headings-line-height,1.2);}';
		$css .= 'body{background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#212529);font-family:var(--bs-body-font-family,inherit);font-size:var(--bs-body-font-size,1rem);line-height:var(--bs-body-line-height,1.5);}';
		$css .= '.btn:not(.btn-square):not(.btn-sm-square):not(.btn-lg-square){padding:var(--sbin-btn-padding-y,.375rem) var(--sbin-btn-padding-x,.75rem);font-weight:var(--sbin-btn-font-weight,400);border-radius:var(--sbin-btn-border-radius,var(--bs-border-radius,.375rem));}';
		$css .= '.form-control,.form-select,.input-group-text{border-radius:var(--sbin-input-border-radius,var(--bs-border-radius,.375rem));}';
		$css .= '.card,.service-item,.team-item,.aihl-footer-cta,.aihl-menu-rich-content{border-radius:var(--sbin-card-border-radius,var(--bs-border-radius,.375rem));}';
		$css .= '.aihl-header-nav .dropdown-menu,.aihl-menu-rich-links .dropdown-item,.aihl-mobile-rail-btn{border-radius:var(--sbin-input-border-radius,var(--bs-border-radius,.375rem));}';
		$css .= '.aihl-header-nav .navbar-nav .nav-link,.aihl-header-nav .dropdown-menu .dropdown-item{font-weight:var(--sbin-btn-font-weight,400);}';
		$css .= '.aihl-header-nav .dropdown-menu .dropdown-item:hover,.aihl-header-nav .dropdown-menu .dropdown-item:focus{background:rgba(var(--bs-primary-rgb,13,110,253),.1);}';
		$css .= '.section-title h6::before,.section-title h6::after{background:rgba(var(--bs-primary-rgb,13,110,253),.45);}';
		$css .= '.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-brand,.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-brand .h2{color:var(--bs-primary,#0d6efd);}';
		$css .= '.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-nav>.current-menu-item>.nav-link,.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-nav>.current-menu-ancestor>.nav-link{color:var(--bs-link-hover-color,var(--bs-primary,#0d6efd));}';
		$css .= '.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-brand,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-brand .h4,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-kicker,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-contact i,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-menu a::before{color:var(--bs-primary,#0d6efd);}';
		$css .= '.aihl-footer:not(.aihl-footer-surface-dark) a{color:var(--bs-link-color,var(--bs-primary,#0d6efd));}.aihl-footer:not(.aihl-footer-surface-dark) a:hover,.aihl-footer:not(.aihl-footer-surface-dark) a:focus{color:var(--bs-link-hover-color,var(--bs-primary,#0d6efd));}';
		$css .= '.aihl-footer-form input[type="submit"],.aihl-footer-form button[type="submit"],.aihl-footer .mc4wp-form input[type="submit"],.aihl-footer .mc4wp-form button[type="submit"]{background:var(--bs-primary)!important;border-color:var(--bs-primary)!important;color:var(--sbin-primary-contrast,#fff)!important;border-radius:var(--sbin-btn-border-radius,var(--bs-border-radius,.375rem))!important;}';
		$css .= '.aihl-footer-form input[type="radio"],.aihl-footer-form input[type="checkbox"],.aihl-footer .mc4wp-form input[type="radio"],.aihl-footer .mc4wp-form input[type="checkbox"]{accent-color:var(--bs-primary)!important;}';
		$css .= '.aihl-footer-form .form-check-input:checked,.aihl-footer .mc4wp-form .form-check-input:checked{background-color:var(--bs-primary)!important;border-color:var(--bs-primary)!important;}';
		$css .= '.text-primary{color:var(--bs-primary)!important;}.bg-primary{background-color:var(--bs-primary)!important;}.border-primary{border-color:var(--bs-primary)!important;}';

		return $css;
	}
}

add_action('wp_enqueue_scripts', function() {
	if (is_admin() || !aihl_is_bootstrap_manager_active()) {
		return;
	}

	$has_bootstrap = wp_style_is('smart-bootstrap', 'enqueued') || wp_style_is('smart-bootstrap', 'registered');
	if (!$has_bootstrap) {
		return;
	}

	wp_enqueue_style(
		'aihl-bootstrap-bridge',
		AIHL_DIR_URL . '/resource/css/aihl-bootstrap-bridge.css',
		array('smart-bootstrap', 'ai-html-theme'),
		AIHL_UNICODE
	);

	$dynamic_css = aihl_build_bootstrap_bridge_css();
	if (is_string($dynamic_css) && $dynamic_css !== '') {
		wp_add_inline_style('aihl-bootstrap-bridge', $dynamic_css);
	}
}, 120);
