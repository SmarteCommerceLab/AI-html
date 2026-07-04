<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset');?>">
    <?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('head_start'); } ?>
    <meta name="viewport"     content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <?php if (get_site_icon_url()) : ?>
        <link rel="icon" href="<?php echo esc_url(get_site_icon_url()); ?>"/>
    <?php endif; ?>

    <?php
    if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('head_end'); }
    wp_head();
    ?>
</head>

<body <?php body_class();?>><?php wp_body_open(); ?>
    <a class="visually-hidden-focusable" href="#main"><?php esc_html_e('Vai al contenuto principale', AIHL_TEXT_DOMAIN); ?></a>
    <?php
    $aihl_slot_context = array(
        'theme' => 'ai-html',
        'screen' => 'header',
        'entity_id' => (int) get_queried_object_id(),
    );
    if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('before_header'); }

    // Mobile nav variables — defined before code-slot override so rail/bottom-bar always render
    $aihl_mobile_navigation = aihl_get_mobile_navigation_config();
    $aihl_mobile_rail_position = $aihl_mobile_navigation['rail_position'];
    $aihl_offcanvas_position = $aihl_mobile_navigation['offcanvas_class'];
    $aihl_mobile_rail_autohide = $aihl_mobile_navigation['rail_autohide'];
    $aihl_mobile_nav_style = $aihl_mobile_navigation['style'];
    $aihl_contact_phone = $aihl_mobile_navigation['phone'];

    $aihl_has_header_override = function_exists('aihl_should_render_canvas_structure')
        ? aihl_should_render_canvas_structure('header')
        : (function_exists('aihl_code_slot_has_override') && aihl_code_slot_has_override('header_full'));

    if ($aihl_has_header_override) :
        // ── Header Full Override: l'AI sostituisce l'intero header nativo ──
        aihl_render_code_slot('header_full');
    else :
    // ── Header Nativo ──
    do_action('sdc/smart-builder-site/slot/slot.global.notifications', $aihl_slot_context);
    do_action('sdc/smart-builder-site/slot/slot.header.actions', $aihl_slot_context);
    do_action('sdc/smart-builder-site/slot/slot.header.secondary', $aihl_slot_context);
    do_action('sdc/smart-site-builder/slot/slot.header.actions', $aihl_slot_context);
    do_action('sdc/smart-site-builder/slot/slot.header.secondary', $aihl_slot_context);

    $aihl_header_overlay_mode = (string) aihtml_option_value('header_overlay_mode', 'auto');
    if (!in_array($aihl_header_overlay_mode, array('auto', 'always', 'never'), true)) {
        $aihl_header_overlay_mode = 'auto';
    }
    $aihl_header_overlay_opacity = (float) aihtml_option_value('header_overlay_opacity', '0.18');
    if ($aihl_header_overlay_opacity < 0) {
        $aihl_header_overlay_opacity = 0.18;
    }
    if ($aihl_header_overlay_opacity > 1) {
        $aihl_header_overlay_opacity = 1;
    }
    $aihl_header_overlay_blur = (int) aihtml_option_value('header_overlay_blur', '8');
    if ($aihl_header_overlay_blur < 0) {
        $aihl_header_overlay_blur = 8;
    }
    if ($aihl_header_overlay_blur > 24) {
        $aihl_header_overlay_blur = 24;
    }
    $aihl_header_structure = (string) aihtml_option_value('header_structure', 'standard');
    if (!in_array($aihl_header_structure, array('standard', 'dualbar', 'centered', 'topbar-nav', 'mega-centered', 'sidebar', 'triple-row', 'stacked-centered'), true)) {
        $aihl_header_structure = 'standard';
    }
    $aihl_header_nav_layout = (string) aihtml_option_value('header_nav_layout', 'clean');
    if (!in_array($aihl_header_nav_layout, array('clean', 'pills', 'underline', 'compact'), true)) {
        $aihl_header_nav_layout = 'clean';
    }
    $aihl_header_nav_text_variant = (string) aihtml_option_value('header_nav_text_variant', 'normal');
    if (!in_array($aihl_header_nav_text_variant, array('normal', 'uppercase', 'lowercase', 'italic', 'uppercase-italic', 'lowercase-italic'), true)) {
        $aihl_header_nav_text_variant = 'normal';
    }
    $aihl_header_nav_font_weight = (string) aihtml_option_value('header_nav_font_weight', '500');
    if (!in_array($aihl_header_nav_font_weight, array('300', '400', '500', '600', '700', '800'), true)) {
        $aihl_header_nav_font_weight = '500';
    }
    $aihl_header_nav_letter_spacing = (float) str_replace(',', '.', (string) aihtml_option_value('header_nav_letter_spacing', '0'));
    $aihl_header_nav_letter_spacing = max(0, min(0.2, $aihl_header_nav_letter_spacing));
    $aihl_header_cta_label = trim((string) aihtml_option_value('header_cta_label', 'Consulenza gratuita'));
    $aihl_header_cta_url = esc_url((string) aihtml_option_value('header_cta_url', '#'));
    $aihl_header_login_label = trim((string) aihtml_option_value('header_login_label', 'Login'));
    $aihl_header_login_url = esc_url((string) aihtml_option_value('header_login_url', '#'));
    $aihl_contact_phone = trim((string) aihtml_option_value('contatti_telefono', ''));
    $aihl_contact_email = trim((string) aihtml_option_value('contatti_email', ''));
    $aihl_has_fullscreen_hero = function_exists('aihl_page_has_sbs_fullscreen_hero') && aihl_page_has_sbs_fullscreen_hero();

    $aihl_header_search_style = (string) aihtml_option_value('header_search_style', 'icon-dropdown');
    if (!in_array($aihl_header_search_style, array('none', 'icon-dropdown', 'icon-fullscreen', 'inline'), true)) {
        $aihl_header_search_style = 'icon-dropdown';
    }
    $aihl_header_topbar_scroll = (string) aihtml_option_value('header_topbar_scroll_behavior', 'scroll-away');
    if (!in_array($aihl_header_topbar_scroll, array('sticky', 'scroll-away'), true)) {
        $aihl_header_topbar_scroll = 'scroll-away';
    }
    $aihl_header_sticky_style = (string) aihtml_option_value('header_sticky_style', 'solid');
    if (!in_array($aihl_header_sticky_style, array('solid', 'blur', 'transparent', 'gradient-fade'), true)) {
        $aihl_header_sticky_style = 'solid';
    }
    $aihl_uses_overlay_header = $aihl_has_fullscreen_hero && 'never' !== $aihl_header_overlay_mode;
    $aihl_dark_surface_logo_variant = function_exists('aihl_get_dark_surface_logo_variant') ? aihl_get_dark_surface_logo_variant() : 'transparent';
    $aihl_header_logo_variant = $aihl_uses_overlay_header ? $aihl_dark_surface_logo_variant : 'default';
    $aihl_mobile_offcanvas_logo_variant = $aihl_dark_surface_logo_variant;

    $aihl_show_logo = (bool) aihtml_option_value('header_show_logo', true);
    $aihl_show_cta = (bool) aihtml_option_value('header_show_cta', true);
    $aihl_show_login = (bool) aihtml_option_value('header_show_login', true);

    $aihl_header_classes = array(
        'navbar',
        'navbar-expand-lg',
        'aihl-header-nav',
        'aihl-header-structure-' . $aihl_header_structure,
        'aihl-nav-layout-' . $aihl_header_nav_layout,
        'aihl-nav-text-' . $aihl_header_nav_text_variant,
        'aihl-overlay-mode-' . $aihl_header_overlay_mode,
        'aihl-sticky-style-' . $aihl_header_sticky_style,
    );
    if (!$aihl_show_logo) {
        $aihl_header_classes[] = 'aihl-hide-logo';
    }
    if (!$aihl_show_cta) {
        $aihl_header_classes[] = 'aihl-hide-cta';
    }
    if (!$aihl_show_login) {
        $aihl_header_classes[] = 'aihl-hide-login';
    }
    if ($aihl_uses_overlay_header) {
        $aihl_header_classes[] = 'aihl-header-overlay';
    } else {
        $aihl_header_classes[] = 'border-bottom';
        $aihl_header_classes[] = 'border-1';
        $aihl_header_classes[] = 'shadow-sm';
        $aihl_header_classes[] = 'bg-body';
        $aihl_header_classes[] = 'sticky-top';
    }
    $aihl_header_style = '--sbin-overlay-opacity:' . $aihl_header_overlay_opacity . ';--sbin-overlay-blur:' . $aihl_header_overlay_blur . 'px;--aihl-overlay-opacity:' . $aihl_header_overlay_opacity . ';--aihl-overlay-blur:' . $aihl_header_overlay_blur . 'px;--aihl-nav-font-weight:' . $aihl_header_nav_font_weight . ';--aihl-nav-letter-spacing:' . $aihl_header_nav_letter_spacing . 'em;';

    $aihl_has_topbar = in_array($aihl_header_structure, array('dualbar', 'topbar-nav', 'triple-row', 'stacked-centered'), true);
    $aihl_has_social_links = function_exists('aihl_get_site_builder_social_links') && !empty(aihl_get_site_builder_social_links());
    ?>

    <?php if ($aihl_header_structure === 'sidebar') : ?>
    <!-- Sidebar Header -->
    <aside class="aihl-sidebar-nav d-none d-lg-flex" aria-label="<?php esc_attr_e('Navigazione principale', AIHL_TEXT_DOMAIN); ?>">
        <div class="aihl-sidebar-brand">
            <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Homepage', AIHL_TEXT_DOMAIN); ?>">
                <?php if (!function_exists('aihl_render_site_logo') || !aihl_render_site_logo('default', 'img-fluid aihl-site-logo')) { ?>
                    <span class="h5 text-body mb-0"><?php bloginfo('name');?></span>
                <?php } ?>
            </a>
        </div>
        <nav class="aihl-sidebar-menu flex-grow-1">
            <?php wp_nav_menu(array(
                'menu_class' => 'aihl-sidebar-nav-list list-unstyled mb-0',
                'container' => '',
                'depth' => 2,
                'theme_location' => 'topic',
                'fallback_cb' => false,
            )); ?>
        </nav>
        <?php if ($aihl_header_search_style !== 'none') : ?>
        <div class="aihl-sidebar-search px-3 mb-3">
            <form class="d-flex border rounded-pill overflow-hidden" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" class="form-control form-control-sm border-0" placeholder="<?php esc_attr_e('Cerca...', AIHL_TEXT_DOMAIN); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
                <button class="btn btn-sm btn-primary rounded-0" type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>
        <?php endif; ?>
        <div class="aihl-sidebar-footer">
            <?php if ($aihl_header_cta_label !== '' && $aihl_header_cta_url !== '') : ?>
                <a class="btn btn-primary btn-sm w-100 mb-2 aihl-header-cta" href="<?php echo esc_url($aihl_header_cta_url); ?>">
                    <?php echo esc_html($aihl_header_cta_label); ?>
                </a>
            <?php endif; ?>
            <?php if ($aihl_header_login_label !== '' && $aihl_header_login_url !== '') : ?>
                <a class="btn btn-outline-primary btn-sm w-100 aihl-header-login" href="<?php echo esc_url($aihl_header_login_url); ?>">
                    <?php echo esc_html($aihl_header_login_label); ?>
                </a>
            <?php endif; ?>
        </div>
    </aside>
    <div class="aihl-sidebar-content-wrap">
    <!-- Mobile fallback navbar for sidebar -->
    <nav class="navbar navbar-expand-lg aihl-header-nav aihl-header-structure-sidebar d-lg-none border-bottom shadow-sm bg-body sticky-top" aria-label="<?php esc_attr_e('Navigazione principale', AIHL_TEXT_DOMAIN); ?>">
        <div class="container">
            <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <span class="h2 text-body mb-0"><?php bloginfo('name');?></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="<?php esc_attr_e('Apri menu', AIHL_TEXT_DOMAIN); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas <?php echo esc_attr($aihl_offcanvas_position); ?>" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header border-bottom pb-4">
                    <?php aihl_render_mobile_offcanvas_brand($aihl_mobile_offcanvas_logo_variant); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?php esc_attr_e('Chiudi menu', AIHL_TEXT_DOMAIN); ?>"></button>
                </div>
                <div class="offcanvas-body">
                    <?php wp_nav_menu(array(
                        'menu_class' => 'aihl-mobile-menu list-unstyled',
                        'container' => '',
                        'depth' => 3,
                        'theme_location' => 'topic',
                        'walker' => new AIHL_Mobile_Nav_Menu_Walker(),
                        'fallback_cb' => false,
                    )); ?>
                </div>
            </div>
        </div>
    </nav>

    <?php else : ?>

    <?php if ($aihl_has_topbar) : ?>
    <!-- Topbar -->
    <div class="aihl-topbar<?php echo $aihl_header_topbar_scroll === 'scroll-away' ? ' aihl-topbar-scroll-away' : ' aihl-topbar-sticky'; ?><?php echo $aihl_header_structure === 'dualbar' ? ' aihl-topbar-dark' : ''; ?>" data-topbar-scroll="<?php echo esc_attr($aihl_header_topbar_scroll); ?>">
        <div class="container">
            <div class="aihl-topbar-inner">
                <div class="aihl-topbar-left">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'naviga',
                        'menu_class' => 'aihl-utility-menu list-unstyled d-flex align-items-center mb-0',
                        'container' => '',
                        'depth' => 1,
                        'fallback_cb' => false,
                    ));
                    ?>
                </div>
                <div class="aihl-topbar-right">
                    <?php if ($aihl_contact_phone !== '') : ?>
                        <a class="aihl-utility-link" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $aihl_contact_phone)); ?>">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <span><?php echo esc_html($aihl_contact_phone); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($aihl_contact_email !== '') : ?>
                        <a class="aihl-utility-link" href="mailto:<?php echo esc_attr($aihl_contact_email); ?>">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            <span><?php esc_html_e('Inviaci una e-mail', AIHL_TEXT_DOMAIN); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($aihl_has_social_links) : ?>
                        <span class="aihl-utility-social">
                            <?php aihl_render_social_links('btn btn-link btn-sm p-0 aihl-utility-social-link'); ?>
                        </span>
                    <?php endif; ?>
                    <?php do_action('aihl/header/topbar/right', $aihl_slot_context); ?>
                    <?php if ($aihl_header_login_label !== '' && $aihl_header_login_url !== '') : ?>
                        <a class="btn btn-primary btn-sm aihl-header-login" href="<?php echo esc_url($aihl_header_login_url); ?>">
                            <?php echo esc_html($aihl_header_login_label); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (in_array($aihl_header_structure, array('triple-row', 'stacked-centered'), true)) : ?>
    <!-- Brand Bar (triple-row / stacked-centered) -->
    <div class="aihl-brand-bar aihl-brand-bar-<?php echo esc_attr($aihl_header_structure); ?>">
        <div class="container">
            <div class="aihl-brand-bar-inner">
                <?php if ($aihl_header_structure === 'triple-row') : ?>
                    <a class="aihl-brand-bar-logo" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Homepage', AIHL_TEXT_DOMAIN); ?>">
                        <?php if (!function_exists('aihl_render_site_logo') || !aihl_render_site_logo($aihl_header_logo_variant, 'img-fluid aihl-site-logo aihl-brand-bar-img')) { ?>
                            <span class="h3 text-body mb-0"><?php bloginfo('name');?></span>
                        <?php } ?>
                    </a>
                    <div class="aihl-brand-bar-actions d-none d-lg-flex">
                        <?php if ($aihl_header_search_style !== 'none' && $aihl_header_search_style !== 'inline') : ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm aihl-search-toggle" aria-label="<?php esc_attr_e('Cerca', AIHL_TEXT_DOMAIN); ?>" data-search-style="<?php echo esc_attr($aihl_header_search_style); ?>">
                            <i class="fa-solid fa-search me-1"></i> <?php esc_html_e('Cerca', AIHL_TEXT_DOMAIN); ?>
                        </button>
                        <?php elseif ($aihl_header_search_style === 'inline') : ?>
                        <form class="aihl-search-inline d-flex" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <input type="search" class="form-control form-control-sm" placeholder="<?php esc_attr_e('Cerca nel sito...', AIHL_TEXT_DOMAIN); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
                            <button class="btn btn-sm btn-primary ms-1" type="submit"><i class="fa-solid fa-search"></i></button>
                        </form>
                        <?php endif; ?>
                        <?php if ($aihl_header_login_label !== '' && $aihl_header_login_url !== '') : ?>
                            <a class="btn btn-outline-primary btn-sm aihl-header-login" href="<?php echo esc_url($aihl_header_login_url); ?>">
                                <i class="fa-solid fa-user me-1"></i> <?php echo esc_html($aihl_header_login_label); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($aihl_header_cta_label !== '' && $aihl_header_cta_url !== '') : ?>
                            <a class="btn btn-primary btn-sm aihl-header-cta" href="<?php echo esc_url($aihl_header_cta_url); ?>">
                                <?php echo esc_html($aihl_header_cta_label); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else : /* stacked-centered */ ?>
                    <div class="aihl-brand-bar-center text-center">
                        <a class="aihl-brand-bar-logo d-inline-block" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Homepage', AIHL_TEXT_DOMAIN); ?>">
                            <?php if (!function_exists('aihl_render_site_logo') || !aihl_render_site_logo($aihl_header_logo_variant, 'img-fluid aihl-site-logo aihl-brand-bar-img-large')) { ?>
                                <span class="display-6 text-body fw-bold"><?php bloginfo('name');?></span>
                            <?php } ?>
                        </a>
                        <?php if (get_bloginfo('description') !== '') : ?>
                            <p class="aihl-brand-bar-tagline text-muted small mb-0 mt-1"><?php bloginfo('description'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('header_start'); } ?>
    <!--  Navbar -->
    <nav
        class="<?php echo esc_attr(implode(' ', $aihl_header_classes)); ?>"
        style="<?php echo esc_attr($aihl_header_style); ?>"
        data-overlay-mode="<?php echo esc_attr($aihl_header_overlay_mode); ?>"
        data-rail-autohide="<?php echo $aihl_mobile_rail_autohide ? '1' : '0'; ?>"
        data-search-style="<?php echo esc_attr($aihl_header_search_style); ?>"
        aria-label="<?php esc_attr_e('Navigazione principale', AIHL_TEXT_DOMAIN); ?>"
    >
        <div class="container <?php
            if ($aihl_header_structure === 'dualbar') {
                echo 'aihl-header-daily-grid';
            } elseif ($aihl_header_structure === 'mega-centered') {
                echo 'aihl-header-mega-centered-grid';
            } else {
                echo 'aihl-header-main-row';
            }
        ?>">

            <?php if ($aihl_header_structure === 'mega-centered') : ?>
                <!-- Mega Centered: left menu + logo + right menu -->
                <div class="aihl-mc-left d-none d-lg-flex">
                    <?php wp_nav_menu(array(
                        'menu_class' => 'navbar-nav aihl-mc-nav',
                        'container' => '',
                        'depth' => 3,
                        'theme_location' => 'topic_left',
                        'walker' => new AIHL_Nav_Menu_Walker(),
                        'fallback_cb' => false,
                    )); ?>
                </div>
            <?php endif; ?>

            <?php
            $aihl_hide_navbar_brand = in_array($aihl_header_structure, array('triple-row', 'stacked-centered'), true);
            ?>
            <a class="navbar-brand<?php echo $aihl_hide_navbar_brand ? ' d-lg-none' : ''; ?>" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Homepage', AIHL_TEXT_DOMAIN); ?>" >
				<?php if (!function_exists('aihl_render_site_logo') || !aihl_render_site_logo($aihl_header_logo_variant, 'img-fluid m-auto flex-grow-1 aihl-site-logo')) { ?>
					<span class="h2 text-body mb-0"><?php bloginfo('name');?></span>
				<?php }?>
            </a>

            <?php if ($aihl_header_structure === 'mega-centered') : ?>
                <div class="aihl-mc-right d-none d-lg-flex">
                    <?php wp_nav_menu(array(
                        'menu_class' => 'navbar-nav aihl-mc-nav',
                        'container' => '',
                        'depth' => 3,
                        'theme_location' => has_nav_menu('topic_right') ? 'topic_right' : 'utili',
                        'walker' => new AIHL_Nav_Menu_Walker(),
                        'fallback_cb' => false,
                    )); ?>
                </div>
            <?php endif; ?>

            <?php if ($aihl_header_structure === 'dualbar') : ?>
            <div class="aihl-header-daily-panel">
                <div class="aihl-header-main-row">
            <?php endif; ?>

            <button id="aihl-mobile-menu-toggle" class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="<?php esc_attr_e('Apri menu di navigazione', AIHL_TEXT_DOMAIN); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas <?php echo esc_attr($aihl_offcanvas_position); ?>" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header border-bottom pb-4">
                    <?php aihl_render_mobile_offcanvas_brand($aihl_mobile_offcanvas_logo_variant); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?php esc_attr_e('Chiudi menu', AIHL_TEXT_DOMAIN); ?>"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column flex-lg-row align-items-lg-center aihl-header-nav-body">
                    <?php
                    $aihl_nav_menu_class = 'navbar-nav aihl-desktop-menu d-none d-lg-flex justify-content-end flex-grow-1 py-lg-0 me-lg-2';
                    if ($aihl_header_structure === 'stacked-centered') {
                        $aihl_nav_menu_class = 'navbar-nav aihl-desktop-menu d-none d-lg-flex justify-content-center flex-grow-1 py-lg-0';
                    } elseif ($aihl_header_structure === 'triple-row') {
                        $aihl_nav_menu_class = 'navbar-nav aihl-desktop-menu d-none d-lg-flex flex-grow-1 py-lg-0';
                    }
                    wp_nav_menu( array(
                        'menu_class' => $aihl_nav_menu_class,
                        'container' => '',
                        'depth' => 3,
                        'theme_location' => 'topic',
                        'walker' => new AIHL_Nav_Menu_Walker(),
                        'fallback_cb' => false,
                    )); ?>
                    <?php wp_nav_menu(array(
                        'menu_class' => 'aihl-mobile-menu list-unstyled d-lg-none',
                        'container' => '',
                        'depth' => 3,
                        'theme_location' => 'topic',
                        'walker' => new AIHL_Mobile_Nav_Menu_Walker(),
                        'fallback_cb' => false,
                    )); ?>
                    <div class="aihl-mobile-search d-lg-none">
                        <form
                            class	= "search-form"
                            role	= "search"
                            method	= "get"
                            id		= "searchform"
                            action	= "<?php echo esc_url(home_url('/')); ?>"
                        >
                            <input
                                type 				= "search"
                                class 				= "form-control"
                                placeholder 		= "<?php esc_attr_e('Cerca nel sito', AIHL_TEXT_DOMAIN); ?>"
                                aria-label			= "<?php esc_attr_e('Cerca nel sito', AIHL_TEXT_DOMAIN); ?>"
                                value				= "<?php echo esc_attr(strip_tags(get_search_query())); ?>"
                                name				= "s"
                                id					= "s"
                            >
                            <button
                                class	= "btn btn-primary"
                                type	= "submit"
                                aria-label="<?php esc_attr_e('Avvia la ricerca', AIHL_TEXT_DOMAIN); ?>"
                            >
                                <i class="fa-solid fa-search" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
					<?php if (!in_array($aihl_header_structure, array('dualbar', 'topbar-nav', 'triple-row', 'stacked-centered'), true) && $aihl_has_social_links) { ?>
                    <div class="d-flex align-items-center py-4 py-lg-0 aihl-header-social-main">
						<?php aihl_render_social_links('btn btn-outline-primary btn-square me-2'); ?>
                    </div>
					<?php } ?>

                    <!-- Desktop Search -->
                    <?php if ($aihl_header_search_style !== 'none' && $aihl_header_structure !== 'triple-row') : ?>
                    <div class="d-none d-lg-flex align-items-center aihl-header-search-wrap aihl-search-style-<?php echo esc_attr($aihl_header_search_style); ?>">
                        <?php if ($aihl_header_search_style === 'inline') : ?>
                            <form class="aihl-search-inline d-flex" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                                <input type="search" class="form-control form-control-sm" placeholder="<?php esc_attr_e('Cerca...', AIHL_TEXT_DOMAIN); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
                                <button class="btn btn-sm btn-primary ms-1" type="submit"><i class="fa-solid fa-search"></i></button>
                            </form>
                        <?php else : ?>
                            <button type="button" class="btn btn-link aihl-search-toggle p-1" aria-label="<?php esc_attr_e('Cerca', AIHL_TEXT_DOMAIN); ?>" data-search-style="<?php echo esc_attr($aihl_header_search_style); ?>">
                                <i class="fa-solid fa-search fa-lg"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($aihl_header_cta_label !== '' && $aihl_header_cta_url !== '' && $aihl_header_structure !== 'triple-row') : ?>
                    <div class="d-none d-lg-flex align-items-center py-lg-0">
                        <a class="btn btn-primary aihl-header-cta" href="<?php echo esc_url($aihl_header_cta_url); ?>">
                            <?php echo esc_html($aihl_header_cta_label); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($aihl_header_structure === 'dualbar') : ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <?php if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('header_end'); } ?>

    <!-- Search Dropdown Overlay -->
    <?php if ($aihl_header_search_style === 'icon-dropdown') : ?>
    <div class="aihl-search-dropdown" aria-hidden="true">
        <div class="container">
            <form class="aihl-search-dropdown-form d-flex align-items-center gap-2 py-3" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <i class="fa-solid fa-search text-muted"></i>
                <input type="search" class="form-control form-control-lg border-0 shadow-none" placeholder="<?php esc_attr_e('Cerca nel sito...', AIHL_TEXT_DOMAIN); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>" autofocus>
                <button type="button" class="btn-close aihl-search-close" aria-label="<?php esc_attr_e('Chiudi ricerca', AIHL_TEXT_DOMAIN); ?>"></button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search Fullscreen Overlay -->
    <?php if ($aihl_header_search_style === 'icon-fullscreen') : ?>
    <div class="aihl-search-fullscreen" aria-hidden="true">
        <div class="aihl-search-fullscreen-inner">
            <button type="button" class="btn-close btn-close-white aihl-search-close position-absolute top-0 end-0 m-4" aria-label="<?php esc_attr_e('Chiudi ricerca', AIHL_TEXT_DOMAIN); ?>"></button>
            <form class="aihl-search-fullscreen-form text-center" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="h6 text-uppercase text-white-50 mb-3 d-block"><?php esc_html_e('Cerca nel sito', AIHL_TEXT_DOMAIN); ?></label>
                <input type="search" class="aihl-search-fullscreen-input" placeholder="<?php esc_attr_e('Digita e premi Invio...', AIHL_TEXT_DOMAIN); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>" autofocus>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; /* end sidebar else */ ?>

    <?php endif; // end header_full override check ?>

    <?php aihl_render_mobile_quick_navigation($aihl_mobile_navigation, $aihl_has_header_override); ?>

    <?php
    if (function_exists('aihl_render_code_slot')) { aihl_render_code_slot('after_header'); aihl_render_code_slot('before_content'); }
    do_action('sdc/smart-builder-site/slot/slot.content.before', $aihl_slot_context);
    do_action('sdc/smart-site-builder/slot/slot.content.before', $aihl_slot_context);
    ?>
