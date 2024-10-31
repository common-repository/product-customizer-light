<?php
$defaultImage = K2CL_Customizer_Public::get_default_image('full');
list($width, $height) = K2CL_Customizer_Public::getMaxWidthHeight([$defaultImage]);
$style = '';
if($width){
    $style = 'width:'.$width.'px;';
}
$zIndex = 0;
?>
<div class="shop-filter__viewport" style="<?php echo $style;?>">
    <?php foreach ($components as $component): ?>
        <?php if (isset($component['options'])): ?>
            <?php $options = $component['options']; ?>
            <?php if ($component['component_type'] == K2CL_Customizer_Public::COMPONENT_TYPE_IMAGE) : ?>
                <?php foreach ($options as $option): ?><?php $zIndex++;?><?php endforeach; ?>
                <?php continue; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <div data-sf-render="optionImg">
        <?php if ($defaultImage): ?>
            <img src="<?php echo $defaultImage; ?>" class="shop-filter__viewport__option-img default_image"
                 style="z-index:-1;"/>
        <?php endif; ?>
    </div>
</div>