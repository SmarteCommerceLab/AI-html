<?php
/**
 * Theme option bootstrap and compatibility accessors.
 */
if (!defined('WPINC')) {
	exit;
}

class aihl_register_class {
	public static function register(): void {
		foreach (AIHL_OPTION as $option_group) {
			$key = $option_group['option_group'];
			if (false === get_option($key, false)) {
				$defaults = AIHL_OPTION_DEFAULT[$key] ?? array();
				add_option($key, apply_filters($key, $defaults), '', false);
			}
		}
	}

	public static function unistall(): void {
		foreach (AIHL_OPTION as $option_group) {
			delete_option($option_group['option_group']);
		}
	}

	public static function check($field) {
		foreach (AIHL_OPTION as $option_group) {
			$option = get_option($option_group['option_group'], array());
			if (is_array($option) && array_key_exists($field, $option) && $option[$field] !== '') {
				return $option;
			}
		}
		return null;
	}

	public static function check_true($field) {
		$option = self::check($field);
		return is_array($option) && !empty($option[$field]) ? $option : null;
	}

	public static function get_text($field) {
		$option = self::check($field);
		return is_array($option) ? $option[$field] : '';
	}
}

function aihl_migrate_legacy_reset_option(): void {
	if (get_option(AIHL_OPTION_BASE . '_reset', false) !== false) {
		delete_option(AIHL_OPTION_BASE . '_reset');
	}
}

add_action('after_setup_theme', array('aihl_register_class', 'register'));
add_action('after_setup_theme', 'aihl_migrate_legacy_reset_option', 30);
