<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_get_mobile_navigation_config')) {
	function aihl_get_mobile_navigation_config() {
		$position = (string) aihtml_option_value('mobile_rail_position', 'right');
		if (!in_array($position, array('left', 'right'), true)) {
			$position = 'right';
		}

		$style = (string) aihtml_option_value('mobile_nav_style', 'rail');
		if (!in_array($style, array('rail', 'bottom-bar', 'none'), true)) {
			$style = 'rail';
		}

		return array(
			'rail_enabled' => (bool) aihtml_option_value('mobile_rail_enable', true),
			'rail_position' => $position,
			'rail_autohide' => (bool) aihtml_option_value('mobile_rail_autohide', false),
			'style' => $style,
			'offcanvas_class' => $position === 'left' ? 'offcanvas-start' : 'offcanvas-end',
			'phone' => trim((string) aihtml_option_value('contatti_telefono', '')),
			'cta_label' => trim((string) aihtml_option_value('header_cta_label', 'Consulenza gratuita')),
			'cta_url' => esc_url((string) aihtml_option_value('header_cta_url', '#')),
			'login_label' => trim((string) aihtml_option_value('header_login_label', 'Login')),
			'login_url' => esc_url((string) aihtml_option_value('header_login_url', '#')),
		);
	}
}

if (!function_exists('aihl_render_mobile_quick_navigation')) {
	function aihl_render_mobile_quick_navigation($config, $has_header_override = false) {
		if ($has_header_override || !is_array($config)) {
			return;
		}

		$style = isset($config['style']) ? (string) $config['style'] : 'none';
		if ($style === 'rail' && !empty($config['rail_enabled'])) {
			$position = isset($config['rail_position']) && $config['rail_position'] === 'left' ? 'left' : 'right';
			$phone = isset($config['phone']) ? trim((string) $config['phone']) : '';
			?>
			<aside class="aihl-mobile-rail aihl-mobile-rail-<?php echo esc_attr($position); ?> d-lg-none" aria-label="<?php esc_attr_e('Azioni rapide', AIHL_TEXT_DOMAIN); ?>">
				<button type="button" class="aihl-mobile-rail-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="<?php esc_attr_e('Apri menu', AIHL_TEXT_DOMAIN); ?>">
					<i class="fa-solid fa-bars" aria-hidden="true"></i>
				</button>
				<a class="aihl-mobile-rail-btn" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Home', AIHL_TEXT_DOMAIN); ?>">
					<i class="fa-solid fa-house" aria-hidden="true"></i>
				</a>
				<?php if ($phone !== '') : ?>
					<a class="aihl-mobile-rail-btn" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" aria-label="<?php esc_attr_e('Chiama', AIHL_TEXT_DOMAIN); ?>">
						<i class="fa-solid fa-phone" aria-hidden="true"></i>
					</a>
				<?php endif; ?>
				<a class="aihl-mobile-rail-btn" href="#main" aria-label="<?php esc_attr_e('Vai al contenuto', AIHL_TEXT_DOMAIN); ?>">
					<i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
				</a>
			</aside>
			<?php
			return;
		}

		if ($style !== 'bottom-bar') {
			return;
		}

		$cta_label = isset($config['cta_label']) ? trim((string) $config['cta_label']) : '';
		$cta_url = isset($config['cta_url']) ? (string) $config['cta_url'] : '';
		$login_label = isset($config['login_label']) ? trim((string) $config['login_label']) : '';
		$login_url = isset($config['login_url']) ? (string) $config['login_url'] : '';
		?>
		<nav class="aihl-bottom-bar d-md-none" aria-label="<?php esc_attr_e('Navigazione rapida', AIHL_TEXT_DOMAIN); ?>">
			<a class="aihl-bottom-bar-item" href="<?php echo esc_url(home_url('/')); ?>">
				<i class="fa-solid fa-house" aria-hidden="true"></i>
				<span><?php esc_html_e('Home', AIHL_TEXT_DOMAIN); ?></span>
			</a>
			<button type="button" class="aihl-bottom-bar-item aihl-bottom-bar-search-btn">
				<i class="fa-solid fa-search" aria-hidden="true"></i>
				<span><?php esc_html_e('Cerca', AIHL_TEXT_DOMAIN); ?></span>
			</button>
			<button type="button" class="aihl-bottom-bar-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
				<i class="fa-solid fa-bars" aria-hidden="true"></i>
				<span><?php esc_html_e('Menu', AIHL_TEXT_DOMAIN); ?></span>
			</button>
			<?php if ($cta_label !== '' && $cta_url !== '') : ?>
				<a class="aihl-bottom-bar-item aihl-bottom-bar-cta" href="<?php echo esc_url($cta_url); ?>">
					<i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
					<span><?php echo esc_html($cta_label); ?></span>
				</a>
			<?php endif; ?>
			<?php if ($login_label !== '' && $login_url !== '') : ?>
				<a class="aihl-bottom-bar-item" href="<?php echo esc_url($login_url); ?>">
					<i class="fa-solid fa-user" aria-hidden="true"></i>
					<span><?php echo esc_html($login_label); ?></span>
				</a>
			<?php endif; ?>
		</nav>
		<?php
	}
}
