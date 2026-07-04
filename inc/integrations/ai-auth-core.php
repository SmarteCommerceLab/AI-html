<?php
/**
 * Smart AI Auth — Core motore autenticazione
 *
 * File IDENTICO condiviso tra AI-HTML e Smart Builder Site.
 * Contiene SOLO il motore auth: key store, verifica, REST helpers.
 * La pagina admin e la UI sono gestite dal prodotto host (admin-hub o SBS).
 *
 * Tutte le definizioni sono guardate da function_exists / defined:
 * la prima copia caricata definisce, la seconda riusa.
 *
 * - Store chiavi: option `smart_ai_api_keys` (unico, condiviso).
 * - Header: `X-Smart-AI-Key` (accetta anche `X-SBS-AI-Key` per compat).
 * - Permessi per chiave: read, write, publish.
 *
 * @version 1.1.0
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SMART_AI_AUTH_VERSION')) {
    define('SMART_AI_AUTH_VERSION', '1.1.0');
    define('SMART_AI_KEY_OPTION',  'smart_ai_api_keys');
    define('SMART_AI_KEY_HEADER',  'X-Smart-AI-Key');
}

/* ── Key store ── */

if (!function_exists('smart_ai_get_api_keys')) {
    function smart_ai_get_api_keys(): array {
        $keys = get_option(SMART_AI_KEY_OPTION, array());
        return is_array($keys) ? $keys : array();
    }
}

if (!function_exists('smart_ai_generate_api_key')) {
    function smart_ai_generate_api_key(string $label, array $permissions = array('read', 'write')): array {
        $keys    = smart_ai_get_api_keys();
        $raw_key = 'smart_ai_' . bin2hex(random_bytes(24));
        $entry   = array(
            'id'          => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : md5($raw_key),
            'hash'        => wp_hash_password($raw_key),
            'prefix'      => substr($raw_key, 0, 16) . '...',
            'label'       => sanitize_text_field($label),
            'permissions' => array_values(array_intersect($permissions, array('read', 'write', 'publish'))),
            'created_at'  => current_time('mysql'),
            'last_used'   => '',
        );
        $keys[] = $entry;
        update_option(SMART_AI_KEY_OPTION, $keys, false);

        return array(
            'id'          => $entry['id'],
            'api_key'     => $raw_key,
            'prefix'      => $entry['prefix'],
            'label'       => $entry['label'],
            'permissions' => $entry['permissions'],
        );
    }
}

if (!function_exists('smart_ai_revoke_api_key')) {
    function smart_ai_revoke_api_key(string $key_id): bool {
        $keys     = smart_ai_get_api_keys();
        $filtered = array_filter($keys, function ($k) use ($key_id) {
            return !is_array($k) || ($k['id'] ?? '') !== $key_id;
        });
        if (count($filtered) === count($keys)) {
            return false;
        }
        update_option(SMART_AI_KEY_OPTION, array_values($filtered), false);
        return true;
    }
}

if (!function_exists('smart_ai_verify_api_key')) {
    function smart_ai_verify_api_key(string $raw_key): ?array {
        if ('' === $raw_key || (strpos($raw_key, 'smart_ai_') !== 0 && strpos($raw_key, 'sbs_ai_') !== 0)) {
            return null;
        }
        $keys = smart_ai_get_api_keys();
        foreach ($keys as $index => $entry) {
            if (!is_array($entry) || empty($entry['hash'])) {
                continue;
            }
            if (wp_check_password($raw_key, $entry['hash'])) {
                $keys[$index]['last_used'] = current_time('mysql');
                update_option(SMART_AI_KEY_OPTION, $keys, false);
                return $entry;
            }
        }
        return null;
    }
}

/* ── REST authentication ── */

if (!function_exists('smart_ai_rest_authenticate')) {
    function smart_ai_rest_authenticate(WP_REST_Request $request, string $required_permission = 'read'): bool {
        if (current_user_can('manage_options')) {
            return true;
        }
        $api_key = $request->get_header(SMART_AI_KEY_HEADER);
        if (empty($api_key)) {
            $api_key = $request->get_header('X-SBS-AI-Key'); // compat
        }
        if (empty($api_key)) {
            $api_key = $request->get_param('api_key');
        }
        if (empty($api_key)) {
            return false;
        }
        $entry = smart_ai_verify_api_key((string) $api_key);
        if (!is_array($entry)) {
            return false;
        }
        $permissions = isset($entry['permissions']) && is_array($entry['permissions']) ? $entry['permissions'] : array();
        return in_array($required_permission, $permissions, true);
    }
}

if (!function_exists('smart_ai_can_read')) {
    function smart_ai_can_read(WP_REST_Request $request): bool {
        return smart_ai_rest_authenticate($request, 'read');
    }
}
if (!function_exists('smart_ai_can_write')) {
    function smart_ai_can_write(WP_REST_Request $request): bool {
        return smart_ai_rest_authenticate($request, 'write');
    }
}
if (!function_exists('smart_ai_can_publish')) {
    function smart_ai_can_publish(WP_REST_Request $request): bool {
        return smart_ai_rest_authenticate($request, 'publish');
    }
}

/* ── Render contenuto pagina API Keys (condiviso) ── */

if (!function_exists('smart_ai_render_keys_page_content')) {
    function smart_ai_render_keys_page_content() {
        $keys    = smart_ai_get_api_keys();
        $new_key = get_transient('smart_ai_new_key');
        if ($new_key) {
            delete_transient('smart_ai_new_key');
        }
        $site_url      = home_url();
        $has_sbs       = function_exists('sbs_get_widget_registry');
        $has_theme_api = function_exists('aihl_ai_register_rest_routes');
        ?>

        <div class="smart-dash-stats" style="margin-bottom:24px;">
            <div class="smart-dash-stat-card">
                <div class="smart-dash-stat-icon" style="<?php echo $has_theme_api ? 'background:#16a34a;' : 'background:#94a3b8;'; ?>">
                    <i class="fa-solid fa-palette"></i>
                </div>
                <div class="smart-dash-stat-text">
                    <span class="smart-dash-stat-label">Tema AI-HTML</span>
                    <span class="smart-dash-stat-value"><?php echo $has_theme_api ? 'Attivo' : 'Non attivo'; ?></span>
                </div>
            </div>
            <div class="smart-dash-stat-card">
                <div class="smart-dash-stat-icon" style="<?php echo $has_sbs ? 'background:#16a34a;' : 'background:#94a3b8;'; ?>">
                    <i class="fa-solid fa-cubes"></i>
                </div>
                <div class="smart-dash-stat-text">
                    <span class="smart-dash-stat-label">Smart Builder Site</span>
                    <span class="smart-dash-stat-value"><?php echo $has_sbs ? 'Attivo' : 'Non attivo'; ?></span>
                </div>
            </div>
            <div class="smart-dash-stat-card">
                <div class="smart-dash-stat-icon"><i class="fa-solid fa-key"></i></div>
                <div class="smart-dash-stat-text">
                    <span class="smart-dash-stat-label">Chiavi attive</span>
                    <span class="smart-dash-stat-value"><?php echo count($keys); ?></span>
                </div>
            </div>
        </div>

        <?php if ($has_theme_api) : ?>
            <p style="margin-bottom:6px;font-size:13px;color:#646970;">
                <strong>Endpoint Tema:</strong> <code style="background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;font-size:12px;"><?php echo esc_url($site_url); ?>/wp-json/aihtml/v1/ai/</code>
            </p>
        <?php endif; ?>
        <?php if ($has_sbs) : ?>
            <p style="margin-bottom:20px;font-size:13px;color:#646970;">
                <strong>Endpoint SBS:</strong> <code style="background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;font-size:12px;"><?php echo esc_url($site_url); ?>/wp-json/sbs/v1/ai/</code>
            </p>
        <?php endif; ?>

        <?php if ($new_key) : ?>
            <div style="background:#fffbeb;border:1px solid #f59e0b;border-left:4px solid #f59e0b;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                <p style="margin:0 0 8px;font-weight:600;color:#92400e;">Chiave generata — copiala ora, non viene mostrata di nuovo:</p>
                <code style="font-size:14px;padding:10px 14px;background:#fff;border:1px solid #fbbf24;display:block;border-radius:6px;user-select:all;color:#1d2327;word-break:break-all;"><?php echo esc_html($new_key); ?></code>
                <p style="margin:10px 0 0;font-size:13px;color:#92400e;"><strong>Header:</strong> <code style="background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;">X-Smart-AI-Key: <?php echo esc_html($new_key); ?></code></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['revoked'])) : ?>
            <div style="background:#f0fdf4;border:1px solid #16a34a;border-left:4px solid #16a34a;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;">
                Chiave revocata con successo.
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:20px;">
                <h3 style="font-size:15px;font-weight:600;margin:0 0 16px;color:#1d2327;">Genera nuova chiave</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="smart_ai_generate_key">
                    <?php wp_nonce_field('smart_ai_generate_key'); ?>
                    <p style="margin:0 0 14px;">
                        <label style="font-size:13px;font-weight:600;color:#1d2327;display:block;margin-bottom:4px;">Nome agente</label>
                        <input type="text" name="key_label" value="Claude Agent" class="regular-text" style="border-radius:6px;border:1px solid #dcdcde;padding:8px 12px;width:100%;box-sizing:border-box;">
                    </p>
                    <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#1d2327;">Permessi</p>
                    <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                        <label style="font-size:13px;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="key_permissions[]" value="read" checked> <span style="background:#eff6ff;color:#1e40af;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;">READ</span> Lettura dati e stato</label>
                        <label style="font-size:13px;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="key_permissions[]" value="write" checked> <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;">WRITE</span> Modifica opzioni e contenuti</label>
                        <label style="font-size:13px;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="key_permissions[]" value="publish"> <span style="background:#fef2f2;color:#991b1b;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;">PUBLISH</span> Pubblicazione contenuti</label>
                    </div>
                    <button type="submit" class="button button-primary" style="border-radius:6px;padding:8px 20px;font-size:13px;font-weight:600;">Genera API Key</button>
                </form>
            </div>

            <div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:20px;">
                <h3 style="font-size:15px;font-weight:600;margin:0 0 16px;color:#1d2327;">Chiavi attive (<?php echo count($keys); ?>)</h3>
                <?php if (empty($keys)) : ?>
                    <p style="color:#646970;font-size:13px;margin:0;">Nessuna chiave generata. Crea la prima per connettere un agente AI.</p>
                <?php else : ?>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($keys as $k) : if (!is_array($k)) continue; ?>
                        <div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong style="font-size:13px;color:#1d2327;"><?php echo esc_html($k['label'] ?? ''); ?></strong>
                                <div style="font-size:11px;color:#646970;margin-top:2px;">
                                    <code style="background:rgba(0,0,0,.06);padding:1px 5px;border-radius:3px;font-size:11px;"><?php echo esc_html($k['prefix'] ?? ''); ?></code>
                                    &middot;
                                    <?php
                                    $perms = $k['permissions'] ?? array();
                                    foreach ($perms as $p) {
                                        $colors = array('read' => '#1e40af', 'write' => '#92400e', 'publish' => '#991b1b');
                                        $bgs    = array('read' => '#eff6ff', 'write' => '#fef3c7', 'publish' => '#fef2f2');
                                        printf(
                                            '<span style="background:%s;color:%s;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:600;text-transform:uppercase;margin-left:2px;">%s</span>',
                                            esc_attr($bgs[$p] ?? '#f6f7f7'),
                                            esc_attr($colors[$p] ?? '#1d2327'),
                                            esc_html($p)
                                        );
                                    }
                                    ?>
                                    &middot;
                                    <span><?php echo esc_html($k['last_used'] ? 'Ultimo uso: ' . $k['last_used'] : 'Mai usata'); ?></span>
                                </div>
                            </div>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0;">
                                <input type="hidden" name="action" value="smart_ai_revoke_key">
                                <input type="hidden" name="key_id" value="<?php echo esc_attr($k['id'] ?? ''); ?>">
                                <?php wp_nonce_field('smart_ai_revoke_key'); ?>
                                <button type="submit" class="button button-small" style="border-radius:5px;color:#dc3545;border-color:#dc3545;">Revoca</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top:24px;padding:16px 20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;">
            <h4 style="margin:0 0 8px;font-size:14px;color:#1e40af;">Come usare le API Keys</h4>
            <p style="margin:0;font-size:13px;color:#1e40af;line-height:1.6;">
                Passa la chiave nell'header <code style="background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;">X-Smart-AI-Key</code> di ogni richiesta REST.
                Le chiavi funzionano con tutti gli endpoint Smart attivi sul sito.
            </p>
        </div>
        <?php
    }
}

/* ── Admin post actions (genera/revoca) — registrate una sola volta ── */

if (!defined('SMART_AI_KEYS_ADMIN_LOADED')) {
    define('SMART_AI_KEYS_ADMIN_LOADED', true);

    /**
     * Calcola l'URL di redirect dopo genera/revoca.
     * Se AI-HTML attivo → aihl-api-keys, altrimenti → smart_ai_api_keys standalone.
     */
    function smart_ai_keys_redirect_url(array $args = array()): string {
        $page = defined('AIHL_VERSION') ? 'aihl-api-keys' : 'smart_ai_api_keys';
        return add_query_arg(array_merge(array('page' => $page), $args), admin_url('admin.php'));
    }

    add_action('admin_post_smart_ai_generate_key', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti.');
        }
        check_admin_referer('smart_ai_generate_key');
        $label = isset($_POST['key_label']) ? sanitize_text_field(wp_unslash((string) $_POST['key_label'])) : 'AI Agent';
        $perms = isset($_POST['key_permissions']) && is_array($_POST['key_permissions'])
            ? array_map('sanitize_key', $_POST['key_permissions'])
            : array('read', 'write');
        $result = smart_ai_generate_api_key($label, $perms);
        set_transient('smart_ai_new_key', $result['api_key'], 120);
        wp_safe_redirect(smart_ai_keys_redirect_url(array('created' => '1')));
        exit;
    });

    add_action('admin_post_smart_ai_revoke_key', function () {
        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti.');
        }
        check_admin_referer('smart_ai_revoke_key');
        $key_id = isset($_POST['key_id']) ? sanitize_text_field(wp_unslash((string) $_POST['key_id'])) : '';
        smart_ai_revoke_api_key($key_id);
        wp_safe_redirect(smart_ai_keys_redirect_url(array('revoked' => '1')));
        exit;
    });
}
