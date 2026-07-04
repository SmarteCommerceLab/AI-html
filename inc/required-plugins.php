<?php
/*
* AI-HTML - Plugin dependencies registry and admin checks.
*/
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('aihl_plugin_registry')) {
	function aihl_plugin_registry() {
		return array(
			array(
				'name' => 'Smart Customizer Frameworks',
				'path' => 'smart-customizer-frameworks/smart-customizer-frameworks.php',
				'required' => true,
				'area' => 'Customizer controls',
			),
			array(
				'name' => 'Smart Builder Site',
				'paths' => array(
					'smart-builder-site/smart-builder-site.php',
					'smart-site-builder/smart-site-builder.php',
					'wp-smart-site-builder/wp-smart-site-builder.php',
					'smart-site-builder/wp-smart-site-builder.php',
				),
				'required' => true,
				'area' => 'Logo, social links, category widgets and homepage composition',
			),
			array(
				'name' => 'Smart SEO Dots',
				'path' => 'smart-seo-dots/smart-seo-dots.php',
				'required' => true,
				'area' => 'Breadcrumbs and load-more',
			),
			array(
				'name' => 'Smart Bootstrap Manager',
				'path' => 'smart-bootstrap-manager/smart-bootstrap-manager.php',
				'required' => true,
				'area' => 'Bootstrap pagination helpers',
			),
			array(
				'name' => 'Mailchimp for WordPress',
				'path' => 'mailchimp-for-wp/mailchimp-for-wp.php',
				'required' => false,
				'area' => 'Newsletter form shortcode',
			),
			array(
				'name' => 'Contact Form 7',
				'path' => 'contact-form-7/wp-contact-form-7.php',
				'required' => false,
				'area' => 'Contact form shortcode',
			),
			array(
				'name' => 'Yoast SEO',
				'path' => 'wordpress-seo/wp-seo.php',
				'required' => false,
				'area' => 'Primary category support',
			),
		);
	}
}

if (!function_exists('aihl_is_plugin_active_safe')) {
	function aihl_is_plugin_active_safe($plugin_path) {
		if (!function_exists('is_plugin_active')) {
			$plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
			if (file_exists($plugin_file)) {
				require_once $plugin_file;
			}
		}

		if (function_exists('is_plugin_active') && is_plugin_active($plugin_path)) {
			return true;
		}

		$active_plugins = (array) get_option('active_plugins', array());
		if (in_array($plugin_path, $active_plugins, true)) {
			return true;
		}

		if (is_multisite()) {
			$network_plugins = (array) get_site_option('active_sitewide_plugins', array());
			if (isset($network_plugins[$plugin_path])) {
				return true;
			}
		}

		return false;
	}
}

if (!function_exists('aihl_plugin_paths')) {
	function aihl_plugin_paths($plugin) {
		if (!empty($plugin['paths']) && is_array($plugin['paths'])) {
			return $plugin['paths'];
		}
		if (!empty($plugin['path'])) {
			return array($plugin['path']);
		}
		return array();
	}
}

if (!function_exists('aihl_is_plugin_entry_active')) {
	function aihl_is_plugin_entry_active($plugin) {
		$paths = aihl_plugin_paths($plugin);
		foreach ($paths as $path) {
			if (aihl_is_plugin_active_safe($path)) {
				return true;
			}
		}
		return false;
	}
}

if (!function_exists('aihl_missing_plugins')) {
	function aihl_missing_plugins($required_only = false) {
		$missing = array();

		foreach (aihl_plugin_registry() as $plugin) {
			if ($required_only && empty($plugin['required'])) {
				continue;
			}
			if (!aihl_is_plugin_entry_active($plugin)) {
				$missing[] = $plugin;
			}
		}

		return $missing;
	}
}

if (!function_exists('aihl_missing_plugins_grouped')) {
	function aihl_missing_plugins_grouped() {
		$groups = array(
			'required' => array(),
			'recommended' => array(),
		);

		foreach (aihl_missing_plugins() as $plugin) {
			$group = !empty($plugin['required']) ? 'required' : 'recommended';
			$groups[$group][] = $plugin;
		}

		return $groups;
	}
}

/* Menu page registration moved to inc/admin/admin-hub.php (v1.2.0) */

if (!function_exists('aihl_render_plugins_page')) {
	function aihl_render_plugins_page() {
		if (!current_user_can('manage_options')) {
			return;
		}
		$plugins = aihl_plugin_registry();
		?>
		<div class="wrap">
			<h1><?php echo esc_html(AIHL_THEME_NAME . ' - Plugin Dipendenze'); ?></h1>
			<p><?php esc_html_e('Controlla lo stato dei plugin richiesti e raccomandati dal tema.', AIHL_TEXT_DOMAIN); ?></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Plugin', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Tipo', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Stato', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Path', AIHL_TEXT_DOMAIN); ?></th>
						<th><?php esc_html_e('Uso', AIHL_TEXT_DOMAIN); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($plugins as $plugin) : ?>
					<?php
						$is_active = aihl_is_plugin_entry_active($plugin);
						$paths = aihl_plugin_paths($plugin);
					?>
					<tr>
						<td><?php echo esc_html($plugin['name']); ?></td>
						<td><?php echo !empty($plugin['required']) ? 'Required' : 'Recommended'; ?></td>
						<td>
							<?php if ($is_active) : ?>
								<span style="color:#067a00;font-weight:600;">Active</span>
							<?php else : ?>
								<span style="color:#b32d2e;font-weight:600;">Missing/Inactive</span>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html(implode(' | ', $paths)); ?></code></td>
						<td><?php echo esc_html($plugin['area']); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}

if (!function_exists('aihl_render_plugin_dependency_summary')) {
	function aihl_render_plugin_dependency_summary() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$groups = aihl_missing_plugins_grouped();
	if (empty($groups['required']) && empty($groups['recommended'])) {
		return;
	}
	?>
	<section class="smart-dependency-summary" aria-labelledby="aihl-dependency-summary-title">
		<div class="smart-dependency-summary-heading">
			<div>
				<span class="smart-dependency-summary-kicker"><?php esc_html_e('Configurazione tema', AIHL_TEXT_DOMAIN); ?></span>
				<h2 id="aihl-dependency-summary-title"><?php esc_html_e('Integrazioni da verificare', AIHL_TEXT_DOMAIN); ?></h2>
				<p><?php esc_html_e('Gli avvisi sono raccolti qui per non interrompere il lavoro nelle altre schermate WordPress.', AIHL_TEXT_DOMAIN); ?></p>
			</div>
			<a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=aihl-plugins')); ?>">
				<span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span>
				<?php esc_html_e('Gestisci plugin', AIHL_TEXT_DOMAIN); ?>
			</a>
		</div>

		<div class="smart-dependency-summary-grid">
			<?php if (!empty($groups['required'])) : ?>
				<div class="smart-dependency-card smart-dependency-card-required">
					<div class="smart-dependency-card-title">
						<span class="dashicons dashicons-warning" aria-hidden="true"></span>
						<strong><?php esc_html_e('Plugin richiesti', AIHL_TEXT_DOMAIN); ?></strong>
						<span class="smart-dependency-count"><?php echo esc_html((string) count($groups['required'])); ?></span>
					</div>
					<p><?php esc_html_e('Necessari per garantire tutte le funzioni dichiarate dal tema.', AIHL_TEXT_DOMAIN); ?></p>
					<ul class="smart-dependency-list">
						<?php foreach ($groups['required'] as $plugin) : ?>
							<li><?php echo esc_html($plugin['name']); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if (!empty($groups['recommended'])) : ?>
				<div class="smart-dependency-card smart-dependency-card-recommended">
					<div class="smart-dependency-card-title">
						<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
						<strong><?php esc_html_e('Plugin consigliati', AIHL_TEXT_DOMAIN); ?></strong>
						<span class="smart-dependency-count"><?php echo esc_html((string) count($groups['recommended'])); ?></span>
					</div>
					<p><?php esc_html_e('Opzionali: aggiungono funzionalità specifiche senza bloccare il tema.', AIHL_TEXT_DOMAIN); ?></p>
					<ul class="smart-dependency-list">
						<?php foreach ($groups['recommended'] as $plugin) : ?>
							<li><?php echo esc_html($plugin['name']); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	}
}
