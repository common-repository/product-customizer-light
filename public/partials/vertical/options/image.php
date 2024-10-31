<?php foreach ($group as $optionKey => $option): ?>
    <?php if ($option['option_enable'] == 1): ?>
        <?php $floatPrice = (!empty($product)) ? wc_get_price_to_display($product, ['price' => (float)$option['option_price']]) : (float)$option['option_price'] ?>
		<?php $price = strip_tags(K2CL_Customizer_Public::customizer_wc_price($option['option_price'])); ?>
        <?php $description = (empty($option['option_description']) ? esc_html($option['option_name']).(($floatPrice && K2CL_Customizer_Public::is_woocommerce_enabled()) ? ' (' . $price . ')' : '') : esc_html($option['option_description'])); ?>
        <?php $title = ($floatPrice && K2CL_Customizer_Public::is_woocommerce_enabled()) ? esc_html($option['option_name']) . ' (' . $price . ')' : esc_html($option['option_name']); ?>
        <div
            class="shop-filter__option tooltip-holder <?php echo !empty($option['option_default']) ? 'is-default' : ''; ?>"
            data-sf-elem="option"
            data-sf-data='{
            "type" : "<?php echo K2CL_Customizer_Public::COMPONENT_TYPE_IMAGE; ?>",
                "option_id": "<?php echo $option['option_id']; ?>",
                "src" : "<?php echo $option['option_image']; ?>",
                "icon"  : "<?php echo $option['option_icon']; ?>",
                "price" : "<?php echo $floatPrice; ?>",
                "title" : "<?php echo $title; ?>",
                "zIndex" : <?php echo $i++; ?>
                }' data-option_id="<?php echo $option['option_id']; ?>">
            <img src="<?php echo $option['option_icon']; ?>"
                 alt="<?php echo esc_html($option['option_name']); ?>"
                 class="shop-filter__option__icon">
            <div class="tooltip-text"
                 data-tooltip="<?php echo $description; ?>"></div>
        </div>
        <div class="customizer-preload-image">
            <img src="<?php echo $option['option_image']; ?>"
                 alt="<?php echo esc_html($option['option_name']); ?>"/>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
