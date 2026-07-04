<?php
/*
* Template name: About
*/
?>
<?php get_header(); if (have_posts()) { the_post(); } ?>

<main id="main" class="site-main">
    <!-- About Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="position-relative overflow-hidden h-100" style="min-height: 400px;">
                        <img class="position-absolute w-100 h-100 pt-5 pe-5" src="<?php echo esc_url(AIHL_DIR_URL . '/resource/img/about-img.jpg'); ?>" alt="<?php esc_attr_e('Immagine istituzionale', AIHL_TEXT_DOMAIN); ?>" style="object-fit: cover;" loading="eager" decoding="async">
                        <img class="position-absolute top-0 end-0 bg-body ps-2 pb-2" src="<?php echo esc_url(AIHL_DIR_URL . '/resource/img/bg-about-img.png'); ?>" alt="<?php esc_attr_e('Dettaglio aziendale', AIHL_TEXT_DOMAIN); ?>" style="width: 200px; height: 200px;" loading="lazy" decoding="async">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="h-100">
                        <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3"><?php esc_html_e('Chi siamo', AIHL_TEXT_DOMAIN); ?></div>
                        <h1 class="display-6 mb-5"><?php esc_html_e('Un tema corporate pensato per contenuti, servizi e comunicazione aziendale', AIHL_TEXT_DOMAIN); ?></h1>
                        <div class="bg-light border-bottom border-5 border-primary rounded p-4 mb-4">
                            <p class="text-dark mb-2"><?php esc_html_e('La struttura del tema valorizza identita aziendale, contenuti editoriali, pagine istituzionali e sezioni vetrina integrate con l ecosistema Smart.', AIHL_TEXT_DOMAIN); ?></p>
                            <span class="text-primary"><?php echo esc_html(get_bloginfo('name')); ?></span>
                        </div>
                        <p class="mb-5"><?php esc_html_e('AI-HTML separa il layer di presentazione dai contenuti gestiti dal builder e dai plugin Smart, mantenendo template WordPress chiari per blog, pagine corporate, ricerca e categorie.', AIHL_TEXT_DOMAIN); ?></p>
                        <a class="btn btn-primary py-2 px-3 me-3" href="<?php echo esc_url(home_url('/blog/')); ?>">
                            <?php esc_html_e('Blog', AIHL_TEXT_DOMAIN); ?>
                            <div class="d-inline-flex btn-sm-square bg-body text-primary rounded-circle ms-2">
                                <i class="fa fa-arrow-right"></i>
                            </div>
                        </a>
                        <a class="btn btn-outline-primary py-2 px-3" href="<?php echo esc_url(home_url('/contacts/')); ?>">
                            <?php esc_html_e('Contatti', AIHL_TEXT_DOMAIN); ?>
                            <div class="d-inline-flex btn-sm-square bg-primary text-white rounded-circle ms-2">
                                <i class="fa fa-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Team Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 500px;">
                <div class="d-inline-block rounded-pill bg-primary text-white py-1 px-3 mb-3"><?php esc_html_e('Team', AIHL_TEXT_DOMAIN); ?></div>
                <h1 class="display-6 mb-5"><?php esc_html_e('Persone, competenze e riferimenti aziendali', AIHL_TEXT_DOMAIN); ?></h1>
            </div>
            <div class="row g-4 d-flex justify-content-center">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item position-relative rounded overflow-hidden">
                        <div class="overflow-hidden">
                            <img class="img-fluid" src="<?php echo esc_url(AIHL_DIR_URL . '/resource/img/team-2.jpg'); ?>" alt="Chiara Pinci" loading="lazy" decoding="async">
                        </div>
                        <div class="team-text bg-light text-center p-4">
                            <h5><?php echo esc_html(get_bloginfo('name')); ?></h5>
                            <p class="text-primary"><?php esc_html_e('Corporate Team', AIHL_TEXT_DOMAIN); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

<?php if (trim((string) get_the_content()) !== '') { the_content(); } ?>
</main>
<?php get_footer(); ?>
