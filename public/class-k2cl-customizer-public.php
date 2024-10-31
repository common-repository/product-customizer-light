<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link              http://k2-service.com/shop/product-customizer/
 * @author            K2-Service <plugins@k2-service.com>
 *
 * @package           K2CL_Customizer
 * @subpackage        K2CL_Customizer/public
 */
class K2CL_Customizer_Public
{
    const COMPONENT_TYPE_IMAGE = 'image';

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /** @var */
    protected $widget_customizer_id;

    /** @var */
    protected $widget_old_post_id;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param      string $plugin_name The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('k2cl_customizer_widget', array($this, 'get_customizer_widget'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/customizer.css', [], $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '_grid', plugin_dir_url(__FILE__) . 'css/grid.min.css', [], $this->version, 'all');
        $this->get_scripts_by_template(false);
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     */
    public function enqueue_scripts()
    {
        $this->get_scripts_by_template();
	    wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/customizer.js', ['jquery'], $this->version, true);
    }

    /**
     * @param $template
     *
     * @return string
     */
    public function switch_template($template)
    {
        $templatesPath = plugin_dir_path(__FILE__) . '../public/partials/';
        if (is_archive() && get_post_type() == 'k2cl_customizer') {
            return $templatesPath . 'archive/customizer.php';
        } else if (get_post_type() == 'k2cl_customizer') {
            $newTemplate = get_post_meta(get_the_ID(), 'customizer-settings');
            if (empty($newTemplate[0]['template']) || (wp_is_mobile() && $newTemplate[0]['template'] == 'customizer-template-VerticalRightSidebar.php')) {
                return $templatesPath . 'customizer-template-VerticalLeftSidebar.php';
            } else {
                $newTemplate = $newTemplate[0]['template'];
                if (!file_exists($templatesPath . $newTemplate)) {
                    return $templatesPath . 'customizer-template-VerticalLeftSidebar.php';
                }
                return $templatesPath . $newTemplate;
            }
        }
        
        return $template;
    }

    /**
     * @param bool $js
     * @param bool $return_path_only
     *
     * @return string|bool
     */
    public function get_scripts_by_template($js = true, $return_path_only = false)
    {

        $versionMap = [
            'customizer-template-VerticalLeftSidebar.php'    => 'vertical',
        ];
        if (!is_archive() && get_post_type() == 'k2cl_customizer') {
            $template = 'customizer-template-VerticalLeftSidebar.php';
            if (empty($versionMap[$template])) {
                return false;
            }

            if ($js) {
                if ($return_path_only) {
                    return plugin_dir_url(__FILE__) . 'js/template/' . $versionMap[$template] . '.js';
                } else {
                    wp_enqueue_script($this->plugin_name . '_filter_template', plugin_dir_url(__FILE__) . 'js/template/' . $versionMap[$template] . '.js', ['jquery'], $this->version, true);
                }
            } else {
                if ($return_path_only) {
                    return plugin_dir_url(__FILE__) . 'css/template/' . $versionMap[$template] . '.css';
                } else {
                    wp_enqueue_style($this->plugin_name . '_filter_template', plugin_dir_url(__FILE__) . 'css/template/' . $versionMap[$template] . '.css', [], $this->version, false);
                }
            }
        }
    }

    /**
     * @param null $customizer_id
     * @return null
     */
    public static function get_product_id($customizer_id = null)
    {
        $customizer_id = !empty($customizer_id) ? $customizer_id : get_the_ID();
        $settings = get_post_meta($customizer_id, 'customizer-settings');

        return !empty($settings[0]['product_id']) ? $settings[0]['product_id'] : null;
    }

    /**
     * @return array|null|\WP_Post
     */
    public static function get_product_data()
    {
        return get_post(self::get_product_id());
    }

    /**
     * @return mixed
     */
    public static function get_product_meta()
    {
        return get_post_meta(self::get_product_id());
    }

    /**
     * @return false|int
     */
    public static function get_customizer_id()
    {
        return get_the_ID();
    }

    /**
     * @param $product_id
     *
     * @return mixed
     */
    public static function get_customizer_by_product($product_id)
    {
        $customizerList = get_posts(['numberposts' => -1, 'post_type' => 'k2cl_customizer']);
        foreach ($customizerList as $customizer) {
            $meta = self::get_customizer_meta($customizer->ID);
            if (!empty($meta['customizer-settings']['product_id']) &&
                $meta['customizer-settings']['product_id'] == $product_id
            ) {
                return $customizer;
            }
        }

        return false;
    }

    /**
     * @return array|null|\WP_Post
     */
    public static function get_customizer_data()
    {
        return get_post(self::get_customizer_id());
    }

    /**
     * @param null $customizer_id
     *
     * @return mixed
     */
    public static function get_customizer_meta($customizer_id = null)
    {
    	$customizer_id = empty($customizer_id) ? self::get_customizer_id() : $customizer_id;
        $meta = get_post_meta($customizer_id);

        if (!empty($meta[K2CL_Customizer_Admin::CUSTOMIZER_DATA_KEY])) {
            $meta[K2CL_Customizer_Admin::CUSTOMIZER_DATA_KEY] = self::prepare_meta_customizer($meta[K2CL_Customizer_Admin::CUSTOMIZER_DATA_KEY]);
        }

        if (!empty($meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY][0])) {
            $meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY] = unserialize($meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY][0]);
        } elseif (!empty($meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY]) && is_string($meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY])) {
            $meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY] = unserialize($meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY]);
        }

        return $meta;
    }

    /**
     * @param null $customizer_id
     * @return array
     */
    public static function get_customizer_settings($customizer_id = null)
    {
        $meta = self::get_customizer_meta($customizer_id);
        if (!empty($meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY])) {
            return $meta[K2CL_Customizer_Admin::CUSTOMIZER_SETTINGS_KEY];
        }
        return [];
    }

    /**
     * @param $components
     *
     * @return array
     */
    public static function prepare_meta_customizer($components)
    {
        if (!empty($components[0]) && !is_array($components[0])) {
            $components = unserialize($components[0]);
        }

        if (empty($components)) {
            return [];
        }
        foreach ($components as $componentKey => $component) {
            $groups = [];

            if (!empty($component['options'])) {
                foreach ($component['options'] as $optionKey => $option) {
                    if (!isset($option['group_name'])) {
                        $option['group_name'] = '';
                    }
                    $groups[$option['group_name']][$option['option_id']] = $option;

                }
            }
            $components[$componentKey]['groups'] = $groups;
        }

        return $components;
    }

    /**
     *
     */
    public function add_to_cart()
    {
	    if (isset($_POST['add_to_cart']) && wp_verify_nonce($_POST['nonce'], 'add_to_cart')) {
            global $woocommerce;

            unset ($_POST['nonce']);
            unset ($_POST['add_to_cart']);

            $orderedOptions = !empty($_POST['customizer_selected_options']) ? $_POST['customizer_selected_options'] : '';

            $resultOptions = $this->validateSelectedOptions($orderedOptions);
            $attributes = [
                'customizer_id' => self::get_customizer_id(),
                'customizer'    => serialize($resultOptions)
            ];

            /** @var WC_Cart $woocommerce ->cart */
            $woocommerce->cart->add_to_cart(self::get_product_id(), 1, 0, $attributes);
            $cart_url = get_permalink(wc_get_page_id('cart'));
            wp_redirect($cart_url);

            exit();
        }
    }

    /**
     * @param $selectedOptions
     * @return array
     */
    public static function validateSelectedOptions($selectedOptions)
    {
        $resultOptions = [];

        if (is_string($selectedOptions)) {
            $selectedOptions = str_replace('\"', '"', $selectedOptions);
            $selectedOptions = json_decode($selectedOptions, true);
        }

        $customizerOptions = self::get_customizer_meta();
        $customizerOptions = $customizerOptions['customizer'];
        if (empty($customizerOptions)) {
            return $resultOptions;
        }

        // parse type 'image'
        $imageOptions = !empty($selectedOptions['options']) ? $selectedOptions['options'] : [];
        foreach ($imageOptions as $sOption) {
            $params = self::setDefaultParams($sOption, $customizerOptions);
            $resultOptions[$params['component_id']][] = $params;
        }

        return $resultOptions;
    }

    /**
     * @param $optionId
     * @param $customizerOptions
     * @return array
     */
    private static function setDefaultParams($optionId, $customizerOptions)
    {
        $component = [];
        $option = [];
        $arr = [];
        foreach ($customizerOptions as $comp) {
            foreach ($comp['options'] as $opt) {
                if ($opt['option_id'] == $optionId) {
                    $component = $comp;
                    $option = $opt;
                    break;
                }
            }
        }
        if (!empty($option)) {
            $arr = [
                'component_id'     => $component['component_id'],
                'component_type'   => $component['component_type'],
                'component_name'   => $component['component_name'],
                'component_icon'   => $component['component_icon'],
                'option_id'        => $option['option_id'],
                'option_name'      => $option['option_name'],
                'option_price'     => $option['option_price'],
                'option_icon'      => $option['option_icon'],
                'option_image'     => $option['option_image'],
            ];
        }
        return $arr;
    }

    /**
     * @param      $data
     * @param bool $withImages
     * @param bool $withPrice
     *
     * @return string
     */
    public static function get_customizer_options_formatted($data, $withImages = true, $withPrice = false, $isOrder = false)
    {
    	if (empty($data)) return '';

        $img_style = 'style="display:inline; max-height: 24px;"';
        $ul_style = 'style="list-style: none;"';

        $html = '<ul class="components" ' . $ul_style . '>';

        foreach ($data as $component) {
            $options = [];
            $componentName = '';
            $componentImage = '';
            $optionsHtml = '';
            foreach ($component as $option) {
                if (empty($option['option_name'])) {
                    continue;
                }

                $zeroPrice = true;
                if ($isOrder && !$zeroPrice && $option['option_price'] == 0) {
                    continue;
                }

                $optionImage = '';
                if ($withImages) {
                    $optionImage = '<img class="cart_option_icon" src="' . $option['option_icon'] . '" ' . $img_style . ' />';
                }
                $optionsHtml .= '<li class="option">' . $optionImage . ' ' . $option['option_name'];
                if ($withPrice && !empty($option['option_price'])) {
                    $optionsHtml .= ' (' . strip_tags(K2CL_Customizer_Public::customizer_wc_price($option['option_price'])) . ')';
                }
                $optionsHtml .= '</li>';

                $options[] = $option['option_name'];
                $componentName = $option['component_name'];
                $componentImage = $option['component_icon'];
            }
            if (!empty($optionsHtml) && !empty($componentName)) {
                if ($withImages) {
                    $componentImage = '<img class="cart_component_icon" src="' . $componentImage . '" ' . $img_style . ' />';
                } else {
                    $componentImage = '';
                }
                $html .= '<li class="component">' . $componentImage . ' ' . $componentName .
                    '<ul class="options" ' . $ul_style . '>' . $optionsHtml . '</ul></li>';
            }
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * @param $cart_item
     *
     * @return float
     */
    public function calculate_product_price($cart_item)
    {
	    $changes = $cart_item['data']->get_changes();
		if (!empty($changes['price'])) {
			return $changes['price'];
		}
        $price = 0;
        if (!empty($cart_item['data'])) {
            $price = (float)$cart_item['data']->get_price();
        }
        $customizer_data = '';
        if (isset($cart_item['variation']['customizer'])) {
            $customizer_data = $cart_item['variation']['customizer'];
        }
        if (isset($cart_item['variation']['attribute_customizer'])) {
            $customizer_data = $cart_item['variation']['attribute_customizer'];
        }
        $customizer_data = unserialize($customizer_data);
        foreach ($customizer_data as $component) {
            foreach ($component as $option) {
                $price += (float)$option['option_price'];
            }
        }

        return $price;
    }

    /**
     * @param boolean $withDefaultOptions
     *
     * @return float
     */
    public static function calculate_default_price($withDefaultOptions = true)
    {
        $price = 0;
        $product = null;

        if (self::is_woocommerce_enabled()) {
            $productId = self::get_product_id();
            if (empty($productId)) {
                return 0;
            }

            $product = wc_get_product($productId);
            if ($product) {
                $price = !empty($customizer_settings['base_price']) ? $customizer_settings['base_price'] : wc_get_price_to_display($product);
            }
        }
        if ($withDefaultOptions) {
            $data = self::get_customizer_meta();
            if (empty($data['customizer'])) {
                return $price;
            }
            foreach ($data['customizer'] as $component) {
                if (empty($component['options'])) {
                    continue;
                }
                foreach ($component['options'] as $option) {
                    if (!empty($option['option_default'])) {
                        $opt_price = (self::is_woocommerce_enabled() && $product) ? wc_get_price_to_display($product,['price' => $option['option_price']]) : $option['option_price'];
                        $price += $opt_price;
                    }
                }
            }
        }

        return $price;
    }

    /**
     * @param $cart_item
     *
     * @return mixed
     */
    public function force_individual_items($cart_item)
    {
        $unique_cart_item_key = md5(microtime() . rand() . rand(1000, 9999));
        $cart_item['unique_key'] = $unique_cart_item_key;

        return $cart_item;
    }

    /**
     * @param $cart_object
     */
    public function change_price($cart_object)
    {
        foreach ($cart_object->cart_contents as $key => $value) {
            if (self::get_customizer_id_from_item($value)) {
                $newPrice = $this->calculate_product_price($value);
	            if (self::isWooCommerce3_3()) {
		            $value['data']->set_price($newPrice);
	            } elseif (self::isWooCommerce3()) {
                    $value['data']->set_price($newPrice);
                } else {
                    $value['data']->price = $newPrice;
                }
            }
        }
    }

    /**
     * @param $name
     * @param $order_item
     *
     * @return string
     */
    public function get_item_data_order($name, $order_item)
    {
        return $this->get_item_data($name, $order_item, true);
    }

    /**
     * @param $name
     * @param $cart_item
     * @param $order
     *
     * @return string
     */
    public function get_item_data($name, $cart_item, $order = false)
    {
        $data = self::get_customizer_data_from_item($cart_item);
        $html = '';
        if (empty($data)) {
            $link = get_post_permalink($cart_item['product_id']);
        } else {
            if ($order !== true) {
                $html = $this->get_customizer_options_formatted($data);
            }

            $customizerId = self::get_customizer_id_from_item($cart_item);
            $customizerInfo = get_post($customizerId);
            $link = self::get_customizer_link($customizerId);
            $name = $customizerInfo->post_title;
        }

        $result = '<a href="' . $link . '">' . $name . '</a>';
        if ($order !== true) {
            $result .= '<div class="customizer-options">' . $html . '</div>';

        }

        return $result;
    }

    /**
     * @param $item
     * @return null
     */
    public static function get_customizer_id_from_item($item)
    {
        $customizerId = null;
        if (!empty($item['variation']['customizer_id'])) {
            $customizerId = $item['variation']['customizer_id'];
        }

        if (!empty($item['variation']['attribute_customizer_id'])) {
            $customizerId = $item['variation']['attribute_customizer_id'];
        }

        if (!empty($item['customizer_id'])) {
            $customizerId = $item['customizer_id'];
        }

        if (!empty($item['attribute_customizer_id'])) {
            $customizerId = $item['attribute_customizer_id'];
        }

        return $customizerId;
    }

    /**
     * @param $item
     * @return null
     */
    public static function get_customizer_data_from_item($item)
    {
        $customizer = null;
        if (!empty($item['variation']['customizer'])) {
            $customizer = $item['variation']['customizer'];
        }

        if (!empty($item['variation']['attribute_customizer'])) {
            $customizer = $item['variation']['attribute_customizer'];
        }

        if (!empty($item['customizer'])) {
            $customizer = $item['customizer'];
        }

        if (!empty($item['attribute_customizer'])) {
            $customizer = $item['attribute_customizer'];
        }

        if (!is_array($customizer)) {
            $customizer = unserialize($customizer);
        }

        return $customizer;

    }

    /**
     * Updating mata tags in order
     *
     * @param $post_id
     */
    public function add_item_meta($post_id)
    {
        /** @var WC_Order $order */
        $order = wc_get_order($post_id);
        foreach ($order->get_items() as $item_id => $item) {
            $customizer_components = unserialize(wc_get_order_item_meta($item_id, 'k2cl_customizer'));
            if (is_array($customizer_components)) {
                foreach ($customizer_components as $component) {
                    $componentName = '';
                    $selectedOptions = [];
                    foreach ($component as $option) {
                        $selectedOptions[] = $option['option_name'] . ' (' . strip_tags(K2CL_Customizer_Public::customizer_wc_price($option['option_price'])) . ')';
                        $componentName = $option['component_name'];
                    }
                    if (!empty($selectedOptions) && $componentName) {
                        wc_add_order_item_meta($item_id, $componentName, implode(', ', $selectedOptions));
                    }
                }
            }
        }
    }

    /**
     * @param $product_image_code
     * @param $item
     * @param $cart_item_key
     *
     * @return string
     */
    public static function get_customizer_image($product_image_code, $item, $cart_item_key)
    {
        //image in shipping cart
        $data = self::get_customizer_data_from_item($item['variation']);
        if (empty($data)) {
            return $product_image_code;
        }
        $customizer_id = self::get_customizer_id_from_item($item);
        $link = get_post_permalink($customizer_id);
        $html = '</a><a href="' . $link . '"><div class="customizer_cart_thumb">';

        $images = self::get_all_customizer_images($data);

        foreach ($images as $option_id => $image) {
        	$html .= '<img src="' . $image . '" />';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param      $customizerData
     * @param bool $onlyDefault
     * @param bool $listing
     * @return array
     */
    public static function get_all_customizer_images($customizerData, $onlyDefault = false, $listing = false)
    {
        $images = [];
        if ($listing) {
            $defaultImage = self::get_default_listing_image();
        } else {
            $defaultImage = self::get_default_image('full');
        }

        if ($defaultImage) {
            $images[] = $defaultImage;
        }
        if ($onlyDefault) return $images;

        foreach ($customizerData as $component) {
            if (!empty($component['options'])) {
                $component = $component['options'];
            }
            foreach ($component as $option) {
                if ($onlyDefault && empty($option['option_default'])) {
                    continue;
                }
                if (!empty($option['option_image'])) {
                	$images[$option['option_id']] = $option['option_image'];
                }
            }
        }

        return $images;
    }

    public function item_meta_display($output, $meta)
    {
        if (!empty($meta->meta['customizer_id'][0])) {
            return '';
        }

        return $output;
    }

    /**
     * @param $item_id
     * @param $item
     * @param $order
     * @return string
     */
    public function email_order_item_meta($item_id, $item, $order)
    {
        $html = '';
        if (is_order_received_page()) {
            return '';
        }
        $data = self::get_customizer_data_from_item($item);

        if (empty($data)) {
            return '';
        }

        $html .= '<div class="customizer_meta">';
        //generate image
        $html .= self::generateImage($item_id, $item, true);
        $html .= self::get_customizer_options_formatted($data, true, true, true);
        $html .= '</div>';

        echo $html;
    }

    public function hide_woocommerce_items_order($html, $item, $args)
    {
        if (is_order_received_page()) {
            $item = self::get_customizer_data_from_item($item);
            echo self::get_customizer_options_formatted($item, true, true, true);
        } else
            return '';
    }
    /**
     * @param      $itemId
     * @param      $item
     * @param bool $forceImage
     * @return string
     */
    public static function generateImage($itemId, $item, $forceImage = false)
    {
        $data = self::get_customizer_data_from_item($item);
        $images = self::get_all_customizer_images($data);

        $path = '/uploads/customizer/';
        $name = $itemId . '.jpg';
        $url = get_home_url() . '/wp-content' . $path . $name;
        $file = ABSPATH . 'wp-content' . $path . $name;
        if (file_exists($file)) {
            return '<img width="300" src="' . $url . '"/>';
        }

        if (empty($images)) {
            return '';
        }

        $uploadFolder = WP_CONTENT_DIR . $path;

        if (!file_exists($uploadFolder)) {
            wp_mkdir_p($uploadFolder);
        }
        list($width, $height) = self::getMaxWidthHeight($images);

        $canvas = imagecreatetruecolor($width, $height);
        imagesavealpha($canvas, true);

        $transLayerOverlay = imagecolorallocatealpha($canvas, 225, 225, 225, 127);
        imagefill($canvas, 0, 0, $transLayerOverlay);

        foreach ($images as $option_id => $image) {
            $image = str_replace(self::get_domain_names(), '', $image);
             if (!file_exists(ABSPATH . $image)) {
             	continue;
             }

             if (substr($image, -3) == 'gif') {
             	$pngNameArr = explode('/', $image);
             	$pngName = end($pngNameArr);

             	$pngName = str_replace('gif', 'png', $pngName);
             	array_pop($pngNameArr);
             	$pngNameArr[] = $pngName;
             	$pngName = implode('/', $pngNameArr);

             	imagepng(imagecreatefromstring(file_get_contents(ABSPATH . $image)), ABSPATH . $pngName);
             	$image = $pngName;
             }

             $image = ABSPATH . $image;

             $img = false;
             if (is_file($image) && mime_content_type($image) == 'image/png') {
             	$img = imagecreatefrompng($image);
             } elseif (is_file($image) && mime_content_type($image) == 'image/jpeg') {
             	$img = imagecreatefromjpeg($image);
             }

             list($iWidth, $iHeight) = getimagesize($image);
             if ($img) {
             	imagecopyresampled($canvas, $img, 0, 0, 0, 0, $iWidth, $iHeight, $iWidth, $iHeight);
             }
        }

        $name = $itemId . '.png';
        imagepng($canvas, $uploadFolder . '/' . $name, 9);

//        save as jpg
        $input_file = $uploadFolder . '/' . $name;
        $output_file = $uploadFolder . '/' . $itemId . '.jpg';
        $input = imagecreatefrompng($input_file);
        list($width, $height) = getimagesize($input_file);
        $output = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($output, 255, 255, 255);
        imagefilledrectangle($output, 0, 0, $width, $height, $white);
        imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
        imagejpeg($output, $output_file);
        unlink($input_file);

        return '<img width="300" src="' . $url . '" />';
    }


    /**
     * @param $item
     * @param $orderImageWidth
     * @return string
     */
    public static function generateHtmlImage($item, $orderImageWidth = 300)
    {
        $data = self::get_customizer_data_from_item($item);
        $images = self::get_all_customizer_images($data);

        list($width, $height) = self::getMaxWidthHeight($images);
        $newHeight = round($orderImageWidth * $height / $width);
        $zIndex = 1;
        $html = '<div style="position:relative; height:' . $newHeight . 'px; width:' . $orderImageWidth . 'px;">';
        $html .= '<div data-sf-render="optionImg" style="height:' . $height . 'px; z-index:-1;">';

        foreach ($images as $image) {
        	$html .= '<img width="' . $orderImageWidth . '" src="' . $image . '" style="position:absolute;" z-index="' . $zIndex++ . '" />';
        }
        $html .= '</div></div>';

        return $html;
    }

    /**
     * @param $images
     * @return array|string
     */
    public static function getMaxWidthHeight($images)
    {
        $width = 0;
        $height = 0;
        foreach ($images as $image) {
            if (!empty($image)) {
                $image = str_replace(self::get_domain_names(), '', $image);
                if (!file_exists(ABSPATH . $image)) {
                    continue;
                }

                list($tempWidth, $tempHeight) = getimagesize(ABSPATH . $image);
                if ($width < $tempWidth) {
                    $width = $tempWidth;
                }
                if ($height < $tempHeight) {
                    $height = $tempHeight;
                }
            }
        }
        return [$width, $height];
    }

    /**
     * @param string $size
     * @param null   $customizer_id
     * @param bool   $listing
     * @return string
     */
    public static function get_default_image($size = 'medium', $customizer_id = null, $listing = false)
    {
        $defaultImage = '';
        $customizer_settings = self::get_customizer_settings($customizer_id);

        if ($listing) {
            $useProductImage = !empty($customizer_settings['use_product_image_listing']) ? $customizer_settings['use_product_image_listing'] : 0;
        } else {
            $useProductImage = !empty($customizer_settings['use_product_image_single']) ? $customizer_settings['use_product_image_single'] : 0;
        }

        if ($useProductImage) {
            $productId = self::get_product_id($customizer_id);
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($productId), $size);
            $defaultImage = !empty($image[0]) ? $image[0] : '';
        }

        return $defaultImage;
    }

    /**
     * @return string
     */
    public static function get_default_listing_image()
    {
        return self::get_default_image('medium', null, true);
    }

    /** Prepare button on product page */
    public function get_customizer_button()
    {
        $post_id = get_the_ID();
        $customizer = self::get_customizer_by_product($post_id);
        if ($customizer) {
        	echo '<style type="text/css">.quantity,.cart button[type="submit"]{display:none;}</style>';
        	echo '<a href="' . get_permalink($customizer->ID) . '" class="single_add_to_cart_button button alt">' . __('Customize',
			        'k2cl_customizer') . '</a>';
        }
    }

	/**
	 * @param        $customizer_id
	 * @param string $hash
	 *
	 * @return string|\WP_Error
	 */
	public static function get_customizer_link($customizer_id, $hash = null)
	{
		$link = get_post_permalink($customizer_id);
		return $link;
	}

	/**
     * @param      $item_id
     * @param      $option_id
     * @param bool $text
     * @return array
     */
    public static function get_selected_option($item_id, $option_id, $text = true)
    {
        $customizer_components = unserialize(wc_get_order_item_meta($item_id, 'k2cl_customizer'));
        foreach ($customizer_components as $component) {
            foreach ($component as $option) {
                if ($option['option_id'] == $option_id) {
                    return $option;
                }
            }
        }
        return [];
    }

    /**
     * @return array
     */

    public static function get_domain_names()
    {
        //hotfix for test sites
        return [
            get_home_url() . '/',
            'http://wordpress.k2-service.com/',
            'http://v2.wordpress.k2-service.com/',
        ];
    }

    /**
     * @return bool
     */
    public static function is_woocommerce_enabled()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        return is_plugin_active('woocommerce/woocommerce.php');
    }

    /**
     * @param $price
     * @return string
     */
    public static function customizer_wc_price($price)
    {
        if (self::is_woocommerce_enabled()) {
            $pr = $price;
            $productId = self::get_product_id();
            if (!empty($productId)) {
                $product = wc_get_product($productId);
                if ($product) {
                    $pr = wc_get_price_to_display($product, ['price' => $price]);
                }
            }
            return wc_price($pr);
        }
        return $price;
    }

    /**
     * @return bool
     */
    public static function can_show_add_to_cart()
    {
        $showAddToCart = false;
        $customizer_settings = self::get_customizer_settings();

        if (self::is_woocommerce_enabled()
            && !empty($customizer_settings['add-to-cart_show'])
            && !empty($customizer_settings['product_id'])
        ) {
            $showAddToCart = true;
        }

        return $showAddToCart;
    }

    /**
     * @param $formatted_meta
     * @param $order_item
     * @return array
     */
    public function formatted_meta_data($formatted_meta, $order_item)
    {
        $new_formatted_meta = array();
        foreach ($formatted_meta as $meta_id => $meta) {
            if (!in_array($meta->key, array('customizer_id', 'k2cl_customizer'))) {
                $new_formatted_meta[$meta_id] = $meta;
            }
        }
        return $new_formatted_meta;
    }

    /**
     * @return bool
     */
    public static function isWooCommerce3()
    {
        if (class_exists('WooCommerce') && (version_compare(WC()->version, '3.0.0', ">"))) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isWooCommerce3_3()
    {
        if (class_exists('WooCommerce') && (version_compare(WC()->version, '3.3.0', ">="))) {
            return true;
        }

        return false;
    }

	/**
     * @param array $params
     * @param null  $content
     * @return mixed|null|string|void
     */
    function get_customizer_widget($params, $content = null)
    {
        global $post;
        $customizer_id = !empty($params['customizer_id']) ? $params['customizer_id'] : (!empty($params['id']) ? $params['id'] : '');
        $customizer = get_post($customizer_id);

        if (!$customizer) {
            return apply_filters('insert_pages_not_found_message', $content);
        }
        $this->widget_customizer_id = $customizer_id;
        $this->widget_old_post_id = get_the_ID();

        $post = $customizer;
        $template = $this->switch_template('');

        ob_start();
        echo '<div class="customizer_widget">';
        include_once $template;
        echo '</div>';

        $js = $this->get_scripts_by_template(true, true);
        $css = $this->get_scripts_by_template(false, true);
        echo '<script type="text/javascript" src="' . $js . '"></script>';
        echo '<style type="text/css">';
        echo file_get_contents($css);
        echo '</style>';
        $content = ob_get_contents();
        ob_end_clean();

        $post = get_post($this->widget_old_post_id);

        return $content;
    }

}
