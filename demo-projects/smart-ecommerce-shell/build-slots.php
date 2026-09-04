<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$base = __DIR__;
$read = static function (string $name) use ($base): string {
    $contents = file_get_contents($base . '/' . $name);
    if (false === $contents) { fwrite(STDERR, "File mancante: {$name}\n"); exit(1); }
    return $contents;
};
$slots = array(
    array('id' => 'smart-ecommerce-header-full', 'label' => 'Smart eCommerce Header Full', 'hook' => 'header_full', 'type' => 'mixed', 'context' => 'global', 'priority' => 10, 'active' => true, 'author' => 'Smart eCommerce', 'code' => $read('header.html'), 'css' => $read('header.css'), 'js' => $read('header.js')),
    array('id' => 'smart-ecommerce-footer-full', 'label' => 'Smart eCommerce Footer Full', 'hook' => 'footer_full', 'type' => 'mixed', 'context' => 'global', 'priority' => 10, 'active' => true, 'author' => 'Smart eCommerce', 'code' => $read('footer.html'), 'css' => $read('footer.css'), 'js' => ''),
);
$json = json_encode(array('format' => 'aihl-code-slots', 'version' => 1, 'count' => count($slots), 'slots' => $slots), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (false === $json || false === file_put_contents($base . '/smart-ecommerce-shell-slots.json', $json . PHP_EOL)) { fwrite(STDERR, "Generazione JSON non riuscita.\n"); exit(1); }
$preview_header = str_replace(
    array('<smart-logo variant="default" class="sec-shell-header__logo"></smart-logo>', '<smart-menu location="topic" class="sec-shell-header__menu" depth="2"></smart-menu>', '<smart-social class="sec-shell-header__social-link"></smart-social>'),
    array('<a class="aihl-runtime-brand" href="#"><strong class="sec-shell-header__logo">Smart eCommerce</strong></a>', '<ul class="sec-shell-header__menu"><li><a href="#">Prodotti</a><ul class="sub-menu"><li><a href="#">AI-HTML</a></li><li><a href="#">Smart Builder Site</a></li><li><a href="#">Smart Login</a></li></ul></li><li><a href="#">Blog</a></li><li><a href="#">Editoria e advertising</a></li><li><a href="#">Magazine</a></li></ul>', '<a class="sec-shell-header__social-link" href="#" aria-label="LinkedIn">in</a>'),
    $read('header.html')
);
$preview_footer = str_replace(
    array('<smart-logo variant="footer" class="sec-shell-footer__logo"></smart-logo>', '<smart-menu location="topic" class="sec-shell-footer__menu" depth="1"></smart-menu>', '<smart-social class="sec-shell-footer__social-link"></smart-social>', '<smart-contact field="email" link="true" class="sec-shell-footer__link"></smart-contact>', '<smart-contact field="address"></smart-contact>'),
    array('<a class="aihl-runtime-brand" href="#"><strong class="sec-shell-footer__logo">Smart eCommerce</strong></a>', '<ul class="sec-shell-footer__menu"><li><a href="#">Prodotti</a></li><li><a href="#">Blog</a></li><li><a href="#">Editoria e advertising</a></li><li><a href="#">Magazine</a></li></ul>', '<a class="sec-shell-footer__social-link" href="#" aria-label="LinkedIn">in</a><a class="sec-shell-footer__social-link" href="#" aria-label="YouTube">yt</a>', '<a class="sec-shell-footer__link" href="#">adv@smartecommerce.it</a>', 'Napoli / Italia / Remote'),
    $read('footer.html')
);
$preview = '<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Smart eCommerce shell preview</title><style>:root{--bs-primary:#e509f9;--bs-body-bg:#fff;--bs-body-color:#11110f;--bs-dark:#11110f;--bs-light:#fff;--bs-border-color:#d9d9de;--bs-dark-rgb:17,17,15;--bs-body-font-family:Arial,sans-serif}body{margin:0;background:#f4f4f6;color:#11110f}main{display:grid;min-height:85vh;place-items:center;padding:120px 24px 60px}main h1{max-width:900px;margin:0;font-size:clamp(3rem,8vw,8rem);line-height:.92}.screen-reader-text{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}</style><style>' . $read('header.css') . $read('footer.css') . '</style></head><body>' . $preview_header . '<main><h1>Trasformiamo idee in sistemi vivi.</h1></main>' . $preview_footer . '<script>' . $read('header.js') . '</script></body></html>';
if (false === file_put_contents($base . '/preview.html', $preview)) { fwrite(STDERR, "Generazione preview non riuscita.\n"); exit(1); }
$mobile = '<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mobile shell preview</title><style>body{margin:0;padding:20px;background:#202124}iframe{display:block;width:390px;max-width:100%;height:844px;margin:auto;border:0;background:#fff;box-shadow:0 12px 40px #000}</style></head><body><iframe src="preview.html" title="Preview mobile a 390 pixel"></iframe></body></html>';
if (false === file_put_contents($base . '/preview-mobile.html', $mobile)) { fwrite(STDERR, "Generazione preview mobile non riuscita.\n"); exit(1); }
echo $base . '/smart-ecommerce-shell-slots.json' . PHP_EOL;
