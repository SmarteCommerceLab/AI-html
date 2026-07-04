<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('AIHL_Nav_Menu_Walker')) {
	class AIHL_Nav_Menu_Walker extends Walker_Nav_Menu {
		protected $has_children_map = array();
		protected $child_count_map = array();
		protected $child_nested_map = array();
		protected $rich_parent_stack = array();
		protected $current_rich_parent_id = 0;
		protected $current_rich_panel = '';
		protected $current_rich_layout = 'split';
		protected $current_rich_child_count = 0;
		protected $current_rich_has_nested = false;
		protected $current_rich_cta_label = '';
		protected $current_rich_cta_url = '';
		protected $current_rich_footer = '';

		public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
			if (!$element) {
				return;
			}
			$id_field = $this->db_fields['id'];
			$this->has_children_map[(int) $element->$id_field] = !empty($children_elements[$element->$id_field]);
			$this->child_count_map[(int) $element->$id_field] = !empty($children_elements[$element->$id_field])
				? count((array) $children_elements[$element->$id_field])
				: 0;
			$this->child_nested_map[(int) $element->$id_field] = false;
			if (!empty($children_elements[$element->$id_field])) {
				foreach ((array) $children_elements[$element->$id_field] as $child_element) {
					$child_id = isset($child_element->$id_field) ? (int) $child_element->$id_field : 0;
					if ($child_id > 0 && !empty($children_elements[$child_id])) {
						$this->child_nested_map[(int) $element->$id_field] = true;
						break;
					}
				}
			}
			parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
		}

		public function start_lvl(&$output, $depth = 0, $args = array()) {
			$indent = str_repeat("\t", $depth);

			// Children of top-level item.
			if ($depth === 0 && $this->current_rich_parent_id > 0) {
				$layout = in_array($this->current_rich_layout, array('split', 'compact', 'columns', 'grid', 'tabbed', 'featured', 'showcase', 'directory', 'panel'), true) ? $this->current_rich_layout : 'split';
				$density = $this->current_rich_child_count <= 6 && !$this->current_rich_has_nested && $this->current_rich_panel === '' && $this->current_rich_footer === ''
					? 'compact'
					: 'expanded';
				$dropdown_classes = array(
					'dropdown-menu',
					'aihl-menu-dropdown',
					'aihl-menu-dropdown-rich',
					'aihl-rich-layout-' . $layout,
					'aihl-rich-density-' . $density,
					'aihl-rich-count-' . max(0, (int) $this->current_rich_child_count),
				);
				$output .= "\n{$indent}<div class=\"" . esc_attr(implode(' ', $dropdown_classes)) . "\" role=\"menu\">";
				if ($layout === 'tabbed') {
					$output .= "\n{$indent}\t<div class=\"aihl-menu-tabbed-wrap\">";
					$output .= "\n{$indent}\t\t<ul class=\"aihl-menu-rich-links aihl-menu-tabbed-links list-unstyled mb-0\">";
				} else {
					$output .= "\n{$indent}\t<div class=\"aihl-menu-rich-grid\">";
					$output .= "\n{$indent}\t\t<div class=\"aihl-menu-rich-main\">";
					$output .= "\n{$indent}\t\t\t<ul class=\"aihl-menu-rich-links list-unstyled mb-0\">";
				}
				return;
			}

			$classes = array('dropdown-menu');
			if ($depth > 0) {
				$classes[] = 'sub-menu';
			}
			$output .= "\n{$indent}<ul class=\"" . esc_attr(implode(' ', $classes)) . "\">";
		}

		public function end_lvl(&$output, $depth = 0, $args = array()) {
			$indent = str_repeat("\t", $depth);

			if ($depth === 0 && $this->current_rich_parent_id > 0) {
				$is_tabbed = $this->current_rich_layout === 'tabbed';
				if ($is_tabbed) {
					$output .= "\n{$indent}\t\t</ul>";
					$output .= "\n{$indent}\t</div>";
				} else {
					$output .= "\n{$indent}\t\t\t</ul>";
					if ($this->current_rich_cta_label !== '' && $this->current_rich_cta_url !== '') {
						$output .= "\n{$indent}\t\t\t<div class=\"aihl-menu-rich-cta mt-3 pt-3 border-top\">";
						$output .= '<a class="btn btn-primary btn-sm" href="' . esc_url($this->current_rich_cta_url) . '">' . esc_html($this->current_rich_cta_label) . '</a>';
						$output .= "</div>";
					}
					$output .= "\n{$indent}\t\t</div>";
					if ($this->current_rich_panel !== '') {
						$output .= "\n{$indent}\t\t<aside class=\"aihl-menu-rich-side\">";
						$output .= "\n{$indent}\t\t\t<div class=\"aihl-menu-rich-content\">" . wp_kses_post(wpautop($this->current_rich_panel)) . "</div>";
						$output .= "\n{$indent}\t\t</aside>";
					}
					$output .= "\n{$indent}\t</div>";
				}
				if ($this->current_rich_footer !== '') {
					$output .= "\n{$indent}\t<div class=\"aihl-menu-rich-footer border-top mt-2 pt-2\">";
					$output .= wp_kses_post($this->current_rich_footer);
					$output .= "</div>";
				}
				$output .= "\n{$indent}</div>";
				return;
			}

			$output .= "</ul>\n";
		}

		public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
			$indent = $depth ? str_repeat("\t", $depth) : '';
			$item_id = (int) $item->ID;
			$has_children = !empty($this->has_children_map[$item_id]);
			$classes = empty($item->classes) ? array() : array_filter((array) $item->classes);
			$menu_mode = isset($item->aihl_menu_mode) ? sanitize_key((string) $item->aihl_menu_mode) : '';

			$is_rich_parent = $this->is_rich_parent($item, $classes, $depth, $has_children);
			if ($depth === 0) {
				$this->current_rich_parent_id = $is_rich_parent ? $item_id : 0;
				$this->current_rich_panel = $is_rich_parent
					? (string) (!empty($item->aihl_menu_rich_content) ? $item->aihl_menu_rich_content : (isset($item->description) ? trim($item->description) : ''))
					: '';
				$this->current_rich_layout = $is_rich_parent && !empty($item->aihl_menu_rich_layout)
					? sanitize_key((string) $item->aihl_menu_rich_layout)
					: 'split';
				$this->current_rich_child_count = $is_rich_parent && isset($this->child_count_map[$item_id])
					? (int) $this->child_count_map[$item_id] : 0;
				$this->current_rich_has_nested = $is_rich_parent && !empty($this->child_nested_map[$item_id]);
				$this->current_rich_cta_label = $is_rich_parent && !empty($item->aihl_menu_rich_cta_label)
					? trim((string) $item->aihl_menu_rich_cta_label) : '';
				$this->current_rich_cta_url = $is_rich_parent && !empty($item->aihl_menu_rich_cta_url)
					? esc_url((string) $item->aihl_menu_rich_cta_url) : '';
				$this->current_rich_footer = $is_rich_parent && !empty($item->aihl_menu_rich_footer)
					? (string) $item->aihl_menu_rich_footer : '';
			}

			$rich_context = $this->is_in_rich_context($depth, $item);

			$classes[] = 'menu-item-' . $item_id;
			if ($depth === 0) {
				$classes[] = 'nav-item';
			}
			if ($has_children && $depth === 0) {
				$classes[] = 'dropdown';
			}
			if ($has_children && $depth > 0) {
				$classes[] = 'dropdown-submenu';
			}
			if ($is_rich_parent) {
				$classes[] = 'aihl-menu-rich-parent';
				$this->rich_parent_stack[$item_id] = true;
			}
			if ($is_rich_parent && $menu_mode === 'dropdown') {
				$classes[] = 'aihl-menu-dropdown-parent';
			}

			$output .= $indent . '<li class="' . esc_attr(implode(' ', array_map('sanitize_html_class', array_unique($classes)))) . '">';

			$link_classes = array($depth === 0 ? 'nav-link' : 'dropdown-item');
			$atts = array(
				'title' => !empty($item->attr_title) ? $item->attr_title : '',
				'target' => !empty($item->target) ? $item->target : '',
				'rel' => !empty($item->xfn) ? $item->xfn : '',
				'href' => !empty($item->url) ? $item->url : '',
			);

			if ($has_children) {
				$link_classes[] = 'dropdown-toggle';
				if ($depth === 0) {
					$atts['data-bs-toggle'] = 'dropdown';
				}
				$atts['aria-expanded'] = 'false';
			}
			if ($is_rich_parent) {
				$link_classes[] = 'aihl-menu-rich-trigger';
			}

			$icon = isset($item->aihl_menu_icon) ? trim($item->aihl_menu_icon) : '';
			$badge = isset($item->aihl_menu_badge) ? trim($item->aihl_menu_badge) : '';
			$badge_color = isset($item->aihl_menu_badge_color) ? trim($item->aihl_menu_badge_color) : '';
			$subtitle = isset($item->aihl_menu_subtitle) ? trim($item->aihl_menu_subtitle) : '';
			$eyebrow = isset($item->aihl_menu_eyebrow) ? trim($item->aihl_menu_eyebrow) : '';
			$image = $this->resolve_item_image($item);
			$is_highlight = !empty($item->aihl_menu_highlight) && (string) $item->aihl_menu_highlight === '1';
			$accent_color = isset($item->aihl_menu_color) ? trim($item->aihl_menu_color) : '';
			$item_cta_style = isset($item->aihl_menu_item_cta) ? trim($item->aihl_menu_item_cta) : '';

			if ($is_highlight) {
				$link_classes[] = 'aihl-menu-item-highlight';
			}
			if ($rich_context && $depth === 1) {
				$link_classes[] = 'aihl-menu-rich-item';
			}
			if ($item_cta_style !== '') {
				$link_classes[] = 'btn';
				$link_classes[] = sanitize_html_class($item_cta_style);
				$link_classes[] = 'aihl-menu-item-cta';
			}

			$inline_style = '';
			if ($accent_color !== '') {
				$inline_style .= '--aihl-item-color:' . esc_attr($accent_color) . ';';
				$link_classes[] = 'aihl-menu-has-color';
			}

			$atts['class'] = implode(' ', $link_classes);
			if ($inline_style !== '') {
				$atts['style'] = $inline_style;
			}
			if ($atts['target'] === '_blank') {
				$rels = preg_split('/\s+/', (string) $atts['rel']);
				$rels = is_array($rels) ? $rels : array();
				$rels[] = 'noopener';
				$rels[] = 'noreferrer';
				$atts['rel'] = trim(implode(' ', array_unique(array_filter($rels))));
			}

			$attributes = '';
			foreach ($atts as $attr => $value) {
				if ($value === '') {
					continue;
				}
				$attributes .= ' ' . $attr . '="' . ('href' === $attr ? esc_url($value) : esc_attr($value)) . '"';
			}

			$title = apply_filters('the_title', $item->title, $item_id);
			$label = '<span class="aihl-menu-text">' . esc_html($title) . '</span>';
			if ($depth > 0 && $badge !== '') {
				$badge_style_attr = '';
				if ($badge_color !== '') {
					$badge_style_attr = ' style="background:' . esc_attr($badge_color) . '!important;border-color:' . esc_attr($badge_color) . '!important"';
				}
				$label .= '<span class="aihl-menu-badge badge rounded-pill bg-primary ms-2"' . $badge_style_attr . '>' . esc_html($badge) . '</span>';
			}

			$content = '';
			if ($rich_context && $depth === 1 && $image !== '') {
				$content .= '<span class="aihl-menu-thumb-wrap" aria-hidden="true"><img class="aihl-menu-thumb" src="' . esc_url($image) . '" alt="" loading="lazy" decoding="async"></span>';
			}
			$content .= '<span class="aihl-menu-title-group">';
			if ($depth > 0 && $eyebrow !== '') {
				$content .= '<small class="aihl-menu-eyebrow d-block">' . esc_html($eyebrow) . '</small>';
			}
			if ($icon !== '') {
				$content .= '<i class="' . esc_attr($icon) . ' aihl-menu-icon me-2" aria-hidden="true"></i>';
			}
			$content .= '<span class="aihl-menu-label-wrap">' . $label;
			if ($depth > 0 && $subtitle !== '') {
				$content .= '<small class="aihl-menu-subtitle d-block">' . esc_html($subtitle) . '</small>';
			}
			$content .= '</span></span>';

			$dropdown_indicator = '';
			if ($has_children && $depth === 0) {
				$show_indicator = (bool) (function_exists('aihtml_option_value')
					? aihtml_option_value('menu_dropdown_indicator', true)
					: true);
				if ($show_indicator) {
					$dropdown_indicator = ' <i class="fa-solid fa-chevron-down aihl-menu-dropdown-icon" aria-hidden="true"></i>';
				}
			}

			$item_output = (isset($args->before) ? $args->before : '')
				. '<a' . $attributes . '>'
				. (isset($args->link_before) ? $args->link_before : '')
				. $content
				. $dropdown_indicator
				. (isset($args->link_after) ? $args->link_after : '')
				. '</a>'
				. (isset($args->after) ? $args->after : '');

			$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
		}

		public function end_el(&$output, $item, $depth = 0, $args = array()) {
			$item_id = (int) $item->ID;
			if ($depth === 0) {
				unset($this->rich_parent_stack[$item_id]);
				$this->current_rich_parent_id = 0;
				$this->current_rich_panel = '';
				$this->current_rich_layout = 'split';
				$this->current_rich_child_count = 0;
				$this->current_rich_has_nested = false;
				$this->current_rich_cta_label = '';
				$this->current_rich_cta_url = '';
				$this->current_rich_footer = '';
			}
			$output .= "</li>\n";
		}

		protected function is_rich_parent($item, $classes, $depth, $has_children) {
			if ($depth !== 0 || !$has_children) {
				return false;
			}

			$mode = isset($item->aihl_menu_mode) ? sanitize_key((string) $item->aihl_menu_mode) : '';
			if ($mode === 'simple') {
				return false;
			}
			if ($mode === 'rich') {
				return true;
			}
			if ($mode === 'dropdown') {
				return true;
			}
			if ($mode === '') {
				return true;
			}

			$rich_flags = array('menu-rich', 'menu-mega', 'menu-mega-content');
			foreach ($rich_flags as $flag) {
				if (in_array($flag, $classes, true)) {
					return true;
				}
			}
			return false;
		}

		protected function is_in_rich_context($depth, $item) {
			if ($depth <= 0) {
				return false;
			}
			$parent_id = (int) $item->menu_item_parent;
			if ($parent_id <= 0) {
				return false;
			}
			if (isset($this->rich_parent_stack[$parent_id])) {
				return true;
			}
			$parent_mode = sanitize_key((string) get_post_meta($parent_id, '_aihl_menu_mode', true));
			return $parent_mode === 'rich' || $parent_mode === 'dropdown';
		}

		protected function resolve_item_image($item) {
			$url = isset($item->aihl_menu_image) ? esc_url((string) $item->aihl_menu_image) : '';
			if ($url !== '') {
				return $url;
			}
			$image_id = isset($item->aihl_menu_image_id) ? (int) $item->aihl_menu_image_id : 0;
			if ($image_id > 0) {
				$media_url = wp_get_attachment_image_url($image_id, 'medium');
				if (is_string($media_url) && $media_url !== '') {
					return esc_url($media_url);
				}
			}
			return '';
		}
	}
}

if (!class_exists('AIHL_Mobile_Nav_Menu_Walker')) {
	class AIHL_Mobile_Nav_Menu_Walker extends Walker_Nav_Menu {
		protected $has_children_map = array();
		protected $submenu_ids = array();

		public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
			if (!$element) {
				return;
			}

			$id_field = $this->db_fields['id'];
			$this->has_children_map[(int) $element->$id_field] = !empty($children_elements[$element->$id_field]);
			parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
		}

		public function start_lvl(&$output, $depth = 0, $args = array()) {
			$indent = str_repeat("\t", $depth + 1);
			$submenu_id = isset($this->submenu_ids[$depth]) ? $this->submenu_ids[$depth] : '';
			$id_attribute = $submenu_id !== '' ? ' id="' . esc_attr($submenu_id) . '"' : '';
			$output .= "\n{$indent}<ul class=\"aihl-mobile-submenu list-unstyled\"{$id_attribute} hidden>\n";
		}

		public function end_lvl(&$output, $depth = 0, $args = array()) {
			$indent = str_repeat("\t", $depth + 1);
			$output .= "{$indent}</ul>\n";
		}

		public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
			$indent = str_repeat("\t", $depth);
			$item_id = (int) $item->ID;
			$has_children = !empty($this->has_children_map[$item_id]);
			$submenu_id = $has_children ? 'aihl-mobile-submenu-' . $item_id : '';
			$classes = array(
				'aihl-mobile-menu-item',
				'aihl-mobile-menu-depth-' . (int) $depth,
				'menu-item-' . $item_id,
			);

			if ($has_children) {
				$classes[] = 'has-children';
				$this->submenu_ids[$depth] = $submenu_id;
			}
			if (in_array('current-menu-item', (array) $item->classes, true)) {
				$classes[] = 'current-menu-item';
			}
			if (in_array('current-menu-ancestor', (array) $item->classes, true)) {
				$classes[] = 'current-menu-ancestor';
			}

			$output .= $indent . '<li class="' . esc_attr(implode(' ', $classes)) . '">';
			$output .= '<div class="aihl-mobile-menu-row">';

			$atts = array(
				'class' => 'aihl-mobile-menu-link',
				'href' => !empty($item->url) ? $item->url : '',
				'title' => !empty($item->attr_title) ? $item->attr_title : '',
				'target' => !empty($item->target) ? $item->target : '',
				'rel' => !empty($item->xfn) ? $item->xfn : '',
			);
			if (!empty($item->current)) {
				$atts['aria-current'] = 'page';
			}
			if ($atts['target'] === '_blank') {
				$rels = preg_split('/\s+/', (string) $atts['rel']);
				$rels = is_array($rels) ? $rels : array();
				$rels[] = 'noopener';
				$rels[] = 'noreferrer';
				$atts['rel'] = trim(implode(' ', array_unique(array_filter($rels))));
			}

			$atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);
			$filtered_classes = isset($atts['class'])
				? preg_split('/\s+/', trim((string) $atts['class']))
				: array();
			$filtered_classes = is_array($filtered_classes) ? array_filter($filtered_classes) : array();
			$filtered_classes[] = 'aihl-mobile-menu-link';
			$atts['class'] = implode(' ', array_unique($filtered_classes));

			$attributes = '';
			foreach ($atts as $attribute => $value) {
				if ($value === '' || $value === false) {
					continue;
				}
				$attributes .= ' ' . $attribute . '="' . ('href' === $attribute ? esc_url($value) : esc_attr($value)) . '"';
			}

			$title = apply_filters('the_title', $item->title, $item_id);
			$item_output = (isset($args->before) ? $args->before : '')
				. '<a' . $attributes . '>'
				. (isset($args->link_before) ? $args->link_before : '')
				. '<span class="aihl-mobile-menu-text">' . esc_html($title) . '</span>'
				. (isset($args->link_after) ? $args->link_after : '')
				. '</a>';

			if ($has_children) {
				$toggle_label = sprintf(
					/* translators: %s menu item label. */
					__('Apri il sottomenu %s', AIHL_TEXT_DOMAIN),
					wp_strip_all_tags($title)
				);
				$item_output .= '<button class="aihl-mobile-submenu-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr($submenu_id) . '" aria-label="' . esc_attr($toggle_label) . '">';
				$item_output .= '<i class="fa-solid fa-chevron-down" aria-hidden="true"></i>';
				$item_output .= '</button>';
			}

			$item_output .= (isset($args->after) ? $args->after : '');
			$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
			$output .= '</div>';
		}

		public function end_el(&$output, $item, $depth = 0, $args = array()) {
			unset($this->submenu_ids[$depth]);
			$output .= "</li>\n";
		}
	}
}
