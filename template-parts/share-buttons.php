<?php
/**
 * Template Part: Share Buttons
 *
 * Pulsanti condivisione social senza JS esterni.
 * $args['style']: 'vertical' | 'responsive' | 'horizontal' | 'minimal'
 *
 * @since 1.2.0
 */
if (!defined('ABSPATH')) {
	exit;
}

$aihl_share_style = isset($args['style']) ? $args['style'] : 'vertical';
$aihl_share_url = rawurlencode(get_permalink());
$aihl_share_title = rawurlencode(get_the_title());

$aihl_share_channels = array(
	array(
		'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $aihl_share_url,
		'icon'  => 'fab fa-facebook-f',
		'label' => 'Facebook',
	),
	array(
		'url'   => 'https://twitter.com/intent/tweet?url=' . $aihl_share_url . '&text=' . $aihl_share_title,
		'icon'  => 'fab fa-twitter',
		'label' => 'Twitter',
	),
	array(
		'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $aihl_share_url,
		'icon'  => 'fab fa-linkedin-in',
		'label' => 'LinkedIn',
	),
	array(
		'url'   => 'https://api.whatsapp.com/send?text=' . $aihl_share_title . '%20' . $aihl_share_url,
		'icon'  => 'fab fa-whatsapp',
		'label' => 'WhatsApp',
	),
);

$aihl_wrap_class = 'aihl-share-buttons';
$aihl_btn_class = 'btn btn-outline-primary btn-square';

if ($aihl_share_style === 'vertical') {
	$aihl_wrap_class .= ' d-flex flex-column gap-2';
	$aihl_btn_class .= ' mb-1';
} elseif ($aihl_share_style === 'responsive') {
	$aihl_wrap_class .= ' aihl-share-buttons-responsive d-flex flex-row flex-lg-column gap-2';
} elseif ($aihl_share_style === 'minimal') {
	$aihl_wrap_class .= ' d-flex flex-wrap gap-1';
	$aihl_btn_class = 'btn btn-link btn-sm p-1 text-muted';
} else {
	$aihl_wrap_class .= ' d-flex flex-wrap gap-2';
}
?>
<div class="<?php echo esc_attr($aihl_wrap_class); ?>">
	<?php foreach ($aihl_share_channels as $channel) : ?>
		<a class="<?php echo esc_attr($aihl_btn_class); ?>"
		   href="<?php echo esc_url($channel['url']); ?>"
		   title="<?php echo esc_attr($channel['label']); ?>"
		   aria-label="<?php /* translators: %s: social network */ echo esc_attr(sprintf(__('Condividi su %s', AIHL_TEXT_DOMAIN), $channel['label'])); ?>"
		   target="_blank"
		   rel="noopener nofollow">
			<i class="<?php echo esc_attr($channel['icon']); ?>"></i>
		</a>
	<?php endforeach; ?>
	<?php if (isset($args['show_native']) ? (bool) $args['show_native'] : true) : ?>
		<button type="button"
				class="<?php echo esc_attr($aihl_btn_class); ?>"
				title="<?php esc_attr_e('Condividi', AIHL_TEXT_DOMAIN); ?>"
				aria-label="<?php esc_attr_e('Condividi', AIHL_TEXT_DOMAIN); ?>"
				onclick="if(navigator.share){navigator.share({title:document.title,url:location.href})}">
			<i class="fa-solid fa-share-nodes"></i>
		</button>
	<?php endif; ?>
</div>
