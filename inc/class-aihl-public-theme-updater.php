<?php
if (!defined('ABSPATH')) {
	exit;
}

class AIHL_Public_Theme_Updater {
	const CACHE_SUCCESS_SECONDS = 900;
	const CACHE_FAILURE_SECONDS = 900;

	private $theme_slug;
	private $product_slug;
	private $version;
	private $endpoint;

	public static function register(array $args) {
		$instance = new self($args);
		$instance->hooks();
		return $instance;
	}

	private function __construct(array $args) {
		$this->theme_slug = $this->sanitize_theme_slug((string) ($args['theme_slug'] ?? get_template()));
		$this->product_slug = sanitize_key((string) ($args['product_slug'] ?? $this->theme_slug));
		$this->version = (string) ($args['version'] ?? '');
		$this->endpoint = esc_url_raw((string) ($args['endpoint'] ?? ''));
	}

	private function hooks() {
		if ($this->theme_slug === '' || $this->product_slug === '' || $this->version === '' || $this->endpoint === '') {
			return;
		}

		add_filter('pre_set_site_transient_update_themes', array($this, 'filter_updates'));
		add_filter('site_transient_update_themes', array($this, 'filter_updates'));
		add_filter('themes_api', array($this, 'filter_theme_info'), 10, 3);
		add_filter('upgrader_source_selection', array($this, 'fix_install_directory'), 10, 4);
		add_filter('theme_action_links_' . $this->theme_slug, array($this, 'add_action_links'));
		add_action('admin_post_aihl_check_updates', array($this, 'handle_manual_update_check'));
		add_action('admin_notices', array($this, 'render_manual_update_notice'));
	}

	public function add_action_links($links) {
		if (!current_user_can('update_themes')) {
			return $links;
		}
		$url = wp_nonce_url(admin_url('admin-post.php?action=aihl_check_updates'), 'aihl_check_updates');
		$links['aihl_check_updates'] = '<a href="' . esc_url($url) . '">' . esc_html__('Controlla aggiornamenti', AIHL_TEXT_DOMAIN) . '</a>';
		return $links;
	}

	public function handle_manual_update_check() {
		if (!current_user_can('update_themes')) {
			wp_die(esc_html__('Non hai i permessi per controllare gli aggiornamenti del tema.', AIHL_TEXT_DOMAIN));
		}
		check_admin_referer('aihl_check_updates');
		delete_site_transient($this->cache_key());
		delete_site_transient('update_themes');
		wp_update_themes();
		wp_safe_redirect(add_query_arg('smart_update_checked', $this->product_slug, self_admin_url('themes.php')));
		exit;
	}

	public function render_manual_update_notice() {
		$checked = isset($_GET['smart_update_checked']) ? sanitize_key(wp_unslash($_GET['smart_update_checked'])) : '';
		if ($checked !== $this->product_slug || !current_user_can('update_themes')) {
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Controllo aggiornamenti tema completato tramite Smart Repository.', AIHL_TEXT_DOMAIN) . '</p></div>';
	}

	public function filter_updates($transient) {
		if (!is_object($transient) || !isset($transient->checked)) {
			return $transient;
		}
		if (is_array($transient->checked) && !isset($transient->checked[$this->theme_slug])) {
			return $transient;
		}

		$release = $this->get_release();
		if (!$this->is_update_available($release)) {
			return $this->clear_update_response($transient);
		}

		if (!isset($transient->response) || !is_array($transient->response)) {
			$transient->response = array();
		}
		if (isset($transient->no_update) && is_array($transient->no_update)) {
			unset($transient->no_update[$this->theme_slug]);
		}

		$transient->response[$this->theme_slug] = array(
			'theme' => $this->theme_slug,
			'new_version' => (string) $release['version'],
			'url' => (string) ($release['homepage'] ?? ''),
			'package' => (string) ($release['download_url'] ?? ''),
			'tested' => (string) ($release['tested'] ?? ''),
			'requires' => (string) ($release['requires'] ?? ''),
			'requires_php' => (string) ($release['requires_php'] ?? ''),
		);

		return $transient;
	}

	private function clear_update_response($transient) {
		if (isset($transient->response) && is_array($transient->response)) {
			unset($transient->response[$this->theme_slug]);
		}

		return $transient;
	}

	public function filter_theme_info($result, $action, $args) {
		if ($action !== 'theme_information' || empty($args->slug) || !in_array(strtolower((string) $args->slug), array(strtolower($this->theme_slug), $this->product_slug), true)) {
			return $result;
		}

		$release = $this->get_release();
		if (!$release) {
			return $result;
		}

		return (object) array(
			'name' => (string) ($release['name'] ?? AIHL_THEME_NAME),
			'slug' => $this->theme_slug,
			'version' => (string) ($release['version'] ?? $this->version),
			'author' => (string) ($release['author'] ?? 'Smart eCommerce'),
			'homepage' => (string) ($release['homepage'] ?? ''),
			'requires' => (string) ($release['requires'] ?? ''),
			'tested' => (string) ($release['tested'] ?? ''),
			'requires_php' => (string) ($release['requires_php'] ?? ''),
			'download_link' => (string) ($release['download_url'] ?? ''),
			'sections' => array(
				'description' => (string) ($release['description'] ?? ''),
				'changelog' => (string) ($release['changelog'] ?? ''),
			),
			'banners' => $release['banners'] ?? array(),
			'icons' => $release['icons'] ?? array(),
		);
	}

	public function fix_install_directory($source, $remote_source, $upgrader, $hook_extra) {
		global $wp_filesystem;

		if (empty($hook_extra['theme']) || $hook_extra['theme'] !== $this->theme_slug) {
			return $source;
		}

		if (!$wp_filesystem || !is_string($source) || !is_string($remote_source)) {
			return $source;
		}

		$expected = trailingslashit($remote_source) . $this->theme_slug;
		if (untrailingslashit($source) === untrailingslashit($expected)) {
			return $source;
		}

		if ($wp_filesystem->exists($expected)) {
			$wp_filesystem->delete($expected, true);
		}

		if ($wp_filesystem->move($source, $expected, true)) {
			return $expected;
		}

		return $source;
	}

	private function get_release() {
		$cache_key = $this->cache_key();
		$cached = get_site_transient($cache_key);
		if (is_array($cached) && $this->is_update_available($cached)) {
			return $cached;
		}

		$url = add_query_arg(array(
			'slug' => $this->product_slug,
			'theme' => $this->theme_slug,
			'version' => $this->version,
		), $this->endpoint);

		$response = wp_remote_get($url, array(
			'timeout' => 15,
			'headers' => array(
				'Accept' => 'application/json',
			),
		));

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			set_site_transient($cache_key, array(), self::CACHE_FAILURE_SECONDS);
			return array();
		}

		$data = json_decode(wp_remote_retrieve_body($response), true);
		if (!is_array($data) || empty($data['version']) || empty($data['download_url'])) {
			set_site_transient($cache_key, array(), self::CACHE_FAILURE_SECONDS);
			return array();
		}

		$release = $this->sanitize_release($data);
		if (!$release) {
			set_site_transient($cache_key, array(), self::CACHE_FAILURE_SECONDS);
			return array();
		}

		set_site_transient($cache_key, $release, self::CACHE_SUCCESS_SECONDS);
		return $release;
	}

	private function cache_key() {
		return 'aihl_public_update_' . md5($this->endpoint);
	}

	private function sanitize_release(array $data) {
		$version = sanitize_text_field((string) ($data['version'] ?? $data['new_version'] ?? ''));
		$download_url = esc_url_raw((string) ($data['download_url'] ?? $data['package'] ?? ''));

		if ($version === '' || $download_url === '') {
			return array();
		}

		return array(
			'name' => sanitize_text_field((string) ($data['name'] ?? AIHL_THEME_NAME)),
			'slug' => sanitize_key((string) ($data['slug'] ?? $this->product_slug)),
			'version' => $version,
			'download_url' => $download_url,
			'homepage' => esc_url_raw((string) ($data['homepage'] ?? $data['url'] ?? '')),
			'requires' => sanitize_text_field((string) ($data['requires'] ?? '')),
			'tested' => sanitize_text_field((string) ($data['tested'] ?? '')),
			'requires_php' => sanitize_text_field((string) ($data['requires_php'] ?? '')),
			'author' => wp_kses_post((string) ($data['author'] ?? 'Smart eCommerce')),
			'description' => wp_kses_post((string) ($data['description'] ?? '')),
			'changelog' => wp_kses_post((string) ($data['changelog'] ?? '')),
			'banners' => $this->sanitize_asset_map($data['banners'] ?? array()),
			'icons' => $this->sanitize_asset_map($data['icons'] ?? array()),
		);
	}

	private function sanitize_asset_map($assets) {
		if (!is_array($assets)) {
			return array();
		}

		$sanitized = array();
		foreach ($assets as $key => $url) {
			$key = sanitize_key((string) $key);
			$url = esc_url_raw((string) $url);
			if ($key !== '' && $url !== '') {
				$sanitized[$key] = $url;
			}
		}

		return $sanitized;
	}

	private function sanitize_theme_slug($slug) {
		$slug = trim((string) $slug);
		$slug = preg_replace('/[^A-Za-z0-9_-]/', '', $slug);
		return is_string($slug) ? $slug : '';
	}

	private function is_update_available(array $release) {
		if (empty($release['version']) || empty($release['download_url'])) {
			return false;
		}

		return version_compare($this->version, (string) $release['version'], '<');
	}
}
