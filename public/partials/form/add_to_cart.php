<p class="price">
    <?php echo K2CL_Customizer_Public::is_woocommerce_enabled() ? $defaultPriceWithFormat : ''; ?>
</p>
<form id="settings" action="<?php echo K2CL_Customizer_Public::get_customizer_link(get_the_ID()); ?>" method="POST" class="cart">
    <div class="col-sm-12">
    <?php if (K2CL_Customizer_Public::can_show_add_to_cart()): ?>
        <input type="hidden" name="add_to_cart" value="add_to_cart"/>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('add_to_cart'); ?>"/>
        <button type="submit" <?php echo($defaultPrice ? '' : 'style="display:none;"'); ?>
                class="customizer_add_to_cart_button single_add_to_cart_button button">
            <?php echo __('Add to cart', 'k2cl_customizer'); ?>
        </button>
    <?php endif; ?>
    <input type="hidden" name="customizer_selected_options" id="customizer_selected_options" value=""/>
    </div>
</form>
