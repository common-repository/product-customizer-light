<?php

$allOpenByDefault = !empty($customizer_settings['all_open']) ? $customizer_settings['all_open'] : 0;
$noImagePath = plugins_url( 'images/no_image.jpg' , dirname(__FILE__ ));
?>
<div class="shop-filter <?php echo($allOpenByDefault ? 'open-by-default' : ''); ?>" data-sf-group="components">
    <?php $i = 0; ?>
    <?php if (!empty($components)): ?>
        <?php foreach ($components as $componentId => $component): ?>
            <?php if ($component['component_enable'] == 1): ?>
                <div class="shop-filter__item">
                    <div class="shop-filter__component <?php echo($allOpenByDefault ? 'open-by-default' : ''); ?>"
                         data-sf-elem="component"
                         data-sf-component-id="<?php echo $component['component_id'] ?>"
                         data-sf-onload
                        <?php if ($allOpenByDefault): ?>
                            onLoad="shopFilter.pickOption(this)"
                        <?php endif; ?>
                         data-sf-data='{"placeholderText" : "<?php echo __('PLEASE SELECT', 'k2cl_customizer'); ?> (0)","placeholderIcon" : "<?php echo $noImagePath; ?>","counterText": "<?php echo __('Total', 'k2cl_customizer'); ?>: "}'>
                        
                        <div class="shop-filter__component__icon">
                            <?php if ($component['component_icon']): ?>
                                <img src="<?php echo $component['component_icon'] ?>"
                                     alt="<?php echo esc_html($component['component_name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="shop-filter__component__title"><?php echo esc_html($component['component_name']); ?></div>

                        <?php if ($component['component_type'] == K2CL_Customizer_Public::COMPONENT_TYPE_IMAGE): ?>
                            <div class="shop-filter__component__subtitle" data-sf-render="component-subtitle">
                                <?php echo __('PLEASE SELECT', 'k2cl_customizer'); ?> (<span
                                    class="customizer_price_symbol"></span> 0)
                            </div>
                        <?php endif; ?>
                        <div class="shop-filter__component__counter"
                             data-sf-render="component-counter"></div>
                        <img src="<?php echo $noImagePath; ?>" alt=""
                             class="shop-filter__component__icon-selected" data-sf-render="component-icon">
                    </div>
                    <div
                        class="shop-filter__options-wrapper" <?php echo(!empty($component['multiple']) ? 'data-sf-multiple' : ''); ?>
                        data-sf-group="options"
                        data-sf-component-id="<?php echo $component['component_id'] ?>">
                        <div class="shop-filter__options__table">
                            <?php foreach ($component['groups'] as $group_name => $group): ?>
                                <?php if ($group_name): ?>
                                    <div class="shop-filter__options__table-row">
                                    <div class="shop-filter__options__table-cell">
                                        <div
                                            class="shop-filter__options__table__label"><?php echo $group_name; ?></div>
                                    </div>
                                    <div class="shop-filter__options__table-cell">
                                <?php endif; ?>
                                <div class="shop-filter__options-list">
                                    <?php require 'options/' . $component['component_type'] . '.php'; ?>
                                </div>
                                <div class="controller-customizer-preload-image">
                                    <img src="<?php echo $option['option_image']; ?>"
                                         alt="<?php echo esc_html($option['option_name']); ?>"/>
                                </div>
                                <?php if ($group_name): ?>
                                    </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
