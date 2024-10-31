<?php
/**
 * The template for displaying archive customizers.
 *
 *
 * @author            K2-Service <plugins@k2-service.com>
 * @link              http://k2-service.com/shop/product-customizer/
 */
get_header(); ?>

    <main class="site-main container-customizer woocommerce">
        <?php if (have_posts()) : ?>
            <div class="page-head">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12 information-block">
                            <h1 class="page-title"><?php echo __('Customizers', 'k2cl_customizer'); ?></h1>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="products">
                <?php
                do_action('storefront_loop_before');
                global $indexCustomizer;
                $indexCustomizer = 0;
                while (have_posts()) : the_post();
                    load_template(plugin_dir_path(__FILE__) . 'content-customizer.php', false);
                endwhile;
                do_action('storefront_loop_after'); ?>
            </ul>
        <?php else :
            get_template_part('content', 'none');
        endif; ?>
    </main>

<?php
get_footer();
