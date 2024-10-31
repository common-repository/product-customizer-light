<?php

/**
 * Product Customizer Template (LeftSidebar)
 *
 * @link              http://k2-service.com/shop/product-customizer/
 * @author            K2-Service <plugins@k2-service.com>
 *
 * @package           K2CL_Customizer
 * @subpackage        K2CL_Customizer/public/зфкешфды
 */
global $k2cl_customizer_widget;
$components = K2CL_Customizer_Public::get_customizer_meta();
$components = $components['customizer'];
$customizer_settings = K2CL_Customizer_Public::get_customizer_settings();
$product = K2CL_Customizer_Public::is_woocommerce_enabled() ? wc_get_product($customizer_settings['product_id']) : '';

$defaultPrice = K2CL_Customizer_Public::calculate_default_price(false);
$defaultPriceWithFormat = K2CL_Customizer_Public::customizer_wc_price($defaultPrice);
?>

<?php if(!$k2cl_customizer_widget){get_header();} ?>
    <div class="container-customizer">
        <div class="row-customizer">
            <div class="col-sm-4">
                <?php require_once('vertical/options.php'); ?>
                <div class="row-customizer single-product">
                    <div class="col-sm-12 product summary">
                        <?php require_once('form/add_to_cart.php'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-7 pic-wrapper">
                <?php require_once('vertical/image.php'); ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="footer sticky-stop"></div>
    <script type="text/javascript">
        var DEFAULT_PRODUCT_PRICE = '<?php echo (float)$defaultPrice; ?>';
    </script>
<?php if(!$k2cl_customizer_widget){get_footer();} ?>