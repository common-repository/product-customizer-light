<?php
/**
 * The template for displaying customizer content within loops
 * @author            K2-Service <plugins@k2-service.com>
 * @link              http://k2-service.com/shop/product-customizer/
 */
global $indexCustomizer;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$data = K2CL_Customizer_Public::get_customizer_meta();
$images = K2CL_Customizer_Public::get_all_customizer_images($data['customizer'], true, true);
$price = K2CL_Customizer_Public::calculate_default_price();

$additionalClass = '';
if ($indexCustomizer == 0 || ($indexCustomizer % 3) == 0) {
    $additionalClass = ' first';
}
if ((($indexCustomizer + 1) % 3) == 0) {
    $additionalClass = ' last';
}

?>
    <li <?php post_class('product' . $additionalClass); ?>>
        <a href="<?php the_permalink(); ?>" class="woocommerce-LoopProduct-link">
            <div class="customizer-thumbnail customizer-thumbnail-list">
                <?php foreach ($images as $_image): ?>
                    <?php if($_image):?>
                        <img src="<?php echo $_image; ?>" alt="<?php echo esc_html(get_the_title()); ?>"
                             class="customizer-thumbnail-image""/>
                    <?php endif;?>
                <?php endforeach; ?>
            </div>
            <h3><?php the_title(); ?></h3>
            <?php if ($price): ?>
                <?php echo K2CL_Customizer_Public::customizer_wc_price($price); ?>
            <?php endif; ?>
        </a>
    </li>

<?php $indexCustomizer++; ?>
