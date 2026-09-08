<?php

$support = file_get_contents(dirname(__DIR__) . '/inc/theme/support.php');
$api = file_get_contents(dirname(__DIR__) . '/inc/integrations/ai-api.php');

foreach (array(
    "add_theme_support('smart-builder-site'",
    "'smart-site-blog.php' => array('builder' => true, 'compose' => true)",
) as $needle) {
    if (false === strpos($support, $needle)) {
        fwrite(STDERR, "Missing SBS capability: {$needle}\n");
        exit(1);
    }
}

if (false !== strpos($support, "'smart-site-home.php'")) {
    fwrite(STDERR, "AI-HTML must not advertise Smart Site Home support.\n");
    exit(1);
}
if (false !== strpos($api, "'smart-site-home.php'     => 'Home builder (SBS)'")) {
    fwrite(STDERR, "AI API still advertises Smart Site Home.\n");
    exit(1);
}

echo "AI-HTML SBS capabilities contract OK\n";
