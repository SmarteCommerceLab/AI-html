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

if (!function_exists('aihl_build_bootstrap_bridge_css')) {
	function aihl_build_bootstrap_bridge_css() {
		$css = ':root{';
		$css .= '--primary:var(--bs-primary,#0d6efd);--secondary:var(--bs-secondary,#6c757d);--light:var(--bs-light,#f8f9fa);--dark:var(--bs-dark,#212529);';
		$css .= '--aihl-brand-color:var(--bs-primary,#0d6efd);';
		$css .= '--aihl-accent-color:var(--bs-primary,#0d6efd);';
		$css .= '--aihl-ui-link-color:var(--bs-link-color,var(--bs-primary,#0d6efd));';
		$css .= '--aihl-ui-link-hover-color:var(--bs-link-hover-color,var(--bs-primary,#0d6efd));';
		$css .= '--aihl-primary-contrast:var(--sbin-primary-contrast,#fff);';
		$css .= '--aihl-headings-weight:var(--sbin-headings-weight,500);';
		$css .= '--aihl-btn-padding-y:var(--sbin-btn-padding-y,.375rem);--aihl-btn-padding-x:var(--sbin-btn-padding-x,.75rem);--aihl-btn-font-weight:var(--sbin-btn-font-weight,400);--aihl-btn-border-radius:var(--sbin-btn-border-radius,var(--bs-border-radius,.375rem));';
		$css .= '--aihl-input-border-radius:var(--sbin-input-border-radius,var(--bs-border-radius,.375rem));--aihl-card-border-radius:var(--sbin-card-border-radius,var(--bs-border-radius,.375rem));';
		$css .= '}';

		$css .= 'h1,h2,h3,h4,h5,h6,.fw-bold{font-weight:var(--aihl-headings-weight)!important;font-family:var(--bs-headings-font-family,var(--bs-body-font-family,inherit));line-height:var(--bs-headings-line-height,1.2);}';
		$css .= 'body{background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#212529);font-family:var(--bs-body-font-family,inherit);font-size:var(--bs-body-font-size,1rem);line-height:var(--bs-body-line-height,1.5);}';
		$css .= '.btn:not(.btn-square):not(.btn-sm-square):not(.btn-lg-square){padding:var(--aihl-btn-padding-y) var(--aihl-btn-padding-x);font-weight:var(--aihl-btn-font-weight);border-radius:var(--aihl-btn-border-radius);}';
		$css .= '.form-control,.form-select,.input-group-text{border-radius:var(--aihl-input-border-radius);}';
		$css .= '.card,.service-item,.team-item,.aihl-footer-cta,.aihl-menu-rich-content{border-radius:var(--aihl-card-border-radius);}';
		$css .= '.aihl-header-nav .dropdown-menu,.aihl-menu-rich-links .dropdown-item,.aihl-mobile-rail-btn{border-radius:var(--aihl-input-border-radius);}';
		$css .= '.aihl-header-nav .navbar-nav .nav-link,.aihl-header-nav .dropdown-menu .dropdown-item{font-weight:var(--aihl-btn-font-weight);}';
		$css .= '.aihl-header-nav .dropdown-menu .dropdown-item:hover,.aihl-header-nav .dropdown-menu .dropdown-item:focus{background:rgba(var(--bs-primary-rgb,13,110,253),.1);}';
		$css .= '.section-title h6::before,.section-title h6::after{background:rgba(var(--bs-primary-rgb,13,110,253),.45);}';
		$css .= '.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-brand,.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-brand .h2{color:var(--aihl-brand-color);}';
		$css .= '.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-nav>.current-menu-item>.nav-link,.aihl-header-nav:not(.aihl-header-overlay):not(.aihl-overlay-mode-always) .navbar-nav>.current-menu-ancestor>.nav-link{color:var(--aihl-ui-link-hover-color);}';
		$css .= '.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-brand,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-brand .h4,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-kicker,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-contact i,.aihl-footer:not(.aihl-footer-surface-dark) .aihl-footer-menu a::before{color:var(--aihl-accent-color);}';
		$css .= '.aihl-footer:not(.aihl-footer-surface-dark) a{color:var(--aihl-ui-link-color);}.aihl-footer:not(.aihl-footer-surface-dark) a:hover,.aihl-footer:not(.aihl-footer-surface-dark) a:focus{color:var(--aihl-ui-link-hover-color);}';
		$css .= '.aihl-footer-form input[type="submit"],.aihl-footer-form button[type="submit"],.aihl-footer .mc4wp-form input[type="submit"],.aihl-footer .mc4wp-form button[type="submit"]{background:var(--bs-primary)!important;border-color:var(--bs-primary)!important;color:var(--aihl-primary-contrast)!important;border-radius:var(--aihl-btn-border-radius)!important;}';
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
