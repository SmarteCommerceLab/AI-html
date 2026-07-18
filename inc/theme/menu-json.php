<?php
if (!defined('ABSPATH')) {
	exit;
}

/* Menu page registration moved to inc/admin/admin-hub.php (v1.2.0) */

add_action('admin_post_aihl_menu_json_export', function() {
	if (!current_user_can('edit_theme_options')) {
		wp_die(esc_html__('Permessi insufficienti.', AIHL_TEXT_DOMAIN));
	}
	check_admin_referer('aihl_menu_json_export');

	$menu_term_id = isset($_POST['menu_term_id']) ? absint($_POST['menu_term_id']) : 0;
	$payload = aihl_build_menu_json_payload($menu_term_id);
	$json = wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	if (!is_string($json) || $json === '') {
		wp_die(esc_html__('Errore durante la generazione JSON.', AIHL_TEXT_DOMAIN));
	}

	$file_name = 'aihl-menu-export-' . gmdate('Ymd-His') . '.json';
	nocache_headers();
	header('Content-Type: application/json; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $file_name . '"');
	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
});

if (!function_exists('aihl_render_menu_json_page')) {
	function aihl_render_menu_json_page() {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$import_notice = '';
		$import_error = '';
		$sticky_json_input = isset($_POST['menu_json_payload']) ? wp_unslash((string) $_POST['menu_json_payload']) : '';
		if (isset($_POST['aihl_menu_json_import_submit'])) {
			check_admin_referer('aihl_menu_json_import');
			$replace_existing = !empty($_POST['replace_existing']);
			$json_input = $sticky_json_input;

			// File upload ha priorita sul textarea
			if (!empty($_FILES['menu_json_file']['tmp_name']) && is_uploaded_file($_FILES['menu_json_file']['tmp_name'])) {
				$file_content = file_get_contents($_FILES['menu_json_file']['tmp_name']);
				if (is_string($file_content) && $file_content !== '') {
					$json_input = $file_content;
					$sticky_json_input = $file_content;
				}
			}

			$result = aihl_import_menu_json_payload($json_input, $replace_existing);
			if (is_wp_error($result)) {
				$import_error = $result->get_error_message();
			} else {
				$import_notice = sprintf(
					/* translators: 1: menu count, 2: item count, 3: failed item count */
					esc_html__('Import completato. Menu: %1$d, Voci: %2$d, Scartate: %3$d', AIHL_TEXT_DOMAIN),
					(int) $result['menus'],
					(int) $result['items'],
					(int) ($result['failed_items'] ?? 0)
				);
			}
		}

		$menus = wp_get_nav_menus(array('hide_empty' => false));
		$presets = function_exists('aihl_menu_json_presets') ? aihl_menu_json_presets() : array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e('AIHL Menu JSON', AIHL_TEXT_DOMAIN); ?></h1>
			<p><?php esc_html_e('Esporta e importa menu WordPress con tutte le impostazioni AIHL (icon, badge, subtitle, image, rich mode, ecc.).', AIHL_TEXT_DOMAIN); ?></p>
			<style>
				.aihl-json-help code{font-size:12px}
				.aihl-json-help pre{background:#f6f7f7;border:1px solid #dcdcde;padding:12px;overflow:auto;max-height:260px}
				.aihl-json-grid{display:grid;grid-template-columns:minmax(320px,1fr) minmax(320px,1fr);gap:24px;max-width:1200px}
				.aihl-json-grid .button{height:auto;line-height:1.2;padding:8px 10px}
				@media (max-width:980px){.aihl-json-grid{grid-template-columns:1fr}}
			</style>

			<?php if ($import_notice !== '') : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html($import_notice); ?></p></div>
			<?php endif; ?>
			<?php if ($import_error !== '') : ?>
				<div class="notice notice-error"><p><?php echo esc_html($import_error); ?></p></div>
			<?php endif; ?>

			<div class="postbox aihl-json-help" style="padding:16px;max-width:1200px;margin-bottom:18px;">
				<h2><?php esc_html_e('Guida rapida JSON', AIHL_TEXT_DOMAIN); ?></h2>
				<p><?php esc_html_e('Flusso consigliato: 1) Esporta un menu esistente, 2) modifica il JSON, 3) importa con sostituzione attiva.', AIHL_TEXT_DOMAIN); ?></p>
				<table class="widefat striped" style="max-width:100%;">
					<thead><tr><th><?php esc_html_e('Campo', AIHL_TEXT_DOMAIN); ?></th><th><?php esc_html_e('Uso', AIHL_TEXT_DOMAIN); ?></th></tr></thead>
					<tbody>
						<tr><td><code>menus[].name</code></td><td><?php esc_html_e('Nome menu WordPress.', AIHL_TEXT_DOMAIN); ?></td></tr>
						<tr><td><code>items[].parent_id</code></td><td><?php esc_html_e('Gerarchia voci (sottomenu).', AIHL_TEXT_DOMAIN); ?></td></tr>
						<tr><td><code>items[].type/object/object_id</code></td><td><?php esc_html_e('Per voci custom usare custom/custom/0.', AIHL_TEXT_DOMAIN); ?></td></tr>
						<tr><td><code>meta._aihl_menu_mode</code></td><td><?php esc_html_e('simple, dropdown oppure rich sul primo livello.', AIHL_TEXT_DOMAIN); ?></td></tr>
						<tr><td><code>meta._aihl_menu_rich_layout</code></td><td><?php esc_html_e('split, compact, columns, grid, tabbed, directory, panel, featured, showcase.', AIHL_TEXT_DOMAIN); ?></td></tr>
						<tr><td><code>meta._aihl_menu_image</code></td><td><?php esc_html_e('URL immagine esterna assoluta.', AIHL_TEXT_DOMAIN); ?></td></tr>
						<tr><td><code>meta._aihl_menu_icon</code></td><td><?php esc_html_e('Classe Font Awesome.', AIHL_TEXT_DOMAIN); ?></td></tr>
					</tbody>
				</table>
				<details style="margin-top:10px;">
					<summary><strong><?php esc_html_e('Esempio minimo voce rich', AIHL_TEXT_DOMAIN); ?></strong></summary>
					<pre>{
  "title": "Smart CRM",
  "type": "custom",
  "object": "custom",
  "object_id": 0,
  "url": "https://example.com/smart-crm",
  "meta": {
    "_aihl_menu_icon": "fa-solid fa-chart-line",
    "_aihl_menu_badge": "Pro",
    "_aihl_menu_subtitle": "Pipeline e segmentazione avanzata.",
    "_aihl_menu_eyebrow": "Sales",
    "_aihl_menu_image": "https://images.unsplash.com/photo-1551281044-8b6d7f4b8b95?auto=format&fit=crop&w=640&q=80",
    "_aihl_menu_highlight": "1"
  }
}</pre>
				</details>
			</div>

			<div class="aihl-json-grid">
				<div class="postbox" style="padding:16px;">
					<h2><?php esc_html_e('Esporta JSON', AIHL_TEXT_DOMAIN); ?></h2>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('aihl_menu_json_export'); ?>
						<input type="hidden" name="action" value="aihl_menu_json_export">
						<p>
							<label for="aihl-menu-term-id"><strong><?php esc_html_e('Menu da esportare', AIHL_TEXT_DOMAIN); ?></strong></label><br>
							<select id="aihl-menu-term-id" name="menu_term_id" class="regular-text">
								<option value="0"><?php esc_html_e('Tutti i menu', AIHL_TEXT_DOMAIN); ?></option>
								<?php foreach ($menus as $menu) : ?>
									<option value="<?php echo (int) $menu->term_id; ?>"><?php echo esc_html($menu->name); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<button type="submit" class="button button-small button-primary"><?php esc_html_e('Scarica JSON', AIHL_TEXT_DOMAIN); ?></button>
						</p>
					</form>
				</div>

				<div class="postbox" style="padding:16px;">
					<h2><?php esc_html_e('Importa JSON', AIHL_TEXT_DOMAIN); ?></h2>
					<?php if (!empty($presets)) : ?>
					<p>
						<label for="aihl-menu-json-preset"><strong><?php esc_html_e('Preset esempi', AIHL_TEXT_DOMAIN); ?></strong></label><br>
						<select id="aihl-menu-json-preset" class="regular-text">
							<option value=""><?php esc_html_e('Seleziona preset', AIHL_TEXT_DOMAIN); ?></option>
							<?php foreach ($presets as $preset_key => $preset_data) : ?>
								<option value="<?php echo esc_attr($preset_key); ?>"><?php echo esc_html($preset_data['label']); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" id="aihl-load-json-preset" class="button button-small"><?php esc_html_e('Carica preset nel textarea', AIHL_TEXT_DOMAIN); ?></button>
					</p>
					<?php endif; ?>
					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field('aihl_menu_json_import'); ?>
						<p>
							<label for="aihl-menu-json-file"><strong><?php esc_html_e('Carica file .json', AIHL_TEXT_DOMAIN); ?></strong></label><br>
							<input type="file" id="aihl-menu-json-file" name="menu_json_file" accept=".json,application/json">
							<span class="description"><?php esc_html_e('Il file ha priorita sul textarea. Max 2MB.', AIHL_TEXT_DOMAIN); ?></span>
						</p>
						<p>
							<label for="aihl-menu-json-payload"><strong><?php esc_html_e('Oppure incolla JSON', AIHL_TEXT_DOMAIN); ?></strong></label>
							<textarea id="aihl-menu-json-payload" name="menu_json_payload" rows="16" class="large-text code" placeholder="{...}"><?php echo esc_textarea($sticky_json_input); ?></textarea>
						</p>
						<p>
							<label>
								<input type="checkbox" name="replace_existing" value="1">
								<?php esc_html_e('Sostituisci voci menu esistenti quando il menu è già presente', AIHL_TEXT_DOMAIN); ?>
							</label>
						</p>
						<p>
							<button type="submit" name="aihl_menu_json_import_submit" value="1" class="button button-small button-primary"><?php esc_html_e('Importa JSON', AIHL_TEXT_DOMAIN); ?></button>
						</p>
					</form>
				</div>
			</div>
			<?php if (!empty($presets)) : ?>
				<script>
				(function(){
					const presets = <?php echo wp_json_encode($presets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
					const select = document.getElementById('aihl-menu-json-preset');
					const btn = document.getElementById('aihl-load-json-preset');
					const target = document.getElementById('aihl-menu-json-payload');
					if (!select || !btn || !target) return;
					btn.addEventListener('click', function(){
						const key = select.value;
						if (!key || !presets[key] || !presets[key].payload) return;
						target.value = presets[key].payload;
						target.focus();
					});
				})();
				</script>
			<?php endif; ?>
		</div>
		<?php
	}
}

if (!function_exists('aihl_menu_json_presets')) {
	function aihl_menu_json_presets() {
		$simple_payload = array(
			'format' => 'aihl-menu-json',
			'version' => 1,
			'generated_at' => gmdate('c'),
			'site_url' => home_url('/'),
			'locations' => array(),
			'menus' => array(
				array(
					'term_id' => 9901,
					'name' => 'AIHL Preset Simple',
					'slug' => 'aihl-preset-simple',
					'description' => 'Preset menu semplice con sottovoci',
					'items' => array(
						array(
							'id' => 1,
							'parent_id' => 0,
							'menu_order' => 1,
							'title' => 'Company',
							'url' => '#',
							'attr_title' => '',
							'target' => '',
							'xfn' => '',
							'description' => '',
							'classes' => array(),
							'type' => 'custom',
							'object' => 'custom',
							'object_id' => 0,
							'meta' => array(
								'_aihl_menu_mode' => 'simple',
								'_aihl_menu_icon' => 'fa-solid fa-building',
							),
						),
						array('id' => 2,'parent_id' => 1,'menu_order' => 1,'title' => 'About Us','url' => 'https://example.com/about','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
						array('id' => 3,'parent_id' => 1,'menu_order' => 2,'title' => 'Contacts','url' => 'https://example.com/contacts','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
					),
				),
			),
		);

		$rich_payload = array(
			'format' => 'aihl-menu-json',
			'version' => 1,
			'generated_at' => gmdate('c'),
			'site_url' => home_url('/'),
			'locations' => array(),
			'menus' => array(
				array(
					'term_id' => 9902,
					'name' => 'AIHL Preset Rich',
					'slug' => 'aihl-preset-rich',
					'description' => 'Preset rich mega menu con immagini',
					'items' => array(
						array(
							'id' => 101,
							'parent_id' => 0,
							'menu_order' => 1,
							'title' => 'Solutions',
							'url' => '#',
							'attr_title' => '',
							'target' => '',
							'xfn' => '',
							'description' => 'Suite enterprise per CRM e automazioni.',
							'classes' => array(),
							'type' => 'custom',
							'object' => 'custom',
							'object_id' => 0,
							'meta' => array(
								'_aihl_menu_mode' => 'rich',
								'_aihl_menu_rich_layout' => 'split',
								'_aihl_menu_icon' => 'fa-solid fa-layer-group',
								'_aihl_menu_rich_content' => '<h4>Enterprise Suite</h4><p>Gestione contenuti, CRM e AI automation.</p>',
							),
						),
						array(
							'id' => 102,
							'parent_id' => 101,
							'menu_order' => 1,
							'title' => 'Smart CRM',
							'url' => 'https://example.com/smart-crm',
							'attr_title' => '',
							'target' => '',
							'xfn' => '',
							'description' => '',
							'classes' => array(),
							'type' => 'custom',
							'object' => 'custom',
							'object_id' => 0,
							'meta' => array(
								'_aihl_menu_icon' => 'fa-solid fa-chart-line',
								'_aihl_menu_badge' => 'Pro',
								'_aihl_menu_subtitle' => 'Pipeline e segmentazione avanzata.',
								'_aihl_menu_eyebrow' => 'Sales',
								'_aihl_menu_image' => 'https://images.unsplash.com/photo-1551281044-8b6d7f4b8b95?auto=format&fit=crop&w=640&q=80',
								'_aihl_menu_highlight' => '1',
							),
						),
						array(
							'id' => 103,
							'parent_id' => 101,
							'menu_order' => 2,
							'title' => 'AI Automation',
							'url' => 'https://example.com/ai-automation',
							'attr_title' => '',
							'target' => '',
							'xfn' => '',
							'description' => '',
							'classes' => array(),
							'type' => 'custom',
							'object' => 'custom',
							'object_id' => 0,
							'meta' => array(
								'_aihl_menu_icon' => 'fa-solid fa-robot',
								'_aihl_menu_badge' => 'Hot',
								'_aihl_menu_subtitle' => 'Automazioni intelligenti per workflow.',
								'_aihl_menu_eyebrow' => 'Automation',
								'_aihl_menu_image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=640&q=80',
							),
						),
					),
				),
			),
		);

		return array(
			'simple_menu' => array(
				'label' => __('Preset Simple (menu classico)', AIHL_TEXT_DOMAIN),
				'payload' => wp_json_encode($simple_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			),
			'rich_menu' => array(
				'label' => __('Preset Rich (mega menu)', AIHL_TEXT_DOMAIN),
				'payload' => wp_json_encode($rich_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			),
			'rich_menu_6x' => array(
				'label' => __('Preset Rich 6 voci (layout misti)', AIHL_TEXT_DOMAIN),
				'payload' => aihl_get_menu_json_demo_payload(),
			),
			'enterprise_full' => array(
				'label' => __('Preset Enterprise Full (tutti i layout + colori + CTA)', AIHL_TEXT_DOMAIN),
				'payload' => aihl_get_menu_json_enterprise_payload(),
			),
		);
	}
}

if (!function_exists('aihl_get_menu_json_demo_payload')) {
	function aihl_get_menu_json_demo_payload() {
		$payload = array(
			'format' => 'aihl-menu-json',
			'version' => 1,
			'generated_at' => gmdate('c'),
			'site_url' => home_url('/'),
			'locations' => array(),
			'menus' => array(
				array(
					'term_id' => 9903,
					'name' => 'AIHL Preset Rich 6',
					'slug' => 'aihl-preset-rich-6',
					'description' => 'Preset completo con 6 top-level e layout misti',
					'items' => array(
						array('id' => 1001,'parent_id' => 0,'menu_order' => 1,'title' => 'Solutions','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_mode' => 'rich','_aihl_menu_rich_layout' => 'split','_aihl_menu_icon' => 'fa-solid fa-layer-group','_aihl_menu_rich_content' => '<h4>Enterprise Suite</h4><p>CRM, contenuti e automazioni in un unico stack.</p>')),
						array('id' => 1002,'parent_id' => 1001,'menu_order' => 1,'title' => 'Smart CRM','url' => 'https://example.com/smart-crm','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-chart-line','_aihl_menu_badge' => 'Pro','_aihl_menu_subtitle' => 'Pipeline e segmentazione avanzata.','_aihl_menu_eyebrow' => 'Sales','_aihl_menu_image' => 'https://images.unsplash.com/photo-1551281044-8b6d7f4b8b95?auto=format&fit=crop&w=640&q=80','_aihl_menu_highlight' => '1')),
						array('id' => 1003,'parent_id' => 1001,'menu_order' => 2,'title' => 'Editorial Hub','url' => 'https://example.com/editorial-hub','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-newspaper','_aihl_menu_badge' => 'New','_aihl_menu_subtitle' => 'Pianificazione contenuti omnicanale.','_aihl_menu_eyebrow' => 'Content','_aihl_menu_image' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=640&q=80')),
						array('id' => 1101,'parent_id' => 0,'menu_order' => 2,'title' => 'Resources','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_mode' => 'rich','_aihl_menu_rich_layout' => 'compact','_aihl_menu_icon' => 'fa-solid fa-book-open','_aihl_menu_rich_content' => '<h4>Knowledge Center</h4><p>Guide operative, tutorial e riferimenti tecnici.</p>')),
						array('id' => 1102,'parent_id' => 1101,'menu_order' => 1,'title' => 'Documentation','url' => 'https://example.com/docs','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-book','_aihl_menu_subtitle' => 'API, setup e integrazione.','_aihl_menu_eyebrow' => 'Docs','_aihl_menu_image' => 'https://images.unsplash.com/photo-1456324504439-367cee3b3c32?auto=format&fit=crop&w=640&q=80')),
						array('id' => 1201,'parent_id' => 0,'menu_order' => 3,'title' => 'Products','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_mode' => 'rich','_aihl_menu_rich_layout' => 'columns','_aihl_menu_icon' => 'fa-solid fa-boxes-stacked','_aihl_menu_rich_content' => '<h4>Product Catalog</h4><p>Modulo scalabile per team enterprise.</p>')),
						array('id' => 1202,'parent_id' => 1201,'menu_order' => 1,'title' => 'Marketing Cloud','url' => 'https://example.com/marketing','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-bullhorn','_aihl_menu_subtitle' => 'Campagne e analytics.','_aihl_menu_eyebrow' => 'MarTech','_aihl_menu_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=640&q=80')),
						array('id' => 1301,'parent_id' => 0,'menu_order' => 4,'title' => 'Services','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_mode' => 'simple','_aihl_menu_icon' => 'fa-solid fa-headset')),
						array('id' => 1302,'parent_id' => 1301,'menu_order' => 1,'title' => 'Onboarding','url' => 'https://example.com/onboarding','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
						array('id' => 1303,'parent_id' => 1301,'menu_order' => 2,'title' => 'Training','url' => 'https://example.com/training','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
						array('id' => 1401,'parent_id' => 0,'menu_order' => 5,'title' => 'Company','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_mode' => 'simple','_aihl_menu_icon' => 'fa-solid fa-building')),
						array('id' => 1402,'parent_id' => 1401,'menu_order' => 1,'title' => 'About us','url' => 'https://example.com/about','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
						array('id' => 1501,'parent_id' => 0,'menu_order' => 6,'title' => 'Contacts','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_mode' => 'simple','_aihl_menu_icon' => 'fa-solid fa-address-book')),
						array('id' => 1502,'parent_id' => 1501,'menu_order' => 1,'title' => 'Email','url' => 'mailto:info@example.com','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
						array('id' => 1503,'parent_id' => 1501,'menu_order' => 2,'title' => 'WhatsApp','url' => 'https://wa.me/390000000000','attr_title' => '','target' => '_blank','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array()),
					),
				),
			),
		);
		return wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
}

if (!function_exists('aihl_build_menu_json_payload')) {
	function aihl_build_menu_json_payload($menu_term_id = 0) {
		$menu_term_id = (int) $menu_term_id;
		$menus = $menu_term_id > 0 ? array(wp_get_nav_menu_object($menu_term_id)) : wp_get_nav_menus(array('hide_empty' => false));
		$menus = array_filter($menus);

		$payload = array(
			'format' => 'aihl-menu-json',
			'version' => 1,
			'generated_at' => gmdate('c'),
			'site_url' => home_url('/'),
			'locations' => get_nav_menu_locations(),
			'menus' => array(),
		);

		$meta_keys = array(
			'_aihl_menu_mode',
			'_aihl_menu_rich_layout',
			'_aihl_menu_icon',
			'_aihl_menu_badge',
			'_aihl_menu_subtitle',
			'_aihl_menu_eyebrow',
			'_aihl_menu_image',
			'_aihl_menu_image_id',
			'_aihl_menu_highlight',
			'_aihl_menu_color',
			'_aihl_menu_badge_color',
			'_aihl_menu_item_cta',
			'_aihl_menu_rich_content',
			'_aihl_menu_rich_cta_label',
			'_aihl_menu_rich_cta_url',
			'_aihl_menu_rich_footer',
		);

		foreach ($menus as $menu) {
			if (!$menu instanceof WP_Term) {
				continue;
			}
			$items = wp_get_nav_menu_items($menu->term_id, array('post_status' => 'any'));
			$items_payload = array();
			if (is_array($items)) {
				foreach ($items as $item) {
					$item_meta = array();
					foreach ($meta_keys as $meta_key) {
						$item_meta[$meta_key] = get_post_meta((int) $item->ID, $meta_key, true);
					}
					$items_payload[] = array(
						'id' => (int) $item->ID,
						'parent_id' => (int) $item->menu_item_parent,
						'menu_order' => (int) $item->menu_order,
						'title' => (string) $item->title,
						'url' => (string) $item->url,
						'attr_title' => (string) $item->attr_title,
						'target' => (string) $item->target,
						'xfn' => (string) $item->xfn,
						'description' => (string) $item->description,
						'classes' => is_array($item->classes) ? array_values(array_filter($item->classes)) : array(),
						'type' => (string) $item->type,
						'object' => (string) $item->object,
						'object_id' => (int) $item->object_id,
						'meta' => $item_meta,
					);
				}
			}

			$payload['menus'][] = array(
				'term_id' => (int) $menu->term_id,
				'name' => (string) $menu->name,
				'slug' => (string) $menu->slug,
				'description' => (string) $menu->description,
				'items' => $items_payload,
			);
		}

		return $payload;
	}
}

if (!function_exists('aihl_import_menu_json_payload')) {
	function aihl_import_menu_json_payload($json_input, $replace_existing = false) {
		$data = json_decode((string) $json_input, true);
		if (!is_array($data)) {
			return new WP_Error('invalid_json', __('JSON non valido.', AIHL_TEXT_DOMAIN));
		}
		if (!isset($data['menus']) || !is_array($data['menus'])) {
			return new WP_Error('invalid_payload', __('Payload menu non valido.', AIHL_TEXT_DOMAIN));
		}
		if (empty($data['menus'])) {
			if (!$replace_existing) {
				return new WP_Error('invalid_payload', __('Payload menu non valido.', AIHL_TEXT_DOMAIN));
			}

			$existing_menus = wp_get_nav_menus(array('hide_empty' => false));
			if (is_array($existing_menus)) {
				foreach ($existing_menus as $existing_menu) {
					$menu_id = isset($existing_menu->term_id) ? (int) $existing_menu->term_id : 0;
					if ($menu_id > 0) {
						wp_delete_nav_menu($menu_id);
					}
				}
			}
			set_theme_mod('nav_menu_locations', array());

			return array(
				'menus'       => 0,
				'items'       => 0,
				'failed_items' => 0,
			);
		}

		$total_menus = 0;
		$total_items = 0;
		$total_failed_items = 0;
		$menu_slug_to_id = array();
		$payload_id_to_slug = array();
		foreach ($data['menus'] as $menu_data_index => $menu_data_row) {
			$payload_id = isset($menu_data_row['term_id']) ? (int) $menu_data_row['term_id'] : 0;
			$payload_slug = isset($menu_data_row['slug']) ? sanitize_title((string) $menu_data_row['slug']) : '';
			if ($payload_id > 0 && $payload_slug !== '') {
				$payload_id_to_slug[$payload_id] = $payload_slug;
			}
		}

		foreach ($data['menus'] as $menu_data) {
			$name = isset($menu_data['name']) ? sanitize_text_field((string) $menu_data['name']) : '';
			$slug = isset($menu_data['slug']) ? sanitize_title((string) $menu_data['slug']) : '';
			if ($name === '') {
				continue;
			}

			$existing_menu = null;
			if ($slug !== '') {
				$all_menus = wp_get_nav_menus(array('hide_empty' => false));
				foreach ($all_menus as $candidate_menu) {
					if ($candidate_menu instanceof WP_Term && (string) $candidate_menu->slug === $slug) {
						$existing_menu = $candidate_menu;
						break;
					}
				}
			}
			if (!$existing_menu) {
				$existing_menu = wp_get_nav_menu_object($name);
			}
			$menu_id = 0;
			if ($existing_menu instanceof WP_Term) {
				$menu_id = (int) $existing_menu->term_id;
			} else {
				$menu_id = (int) wp_create_nav_menu($name);
			}
			if ($menu_id <= 0) {
				continue;
			}

			if ($replace_existing) {
				$existing_items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
				if (is_array($existing_items)) {
					foreach ($existing_items as $existing_item) {
						wp_delete_post((int) $existing_item->ID, true);
					}
				}
			}

			if ($slug !== '') {
				wp_update_term($menu_id, 'nav_menu', array('slug' => $slug));
			}
			if (!empty($menu_data['description'])) {
				wp_update_term($menu_id, 'nav_menu', array('description' => sanitize_text_field((string) $menu_data['description'])));
			}

			$old_to_new = array();
			$new_item_to_old_parent = array();
			$items = isset($menu_data['items']) && is_array($menu_data['items']) ? $menu_data['items'] : array();
			usort($items, function($a, $b) {
				return ((int) ($a['menu_order'] ?? 0)) <=> ((int) ($b['menu_order'] ?? 0));
			});

			foreach ($items as $item_data) {
				$old_id = isset($item_data['id']) ? (int) $item_data['id'] : 0;
				$old_parent = isset($item_data['parent_id']) ? (int) $item_data['parent_id'] : 0;
				$new_parent = 0;

				$args = array(
					'menu-item-title' => sanitize_text_field((string) ($item_data['title'] ?? '')),
					'menu-item-url' => esc_url_raw((string) ($item_data['url'] ?? '')),
					'menu-item-attr-title' => sanitize_text_field((string) ($item_data['attr_title'] ?? '')),
					'menu-item-target' => sanitize_text_field((string) ($item_data['target'] ?? '')),
					'menu-item-classes' => implode(' ', array_map('sanitize_html_class', (array) ($item_data['classes'] ?? array()))),
					'menu-item-xfn' => sanitize_text_field((string) ($item_data['xfn'] ?? '')),
					'menu-item-description' => sanitize_textarea_field((string) ($item_data['description'] ?? '')),
					'menu-item-parent-id' => $new_parent,
					'menu-item-position' => (int) ($item_data['menu_order'] ?? 0),
					'menu-item-status' => 'publish',
					'menu-item-type' => sanitize_key((string) ($item_data['type'] ?? 'custom')),
					'menu-item-object' => sanitize_key((string) ($item_data['object'] ?? 'custom')),
					'menu-item-object-id' => absint((int) ($item_data['object_id'] ?? 0)),
				);
				if ($args['menu-item-type'] !== 'custom' && $args['menu-item-object-id'] <= 0) {
					$args['menu-item-type'] = 'custom';
					$args['menu-item-object'] = 'custom';
				}

				$new_item_id = wp_update_nav_menu_item($menu_id, 0, $args);
				if (is_wp_error($new_item_id) || !$new_item_id) {
					$total_failed_items++;
					continue;
				}
				$new_item_id = (int) $new_item_id;
				$total_items++;

				if ($old_id > 0) {
					$old_to_new[$old_id] = $new_item_id;
				}
				$new_item_to_old_parent[$new_item_id] = $old_parent;

				if (!empty($item_data['meta']) && is_array($item_data['meta'])) {
					foreach ($item_data['meta'] as $meta_key => $meta_value) {
						$meta_key = sanitize_key((string) $meta_key);
						if (strpos($meta_key, '_aihl_menu_') !== 0) {
							continue;
						}
						if (is_array($meta_value)) {
							$meta_value = '';
						}
						$meta_value = (string) $meta_value;
						if ($meta_key === '_aihl_menu_rich_content' || $meta_key === '_aihl_menu_rich_footer') {
							$meta_value = wp_kses_post($meta_value);
						} elseif ($meta_key === '_aihl_menu_image' || $meta_key === '_aihl_menu_rich_cta_url') {
							$meta_value = esc_url_raw($meta_value);
						} elseif ($meta_key === '_aihl_menu_image_id') {
							$meta_value = (string) absint($meta_value);
						} elseif ($meta_key === '_aihl_menu_color' || $meta_key === '_aihl_menu_badge_color') {
							$meta_value = sanitize_hex_color($meta_value);
							if ($meta_value === null) {
								$meta_value = '';
							}
						} else {
							$meta_value = sanitize_text_field($meta_value);
						}
						if ($meta_value === '') {
							delete_post_meta($new_item_id, $meta_key);
						} else {
							update_post_meta($new_item_id, $meta_key, $meta_value);
						}
					}
				}
			}

			// Second pass: relink parent-child hierarchy after all item IDs are known.
			foreach ($new_item_to_old_parent as $new_item_id => $old_parent_id) {
				$new_item_id = (int) $new_item_id;
				$old_parent_id = (int) $old_parent_id;
				if ($new_item_id <= 0 || $old_parent_id <= 0 || empty($old_to_new[$old_parent_id])) {
					continue;
				}
				$new_parent_id = (int) $old_to_new[$old_parent_id];
				update_post_meta($new_item_id, '_menu_item_menu_item_parent', (string) $new_parent_id);
			}

			$total_menus++;
			$menu_slug_to_id[$slug] = $menu_id;
		}

		if ((empty($data['locations']) || !is_array($data['locations'])) && !empty($menu_slug_to_id)) {
			$registered_locations = get_registered_nav_menus();
			if (isset($registered_locations['topic'])) {
				$current_locations = get_nav_menu_locations();
				$first_menu_id = (int) reset($menu_slug_to_id);
				if ($first_menu_id > 0) {
					$current_locations['topic'] = $first_menu_id;
					set_theme_mod('nav_menu_locations', $current_locations);
				}
			}
		}

		if (!empty($data['locations']) && is_array($data['locations'])) {
			$current_locations = get_nav_menu_locations();
			foreach ($data['locations'] as $location_key => $old_menu_id) {
				$location_key = sanitize_key((string) $location_key);
				$old_menu_id = (int) $old_menu_id;
				if ($location_key === '' || $old_menu_id <= 0 || empty($payload_id_to_slug[$old_menu_id])) {
					continue;
				}
				$old_slug = $payload_id_to_slug[$old_menu_id];
				if (!empty($menu_slug_to_id[$old_slug])) {
					$current_locations[$location_key] = (int) $menu_slug_to_id[$old_slug];
				}
			}
			set_theme_mod('nav_menu_locations', $current_locations);
		}

		if ($total_menus === 0) {
			return new WP_Error('no_menu_imported', __('Nessun menu importato.', AIHL_TEXT_DOMAIN));
		}
		if ($total_items === 0) {
			return new WP_Error('no_items_imported', __('Nessuna voce menu importata. Controlla type/object/object_id nel JSON.', AIHL_TEXT_DOMAIN));
		}

		return array(
			'menus' => $total_menus,
			'items' => $total_items,
			'failed_items' => $total_failed_items,
		);
	}
}

if (!function_exists('aihl_get_menu_json_enterprise_payload')) {
	function aihl_get_menu_json_enterprise_payload() {
		$img = 'https://images.unsplash.com/photo-';
		$payload = array(
			'format' => 'aihl-menu-json',
			'version' => 1,
			'generated_at' => gmdate('c'),
			'site_url' => home_url('/'),
			'locations' => array(),
			'menus' => array(
				array(
					'term_id' => 9910,
					'name' => 'AIHL Enterprise Full',
					'slug' => 'aihl-enterprise-full',
					'description' => 'Menu enterprise con tutti i 7 layout, colori, badge, CTA items',
					'items' => array(
						// ── 1. SPLIT (con CTA e footer nel mega)
						array('id' => 2001,'parent_id' => 0,'menu_order' => 1,'title' => 'Solutions','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_mode' => 'rich',
							'_aihl_menu_rich_layout' => 'split',
							'_aihl_menu_icon' => 'fa-solid fa-layer-group',
							'_aihl_menu_rich_content' => '<h4>Enterprise Suite</h4><p>CRM, contenuti e automazioni in un unico stack.</p><p><a href="#" class="btn btn-sm btn-outline-primary">Scopri di piu</a></p>',
							'_aihl_menu_rich_cta_label' => 'Vedi tutte le soluzioni →',
							'_aihl_menu_rich_cta_url' => 'https://example.com/solutions',
							'_aihl_menu_rich_footer' => '<span class="me-3"><i class="fa-solid fa-phone me-1"></i> +39 02 1234567</span><span><i class="fa-solid fa-envelope me-1"></i> info@example.com</span>',
						)),
						array('id' => 2002,'parent_id' => 2001,'menu_order' => 1,'title' => 'Smart CRM','url' => 'https://example.com/crm','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-chart-line',
							'_aihl_menu_badge' => 'Pro',
							'_aihl_menu_badge_color' => '#6f42c1',
							'_aihl_menu_subtitle' => 'Pipeline e segmentazione avanzata per team commerciali.',
							'_aihl_menu_eyebrow' => 'Sales',
							'_aihl_menu_image' => $img . '1551281044-8b6d7f4b8b95?auto=format&fit=crop&w=640&q=80',
							'_aihl_menu_color' => '#6f42c1',
							'_aihl_menu_highlight' => '1',
						)),
						array('id' => 2003,'parent_id' => 2001,'menu_order' => 2,'title' => 'AI Automation','url' => 'https://example.com/automation','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-robot',
							'_aihl_menu_badge' => 'New',
							'_aihl_menu_badge_color' => '#198754',
							'_aihl_menu_subtitle' => 'Workflow intelligenti e automazioni no-code.',
							'_aihl_menu_eyebrow' => 'Automation',
							'_aihl_menu_image' => $img . '1677442136019-21780ecad995?auto=format&fit=crop&w=640&q=80',
							'_aihl_menu_color' => '#198754',
						)),
						array('id' => 2004,'parent_id' => 2001,'menu_order' => 3,'title' => 'Editorial Hub','url' => 'https://example.com/editorial','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-newspaper',
							'_aihl_menu_subtitle' => 'Pianificazione contenuti omnicanale.',
							'_aihl_menu_eyebrow' => 'Content',
							'_aihl_menu_image' => $img . '1455390582262-044cdead277a?auto=format&fit=crop&w=640&q=80',
							'_aihl_menu_color' => '#0dcaf0',
						)),

						// ── 2. FEATURED (card con immagini grandi)
						array('id' => 2101,'parent_id' => 0,'menu_order' => 2,'title' => 'Products','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_mode' => 'rich',
							'_aihl_menu_rich_layout' => 'featured',
							'_aihl_menu_icon' => 'fa-solid fa-boxes-stacked',
						)),
						array('id' => 2102,'parent_id' => 2101,'menu_order' => 1,'title' => 'Marketing Cloud','url' => 'https://example.com/marketing','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-bullhorn',
							'_aihl_menu_badge' => 'Hot',
							'_aihl_menu_badge_color' => '#dc3545',
							'_aihl_menu_subtitle' => 'Campagne multicanale e analytics in tempo reale.',
							'_aihl_menu_eyebrow' => 'MarTech',
							'_aihl_menu_image' => $img . '1460925895917-afdab827c52f?auto=format&fit=crop&w=640&q=80',
							'_aihl_menu_color' => '#dc3545',
						)),
						array('id' => 2103,'parent_id' => 2101,'menu_order' => 2,'title' => 'Data Platform','url' => 'https://example.com/data','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-database',
							'_aihl_menu_subtitle' => 'Warehouse dati unificato con AI insights.',
							'_aihl_menu_eyebrow' => 'Data',
							'_aihl_menu_image' => $img . '1558494949-ef010cbdcc31?auto=format&fit=crop&w=640&q=80',
							'_aihl_menu_color' => '#0d6efd',
						)),
						array('id' => 2104,'parent_id' => 2101,'menu_order' => 3,'title' => 'Commerce Engine','url' => 'https://example.com/commerce','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-cart-shopping',
							'_aihl_menu_badge' => 'Beta',
							'_aihl_menu_badge_color' => '#fd7e14',
							'_aihl_menu_subtitle' => 'Checkout, catalogo e integrazioni payment.',
							'_aihl_menu_eyebrow' => 'eCommerce',
							'_aihl_menu_image' => $img . '1556742049-0cfed4f6a45d?auto=format&fit=crop&w=640&q=80',
							'_aihl_menu_color' => '#fd7e14',
						)),

						// ── 3. SHOWCASE (hero card con bg image)
						array('id' => 2201,'parent_id' => 0,'menu_order' => 3,'title' => 'Case Studies','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_mode' => 'rich',
							'_aihl_menu_rich_layout' => 'showcase',
							'_aihl_menu_icon' => 'fa-solid fa-trophy',
						)),
						array('id' => 2202,'parent_id' => 2201,'menu_order' => 1,'title' => 'Deloitte Digital','url' => 'https://example.com/case/deloitte','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-building',
							'_aihl_menu_badge' => 'Enterprise',
							'_aihl_menu_subtitle' => '+340% lead generation in 6 mesi.',
							'_aihl_menu_eyebrow' => 'Consulting',
							'_aihl_menu_image' => $img . '1486406146926-c627a92ad1ab?auto=format&fit=crop&w=640&q=80',
						)),
						array('id' => 2203,'parent_id' => 2201,'menu_order' => 2,'title' => 'Nike EMEA','url' => 'https://example.com/case/nike','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-shoe-prints',
							'_aihl_menu_badge' => 'Retail',
							'_aihl_menu_subtitle' => 'Omnichannel experience per 12 mercati.',
							'_aihl_menu_eyebrow' => 'DTC',
							'_aihl_menu_image' => $img . '1542291026-7eec264c27ff?auto=format&fit=crop&w=640&q=80',
						)),

						// ── 4. GRID (card 3 colonne con colori)
						array('id' => 2301,'parent_id' => 0,'menu_order' => 4,'title' => 'Resources','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_mode' => 'rich',
							'_aihl_menu_rich_layout' => 'grid',
							'_aihl_menu_icon' => 'fa-solid fa-book-open',
							'_aihl_menu_rich_cta_label' => 'Vai al Knowledge Center →',
							'_aihl_menu_rich_cta_url' => 'https://example.com/resources',
						)),
						array('id' => 2302,'parent_id' => 2301,'menu_order' => 1,'title' => 'Documentation','url' => 'https://example.com/docs','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-book',
							'_aihl_menu_subtitle' => 'API reference e guide tecniche.',
							'_aihl_menu_color' => '#0d6efd',
						)),
						array('id' => 2303,'parent_id' => 2301,'menu_order' => 2,'title' => 'Academy','url' => 'https://example.com/academy','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-graduation-cap',
							'_aihl_menu_subtitle' => 'Corsi e certificazioni online.',
							'_aihl_menu_color' => '#198754',
						)),
						array('id' => 2304,'parent_id' => 2301,'menu_order' => 3,'title' => 'Community','url' => 'https://example.com/community','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-users',
							'_aihl_menu_subtitle' => 'Forum, eventi e networking.',
							'_aihl_menu_color' => '#6f42c1',
						)),
						array('id' => 2305,'parent_id' => 2301,'menu_order' => 4,'title' => 'Blog','url' => 'https://example.com/blog','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-pen-nib',
							'_aihl_menu_subtitle' => 'Articoli, insight e best practice.',
							'_aihl_menu_color' => '#dc3545',
						)),
						array('id' => 2306,'parent_id' => 2301,'menu_order' => 5,'title' => 'Webinar','url' => 'https://example.com/webinar','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-video',
							'_aihl_menu_subtitle' => 'Sessioni live e on-demand.',
							'_aihl_menu_color' => '#fd7e14',
						)),
						array('id' => 2307,'parent_id' => 2301,'menu_order' => 6,'title' => 'Status','url' => 'https://example.com/status','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_icon' => 'fa-solid fa-signal',
							'_aihl_menu_badge' => '99.9%',
							'_aihl_menu_badge_color' => '#198754',
							'_aihl_menu_subtitle' => 'Uptime e stato servizi.',
							'_aihl_menu_color' => '#20c997',
						)),

						// ── 5. SIMPLE con dropdown classico
						array('id' => 2401,'parent_id' => 0,'menu_order' => 5,'title' => 'Company','url' => '#','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_mode' => 'simple',
							'_aihl_menu_icon' => 'fa-solid fa-building',
						)),
						array('id' => 2402,'parent_id' => 2401,'menu_order' => 1,'title' => 'About Us','url' => 'https://example.com/about','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-info-circle')),
						array('id' => 2403,'parent_id' => 2401,'menu_order' => 2,'title' => 'Careers','url' => 'https://example.com/careers','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-briefcase','_aihl_menu_badge' => '5 posizioni','_aihl_menu_badge_color' => '#0dcaf0')),
						array('id' => 2404,'parent_id' => 2401,'menu_order' => 3,'title' => 'Press','url' => 'https://example.com/press','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array('_aihl_menu_icon' => 'fa-solid fa-newspaper')),

						// ── 6. Voce CTA come bottone (senza figli)
						array('id' => 2501,'parent_id' => 0,'menu_order' => 6,'title' => 'Prenota una demo','url' => 'https://example.com/demo','attr_title' => '','target' => '','xfn' => '','description' => '','classes' => array(),'type' => 'custom','object' => 'custom','object_id' => 0,'meta' => array(
							'_aihl_menu_mode' => 'simple',
							'_aihl_menu_item_cta' => 'btn-primary',
							'_aihl_menu_icon' => 'fa-solid fa-paper-plane',
						)),
					),
				),
			),
		);
		return wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
}
