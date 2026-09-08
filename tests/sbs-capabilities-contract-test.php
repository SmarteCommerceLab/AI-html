<?php

$support = file_get_contents(dirname(__DIR__) . '/inc/theme/support.php');
$api = file_get_contents(dirname(__DIR__) . '/inc/integrations/ai-api.php');
$utilities = file_get_contents(dirname(__DIR__) . '/inc/theme/utilities.php');

foreach (array(
    "add_theme_support('smart-builder-site'",
    "'smart-site-home.php'    => array('builder' => true, 'compose' => false)",
    "'smart-site-builder.php' => array('builder' => true, 'compose' => false)",
    "'smart-site-blog.php'    => array('builder' => true, 'compose' => false)",
) as $needle) {
    if (false === strpos($support, $needle)) {
        fwrite(STDERR, "Missing SBS capability: {$needle}\n");
        exit(1);
    }
}

if (false === strpos($api, "array('', 'default', 'smart-site-home.php', 'smart-site-builder.php', 'smart-site-blog.php')")) {
    fwrite(STDERR, "AI API does not allow all SBS templates.\n");
    exit(1);
}

if (false === strpos($utilities, "array('smart-site-home.php', 'smart-site-builder.php', 'smart-site-blog.php')")) {
    fwrite(STDERR, "AI-HTML body classes do not cover all SBS templates.\n");
    exit(1);
}

foreach (array(
    "'smart-site-home.php'     => 'Home builder (SBS)'",
    "'smart-site-builder.php'  => 'Pagina builder (SBS)'",
    "'smart-site-blog.php'     => 'Blog builder (SBS)'",
) as $needle) {
    if (false === strpos($api, $needle)) {
        fwrite(STDERR, "AI API missing template: {$needle}\n");
        exit(1);
    }
}

echo "AI-HTML SBS capabilities contract OK\n";
