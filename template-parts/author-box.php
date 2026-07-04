<?php
/**
 * Template Part: Author Box
 *
 * Blocco autore per pagine articolo: bio visibile, link profilo e segnali E-E-A-T.
 * Supporta preset dal semplice al profilo enterprise ad alto impatto.
 *
 * @since 1.2.0
 */
if (!defined('ABSPATH')) {
	exit;
}

$aihl_author_id          = isset($args['author_id']) ? (int) $args['author_id'] : (int) get_the_author_meta('ID');
$aihl_author_name        = trim((string) get_the_author_meta('display_name', $aihl_author_id));
$aihl_author_bio         = trim((string) get_the_author_meta('user_description', $aihl_author_id));
$aihl_author_url         = get_author_posts_url($aihl_author_id);
$aihl_author_website     = trim((string) get_the_author_meta('user_url', $aihl_author_id));
$aihl_avatar_size        = isset($args['avatar_size']) ? max(72, (int) $args['avatar_size']) : 96;
$aihl_author_posts_count = count_user_posts($aihl_author_id, 'post', true);
$aihl_author_style       = isset($args['style']) ? sanitize_key($args['style']) : 'card';
$aihl_author_role        = '';

foreach (array('aihl_author_role', 'job_title', 'profession', 'title') as $aihl_role_key) {
	$aihl_role_value = trim((string) get_the_author_meta($aihl_role_key, $aihl_author_id));
	if ($aihl_role_value !== '') {
		$aihl_author_role = $aihl_role_value;
		break;
	}
}

$aihl_author_socials = array(
	'facebook'  => array('icon' => 'fab fa-facebook-f', 'label' => 'Facebook'),
	'twitter'   => array('icon' => 'fab fa-twitter', 'label' => 'Twitter'),
	'instagram' => array('icon' => 'fab fa-instagram', 'label' => 'Instagram'),
	'youtube'   => array('icon' => 'fab fa-youtube', 'label' => 'YouTube'),
	'linkedin'  => array('icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'),
);

$aihl_author_same_as = array();
foreach ($aihl_author_socials as $aihl_social_key => $aihl_social_data) {
	$aihl_social_url = trim((string) get_the_author_meta($aihl_social_key, $aihl_author_id));
	if ($aihl_social_url !== '') {
		$aihl_author_same_as[$aihl_social_key] = array(
			'url'   => $aihl_social_url,
			'icon'  => $aihl_social_data['icon'],
			'label' => $aihl_social_data['label'],
		);
	}
}

if ($aihl_author_name === '') {
	return;
}

$aihl_author_bio_text = $aihl_author_bio !== '' ? $aihl_author_bio : sprintf(
	esc_html__('Consulta gli altri approfondimenti pubblicati da %s e verifica il profilo autore.', AIHL_TEXT_DOMAIN),
	esc_html($aihl_author_name)
);
?>

<?php if ($aihl_author_style === 'simple') : ?>

<aside class="aihl-author-box aihl-author-box--simple" itemscope itemtype="https://schema.org/Person" aria-labelledby="aihl-author-box-title">
	<p class="aihl-author-kicker"><?php esc_html_e('Scritto da', AIHL_TEXT_DOMAIN); ?></p>
	<h2 id="aihl-author-box-title" class="aihl-author-name h6 mb-1" itemprop="name">
		<a href="<?php echo esc_url($aihl_author_url); ?>" rel="author" itemprop="url"><?php echo esc_html($aihl_author_name); ?></a>
	</h2>
	<?php if ($aihl_author_role !== '') : ?><p class="aihl-author-role mb-0" itemprop="jobTitle"><?php echo esc_html($aihl_author_role); ?></p><?php endif; ?>
</aside>

<?php elseif ($aihl_author_style === 'compact') : ?>

<aside class="aihl-author-box aihl-author-box--compact" itemscope itemtype="https://schema.org/Person" aria-labelledby="aihl-author-box-title">
	<a class="aihl-author-avatar" href="<?php echo esc_url($aihl_author_url); ?>" rel="author" itemprop="url">
		<?php echo get_avatar($aihl_author_id, 48, '', esc_attr($aihl_author_name), array('class' => 'aihl-author-avatar-img')); ?>
	</a>
	<div class="aihl-author-box-content">
		<p class="aihl-author-kicker"><?php esc_html_e('Autore dell\'articolo', AIHL_TEXT_DOMAIN); ?></p>
		<h2 id="aihl-author-box-title" class="aihl-author-name h6 mb-0" itemprop="name">
			<a href="<?php echo esc_url($aihl_author_url); ?>" rel="author"><?php echo esc_html($aihl_author_name); ?></a>
		</h2>
		<?php if ($aihl_author_role !== '') : ?>
			<p class="aihl-author-role mb-0" itemprop="jobTitle"><?php echo esc_html($aihl_author_role); ?></p>
		<?php endif; ?>
	</div>
	<?php if (!empty($aihl_author_same_as)) : ?>
		<div class="aihl-author-social ms-auto" aria-label="<?php esc_attr_e('Profili autore', AIHL_TEXT_DOMAIN); ?>">
			<?php foreach ($aihl_author_same_as as $aihl_social) : ?>
				<a class="aihl-author-social-link" href="<?php echo esc_url($aihl_social['url']); ?>" title="<?php echo esc_attr($aihl_social['label']); ?>" target="_blank" rel="nofollow noopener" itemprop="sameAs" aria-label="<?php echo esc_attr($aihl_social['label']); ?>">
					<i class="<?php echo esc_attr($aihl_social['icon']); ?>" aria-hidden="true"></i>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</aside>

<?php elseif (in_array($aihl_author_style, array('editorial', 'enterprise', 'impact', 'signature'), true)) : ?>

<aside class="aihl-author-box aihl-author-box--<?php echo esc_attr($aihl_author_style); ?>" itemscope itemtype="https://schema.org/Person" aria-labelledby="aihl-author-box-title">
	<div class="aihl-author-box-media">
		<a class="aihl-author-avatar" href="<?php echo esc_url($aihl_author_url); ?>" rel="author" itemprop="url">
			<?php echo get_avatar($aihl_author_id, $aihl_avatar_size, '', esc_attr($aihl_author_name), array('class' => 'aihl-author-avatar-img')); ?>
		</a>
	</div>
	<div class="aihl-author-box-content">
		<p class="aihl-author-kicker"><?php echo esc_html('signature' === $aihl_author_style ? __('La firma', AIHL_TEXT_DOMAIN) : __('Autore dell\'articolo', AIHL_TEXT_DOMAIN)); ?></p>
		<h2 id="aihl-author-box-title" class="aihl-author-name h3 mb-1" itemprop="name"><a href="<?php echo esc_url($aihl_author_url); ?>" rel="author"><?php echo esc_html($aihl_author_name); ?></a></h2>
		<?php if ($aihl_author_role !== '') : ?><p class="aihl-author-role mb-2" itemprop="jobTitle"><?php echo esc_html($aihl_author_role); ?></p><?php endif; ?>
		<p class="aihl-author-bio mb-3" itemprop="description"><?php echo esc_html($aihl_author_bio_text); ?></p>
		<div class="aihl-author-actions">
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url($aihl_author_url); ?>" rel="author"><?php esc_html_e('Approfondisci il profilo', AIHL_TEXT_DOMAIN); ?></a>
			<?php if ($aihl_author_website !== '') : ?><a class="btn btn-outline-primary btn-sm" href="<?php echo esc_url($aihl_author_website); ?>" target="_blank" rel="noopener nofollow" itemprop="sameAs"><?php esc_html_e('Sito autore', AIHL_TEXT_DOMAIN); ?></a><?php endif; ?>
		</div>
	</div>
	<div class="aihl-author-box-aside">
		<div class="aihl-author-stat"><strong><?php echo esc_html(number_format_i18n($aihl_author_posts_count)); ?></strong><span><?php esc_html_e('articoli pubblicati', AIHL_TEXT_DOMAIN); ?></span></div>
		<?php if (!empty($aihl_author_same_as)) : ?><div class="aihl-author-social" aria-label="<?php esc_attr_e('Profili autore', AIHL_TEXT_DOMAIN); ?>"><?php foreach ($aihl_author_same_as as $aihl_social) : ?><a class="aihl-author-social-link" href="<?php echo esc_url($aihl_social['url']); ?>" target="_blank" rel="nofollow noopener" itemprop="sameAs" aria-label="<?php echo esc_attr($aihl_social['label']); ?>"><i class="<?php echo esc_attr($aihl_social['icon']); ?>" aria-hidden="true"></i></a><?php endforeach; ?></div><?php endif; ?>
	</div>
</aside>

<?php elseif ($aihl_author_style === 'banner') : ?>

<aside class="aihl-author-box aihl-author-box--banner" itemscope itemtype="https://schema.org/Person" aria-labelledby="aihl-author-box-title">
	<div class="aihl-author-box-media">
		<a class="aihl-author-avatar" href="<?php echo esc_url($aihl_author_url); ?>" rel="author" itemprop="url">
			<?php echo get_avatar($aihl_author_id, $aihl_avatar_size, '', esc_attr($aihl_author_name), array('class' => 'aihl-author-avatar-img')); ?>
		</a>
	</div>

	<div class="aihl-author-box-content">
		<p class="aihl-author-kicker"><?php esc_html_e('Autore dell\'articolo', AIHL_TEXT_DOMAIN); ?></p>
		<h2 id="aihl-author-box-title" class="aihl-author-name h4 mb-1" itemprop="name">
			<a href="<?php echo esc_url($aihl_author_url); ?>" rel="author"><?php echo esc_html($aihl_author_name); ?></a>
		</h2>

		<?php if ($aihl_author_role !== '') : ?>
			<p class="aihl-author-role mb-2" itemprop="jobTitle"><?php echo esc_html($aihl_author_role); ?></p>
		<?php endif; ?>

		<p class="aihl-author-bio mb-3" itemprop="description"><?php echo esc_html($aihl_author_bio_text); ?></p>

		<div class="aihl-author-actions">
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url($aihl_author_url); ?>" rel="author">
				<?php esc_html_e('Profilo autore', AIHL_TEXT_DOMAIN); ?>
			</a>
			<?php if ($aihl_author_website !== '') : ?>
				<a class="btn btn-outline-primary btn-sm" href="<?php echo esc_url($aihl_author_website); ?>" target="_blank" rel="noopener nofollow" itemprop="sameAs">
					<?php esc_html_e('Sito autore', AIHL_TEXT_DOMAIN); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="d-flex align-items-center gap-3 mt-3">
			<div class="aihl-author-stat">
				<strong><?php echo esc_html(number_format_i18n($aihl_author_posts_count)); ?></strong>
				<span><?php esc_html_e('contenuti pubblicati', AIHL_TEXT_DOMAIN); ?></span>
			</div>
			<?php if (!empty($aihl_author_same_as)) : ?>
				<div class="aihl-author-social" aria-label="<?php esc_attr_e('Profili autore', AIHL_TEXT_DOMAIN); ?>">
					<?php foreach ($aihl_author_same_as as $aihl_social) : ?>
						<a class="aihl-author-social-link" href="<?php echo esc_url($aihl_social['url']); ?>" title="<?php echo esc_attr($aihl_social['label']); ?>" target="_blank" rel="nofollow noopener" itemprop="sameAs" aria-label="<?php echo esc_attr($aihl_social['label']); ?>">
							<i class="<?php echo esc_attr($aihl_social['icon']); ?>" aria-hidden="true"></i>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</aside>

<?php else : ?>

<aside class="aihl-author-box aihl-author-box--card" itemscope itemtype="https://schema.org/Person" aria-labelledby="aihl-author-box-title">
	<div class="aihl-author-box-media">
		<a class="aihl-author-avatar" href="<?php echo esc_url($aihl_author_url); ?>" rel="author" itemprop="url">
			<?php echo get_avatar($aihl_author_id, $aihl_avatar_size, '', esc_attr($aihl_author_name), array('class' => 'aihl-author-avatar-img')); ?>
		</a>
	</div>

	<div class="aihl-author-box-content">
		<p class="aihl-author-kicker"><?php esc_html_e('Autore dell\'articolo', AIHL_TEXT_DOMAIN); ?></p>
		<h2 id="aihl-author-box-title" class="aihl-author-name h5 mb-1" itemprop="name">
			<a href="<?php echo esc_url($aihl_author_url); ?>" rel="author"><?php echo esc_html($aihl_author_name); ?></a>
		</h2>

		<?php if ($aihl_author_role !== '') : ?>
			<p class="aihl-author-role mb-2" itemprop="jobTitle"><?php echo esc_html($aihl_author_role); ?></p>
		<?php endif; ?>

		<p class="aihl-author-bio mb-3" itemprop="description"><?php echo esc_html($aihl_author_bio_text); ?></p>

		<div class="aihl-author-actions">
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url($aihl_author_url); ?>" rel="author">
				<?php esc_html_e('Profilo autore', AIHL_TEXT_DOMAIN); ?>
			</a>
			<?php if ($aihl_author_website !== '') : ?>
				<a class="btn btn-outline-primary btn-sm" href="<?php echo esc_url($aihl_author_website); ?>" target="_blank" rel="noopener nofollow" itemprop="sameAs">
					<?php esc_html_e('Sito autore', AIHL_TEXT_DOMAIN); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<div class="aihl-author-box-aside">
		<div class="aihl-author-stat">
			<strong><?php echo esc_html(number_format_i18n($aihl_author_posts_count)); ?></strong>
			<span><?php esc_html_e('contenuti pubblicati', AIHL_TEXT_DOMAIN); ?></span>
		</div>

		<?php if (!empty($aihl_author_same_as)) : ?>
			<div class="aihl-author-social" aria-label="<?php esc_attr_e('Profili autore', AIHL_TEXT_DOMAIN); ?>">
				<?php foreach ($aihl_author_same_as as $aihl_social) : ?>
					<a class="aihl-author-social-link" href="<?php echo esc_url($aihl_social['url']); ?>" title="<?php echo esc_attr($aihl_social['label']); ?>" target="_blank" rel="nofollow noopener" itemprop="sameAs" aria-label="<?php echo esc_attr($aihl_social['label']); ?>">
						<i class="<?php echo esc_attr($aihl_social['icon']); ?>" aria-hidden="true"></i>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</aside>

<?php endif; ?>
