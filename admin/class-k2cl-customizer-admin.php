<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link              http://k2-service.com/shop/product-customizer/
 * @author            K2-Service <plugins@k2-service.com>
 *
 * @package           K2CL_Customizer
 * @subpackage        K2CL_Customizer/admin
 */
class K2CL_Customizer_Admin
{

    const CUSTOMIZER_SETTINGS_KEY = 'customizer-settings';

    const CUSTOMIZER_DATA_KEY = 'customizer';

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

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/customizer.min.css', [], $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '_grid', plugin_dir_url(__FILE__) . 'css/grid.min.css', [], $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '_modal', plugin_dir_url(__FILE__) . 'css/modal.min.css', [], $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '_jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/customizer.js', ['jquery', 'jquery-ui-core', 'jquery-ui-sortable'], $this->version, false);
        wp_enqueue_script($this->plugin_name . '_modal', plugin_dir_url(__FILE__) . 'js/modal.min.js', ['jquery'], $this->version, false);
        wp_enqueue_script($this->plugin_name . '_jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', ['jquery'], '1.12.1', false);
    }

    /**
     * @param $columns
     *
     * @return mixed
     */
    public function get_customizer_screen_layout_columns($columns)
    {
        $columns['k2cl_customizer'] = 1;

        return $columns;
    }

    /**
     * @return int
     */
    public function get_screen_layout_customizer()
    {
        return 1;
    }

    /**
     * @param $order
     *
     * @return mixed
     */
    public function get_meta_order($order)
    {

        $order['advanced'] = 'customizer-preview-box,customizer-settings-box';

        return $order;
    }

	public function set_saved_screen_option($status, $option, $value)
	{
		return $status;
	}

    /**
     * Register new post type
     */
    public function register_k2cl_customizer_post_type()
    {
        $labels = [
            'name'               => _x('Customizer', 'k2cl_customizer'),
            'singular_name'      => _x('Customizers', 'k2cl_customizer'),
            'add_new'            => _x('New Customizer', 'k2cl_customizer'),
            'add_new_item'       => _x('New Customizer', 'k2cl_customizer'),
            'edit_item'          => _x('Edit Customizer', 'k2cl_customizer'),
            'new_item'           => _x('New Customizer', 'k2cl_customizer'),
            'view_item'          => _x('View Customizer', 'k2cl_customizer'),
            'not_found'          => _x('No customizer found', 'k2cl_customizer'),
            'not_found_in_trash' => _x('No customizer in the trash', 'k2cl_customizer'),
            'menu_name'          => _x('Customizer', 'k2cl_customizer'),
            'all_items'          => _x('All Customizers', 'k2cl_customizer'),
        ];

        $args = [
            'labels'              => $labels,
            'hierarchical'        => false,
            'description'         => 'Customizers',
            'supports'            => ['title'],
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'map_meta_cap'        => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'query_var'           => true,
            'has_archive'         => true,
            'rewrite'             => ['slug' => 'customizer'],
            'can_export'          => true,
            'menu_icon'           => 'dashicons-images-alt2',
        ];

        register_post_type('k2cl_customizer', $args);
        flush_rewrite_rules();
    }

    /**
     * Get menu items for Product Customizer
     */
    public function get_customizer_menu()
    {
        $parent_slug = 'edit.php?post_type=k2cl_customizer';

        add_submenu_page(
            $parent_slug,
            __('Export', 'k2cl_customizer'),
            __('Export', 'k2cl_customizer'),
            'manage_product_terms',
            'customizer-export',
            [
                $this,
                'get_customizer_export_page'
            ]
        );

    }

	public function get_customizer_export_page()
    {
        set_time_limit(0);
        $export_val = [];
        $zip_arr = [];
        $upload_dir = wp_upload_dir();

        if (!empty($_POST['export'])) {
            $exports = $_POST['export'];
            foreach ($exports as $context) {
                switch ($context) {
                    case 'main' :
                        $export_val[] = ['type' => 'main', 'value' => []];
                        break;
                    case 'customizers' :
                        $customizers = get_posts(['numberposts' => 0, 'post_type' => 'customizer']);
                        $cust_arr = [];
                        foreach ($customizers as $customizer) {
                            $components = K2CL_Customizer_Public::get_customizer_meta($customizer->ID);
                            $cust_arr[] = ['inf' => $customizer, 'components' => $components];
                        }
                        $export_val[] = ['type' => 'customizer', 'value' => $cust_arr];
                        break;
                    case 'images' :
                        $customizers = get_posts(['numberposts' => 0, 'post_type' => 'customizer']);
                        $path_arr = [];
                        if (extension_loaded('zip')) {
                            foreach ($customizers as $customizer) {
                                $components = K2CL_Customizer_Public::get_customizer_meta($customizer->ID);
                                foreach ($components['customizer'] as $id_component => $cust) {
                                    if (!empty($cust['component_icon'])) {
                                        $path = pathinfo(str_replace($upload_dir['baseurl'], '', $cust['component_icon']));
                                        $file_name = $path['basename'];
                                        $zip_arr[] = [
                                            'real_name' => $upload_dir['basedir'] . $path['dirname'] . '/' . $file_name,
                                            'zip_name'  => $path['dirname'] . '/' . $file_name
                                        ];

                                        $path_arr[] = [
                                            'customizer_title' => $customizer->post_title,
                                            'component_id'     => $id_component,
                                            'id'               => $id_component,
                                            'type'             => 'component',
                                            'name'             => 'component_icon',
                                            'path'             => $path['dirname'] . '/' . $file_name
                                        ];
                                    }
                                    foreach ($cust['options'] as $id_option => $option) {
                                        if (!empty($option['option_icon'])) {
                                            $path = pathinfo(str_replace($upload_dir['baseurl'], '', $option['option_icon']));
                                            $file_name = $path['basename'];

                                            $zip_arr[] = [
                                                'real_name' => $upload_dir['basedir'] . $path['dirname'] . '/' . $file_name,
                                                'zip_name'  => $path['dirname'] . '/' . $file_name
                                            ];
                                            $path_arr[] = [
                                                'customizer_title' => $customizer->post_title,
                                                'component_id'     => $id_component,
                                                'id'               => $id_option,
                                                'type'             => 'option',
                                                'name'             => 'option_icon',
                                                'path'             => $path['dirname'] . '/' . $file_name
                                            ];
                                        }
                                        if (!empty($option['option_image'])) {
                                            $path = pathinfo(str_replace($upload_dir['baseurl'], '', $option['option_image']));
                                            $file_name = $path['basename'];

                                            $zip_arr[] = [
                                                'real_name' => $upload_dir['basedir'] . $path['dirname'] . '/' . $file_name,
                                                'zip_name'  => $path['dirname'] . '/' . $file_name
                                            ];
                                            $path_arr[] = [
                                                'customizer_title' => $customizer->post_title,
                                                'component_id'     => $id_component,
                                                'id'               => $id_option,
                                                'type'             => 'option',
                                                'name'             => 'option_image',
                                                'path'             => $path['dirname'] . '/' . $file_name
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                        $export_val[] = ['type' => 'images', 'value' => $path_arr];
                        break;
                }
            }
            $export_val = json_encode($export_val);
            $file_name = 'settings.json';
            $zip = new ZipArchive();
            $zip_name = 'customizer_' . date('Y-m-d_H-i-s') . '.zip';
            $zip->open($upload_dir['path'] . '/' . $zip_name, ZIPARCHIVE::CREATE);

            foreach ($zip_arr as $files) {
                $zip->addFile($files['real_name'], $files['zip_name']);
            }
            $zip->addFromString($file_name, $export_val);
            $zip->close();

            wp_redirect(plugin_dir_url(__FILE__) . 'partials/customizer-admin-export-page.php?file_name=' . $zip_name . '&dir=' . $upload_dir['path']);
            exit;
        }


        $html = '<h1>' . __('Export', 'k2cl_customizer') . '</h1>';

        $html .= '<div class="customizer-admin-wrap postbox"><h2>' . __('Export', 'k2cl_customizer') . '</h2>';
        $html .= '<form name="export_form" action="" method="post" class="mg-top">';
        $html .= '<table class="wp-list-table widefat fixed pages o-root"><tr>';
        $html .= '<td class="label"><h3>' . __('Select Entities', 'k2cl_customizer') . '</h3><ul>';
        $html .= '<li><label><input id="customizer_import_mark_all" type="checkbox" checked="checked">' . __('Mark / Unmark all entities', 'k2cl_customizer') . '</label></li>';
        $html .= '<li><br /></li>';
        $html .= '<li><label><input class="import_entity_type" name="export[]" type="checkbox" value="main" checked="checked">' . __('Main settings', 'k2cl_customizer') . '</label></li>';
        $html .= '<li><label><input class="import_entity_type" name="export[]" type="checkbox" value="customizers" checked="checked">' . __('Customizers', 'k2cl_customizer') . '</label></li>';
        $html .= '<li><label><input class="import_entity_type" name="export[]" type="checkbox" value="images" checked="checked">' . __('Images', 'k2cl_customizer') . '</label></li>';
        $html .= '<li>&nbsp;</li><li><input name="button_export" type="submit" class="button button-primary button-large" value="' . __('Export', 'k2cl_customizer') . '"></li></ul></td><td></td>';
        $html .= '</td></tr></table></form></div>';
        $html .= '';

        echo $html;
    }

    public function get_config_box()
    {
        add_meta_box(
            'customizer-preview-box',
            __('Customizer Preview', 'k2cl_customizer'),
            [$this, 'get_customizer_preview_page'],
            'k2cl_customizer'
        );

        add_meta_box(
            'customizer-settings-box',
            __('Customizer settings', 'k2cl_customizer'),
            [$this, 'get_customizer_settings_box'],
            'k2cl_customizer'
        );
    }

    /**
     * Block with preview for each customizer
     *
     * @return string
     */
    public function get_customizer_preview_page()
    {
        echo '<div id="customizer-preview"></div>';
    }

    /**
     * @return array
     */
    public function get_templates_list()
    {
        $result = [];
        $templatesPath = plugin_dir_path(__FILE__) . '../public/partials/';
        foreach (scandir($templatesPath) as $index => $file) {
            if (is_dir($templatesPath . $file)) {
                continue;
            }
            $name = str_replace(['customizer-template-', '.php'], ['', ''], $file);
            if (substr($name, 0, 1) == '_') {
                continue;
            }

            $name = ucfirst($name);
            preg_match_all('/((?:^|[A-Z])[a-z]+)/', $name, $matches);
            if (!empty($matches[0])) {
                $name = implode(' ', $matches[0]);
            }
            $result[$file] = $name;
        }

        unset ($result['.']);
        unset ($result['..']);

        return $result;
    }

    /**
     * settings for each customizer
     */
    public function get_customizer_settings_box()
    {
        wp_enqueue_media();
        $html = '<div class="block-form">';

        $yesNoOptions = [
            0 => __('No', 'k2cl_customizer'),
            1 => __('Yes', 'k2cl_customizer')
        ];

        $templateValues = $this->get_templates_list();

        $allProducts = $this->get_all_products();

        $savedSettings = get_post_meta(get_the_ID(), self::CUSTOMIZER_SETTINGS_KEY);
        $savedSettings = !empty($savedSettings[0]) ? $savedSettings[0] : [];

        $productOptions = [
            'title'       => __('Product', 'k2cl_customizer'),
            'name'        => 'customizer-settings[product_id]',
            'options'     => $allProducts,
            'default'     => !empty($savedSettings['product_id']) ? $savedSettings['product_id'] : '',
            'class'       => 'chosen_select_nostd customizer_settings_product_id',
            'description' => __('Select product for customization.', 'k2cl_customizer'),
        ];

        $useProductImageListing = [
            'title'       => __('Use Product Image On Listing', 'k2cl_customizer'),
            'name'        => 'customizer-settings[use_product_image_listing]',
            'options'     => $yesNoOptions,
            'default'     => !empty($savedSettings['use_product_image_listing']) ? $savedSettings['use_product_image_listing'] : 0,
            'class'       => 'chosen_select_nostd customizer_settings_standart',
            'description' => __('Use product image as base image for this customizer on listing', 'k2cl_customizer'),
        ];

        $useProductImageSingle = [
            'title'       => __('Use Product Image On Single Page', 'k2cl_customizer'),
            'name'        => 'customizer-settings[use_product_image_single]',
            'options'     => $yesNoOptions,
            'default'     => !empty($savedSettings['use_product_image_single']) ? $savedSettings['use_product_image_single'] : 0,
            'class'       => 'chosen_select_nostd customizer_settings_standart',
            'description' => __('Use product image as base image for this customizer on single page', 'k2cl_customizer'),
        ];

        $addToCartButton = [
            'title'       => __('Add to cart', 'k2cl_customizer'),
            'name'        => 'customizer-settings[add-to-cart_show]',
            'options'     => $yesNoOptions,
            'default'     => isset($savedSettings['add-to-cart_show']) ? $savedSettings['add-to-cart_show'] : 1,
            'class'       => 'chosen_select_nostd customizer_settings_standart',
            'description' => __('Display "Add to cart" button', 'k2cl_customizer'),
        ];

        $componentsHideShowOption = [
            'title'       => __('Open by default', 'k2cl_customizer'),
            'name'        => 'customizer-settings[all_open]',
            'options'     => $yesNoOptions,
            'default'     => !empty($savedSettings['all_open']) ? $savedSettings['all_open'] : 0,
            'class'       => 'chosen_select_nostd customizer_settings_standart',
            'description' => __('Display all components open by default', 'k2cl_customizer'),
        ];

        $templateOptions = [
            'title'       => __('Template', 'k2cl_customizer'),
            'name'        => 'customizer-settings[template]',
            'options'     => $templateValues,
            'default'     => !empty($savedSettings['template']) ? $savedSettings['template'] : 0,
            'class'       => 'chosen_select_nostd customizer-settings-template',
            'description' => __('Customizer look on Frontend', 'k2cl_customizer'),
        ];

        //Customizer settings

        $html .= $this->get_wrap_html();
        $html .= '<tr>';
        $html .= $this->add_admin_element($templateOptions, 'hidden');
        $html .= '</tr><tr>';
        $html .= $this->add_admin_element($componentsHideShowOption, 'select');
        $html .= '</tr><tr>';
        $html .= $this->add_admin_element($addToCartButton, 'select');
        $html .= '</tr><tr>';
        if (K2CL_Customizer_Public::is_woocommerce_enabled()) {
            $html .= $this->add_admin_element($productOptions, 'select');
            $html .= '</tr><tr>';
        }
        $html .= $this->add_admin_element($useProductImageListing, 'select');
        $html .= '</tr><tr>';
        $html .= $this->add_admin_element($useProductImageSingle, 'select');
        $html .= '</tr>';
        $html .= $this->get_wrap_html(false);

        $componentId = [
            'title' => __('ID', 'k2cl_customizer'),
            'name'  => 'component_id',
            'type'  => 'text',
            'class' => 'customizer-component-id',
        ];

        $componentType = [
            'title'    => __('Type', 'k2cl_customizer'),
            'name'     => 'component_type',
            'type'     => 'select',
            'options'  => [
	            K2CL_Customizer_Public::COMPONENT_TYPE_IMAGE        => __('Image', 'k2cl_customizer'),
            ],
            'default'  => '',
            'class'    => 'chosen_select_nostd customizer-component-type',
            'td_style' => 'width:70px;'
        ];

        $componentName = [
            'title'       => __('Name', 'k2cl_customizer'),
            'name'        => 'component_name',
            'type'        => 'text',
            'class'       => 'customizer-component-name',
            'description' => __('Component name', 'k2cl_customizer'),
        ];

        $componentMultiple = [
            'title'    => __('Multiple', 'k2cl_customizer'),
            'name'     => 'multiple',
            'type'     => 'select',
            'options'  => $yesNoOptions,
            'class'    => 'customizer-multiple',
            'default'  => '0',
            'td_style' => 'width:60px;'
        ];

        $componentImage = [
            'title'       => __('Icon', 'k2cl_customizer'),
            'name'        => 'component_icon',
            'url_name'    => 'customizer_component_icon',
            'type'        => 'image',
            'description' => __('Component Icon', 'k2cl_customizer'),
            'td_style'    => 'width:110px;'
        ];

        $componentEnable = [
            'title'    => __('Enable', 'k2cl_customizer'),
            'name'     => 'component_enable',
            'type'     => 'select',
            'options'  => $yesNoOptions,
            'class'    => 'customizer-enable',
            'default'  => '1',
            'td_style' => 'width:60px;'
        ];

        $optionId = [
            'title' => __('ID', 'k2cl_customizer'),
            'name'  => 'option_id',
            'type'  => 'text',
            'class' => 'customizer-option-id'
        ];

        $optionGroup = [
            'title'    => __('Group', 'k2cl_customizer'),
            'name'     => 'group_name',
            'type'     => 'text',
            'class'    => 'customizer-group-name',
            'td_style' => 'width:100px;',
            'td_class' => 'image_option'
        ];

        $optionName = [
            'title'       => __('Name', 'k2cl_customizer'),
            'name'        => 'option_name',
            'type'        => 'text',
            'class'       => 'customizer-option-name',
            'description' => __('Option name', 'k2cl_customizer'),
        ];

        $optionDescription = [
            'title'       => __('Description', 'k2cl_customizer'),
            'name'        => 'option_description',
            'type'        => 'textarea',
            'class'       => 'customizer-option-description',
            'description' => __('Option description', 'k2cl_customizer'),
            'td_class'    => 'image_option'
        ];

        $optionPrice = [
            'title'       => __('Price', 'k2cl_customizer'),
            'name'        => 'option_price',
            'type'        => 'text',
            'class'       => 'customizer-option-price',
            'description' => __('Option Price', 'k2cl_customizer'),
            'td_style'    => 'width:70px;',
	        'price'       => true
        ];

        $optionIcon = [
            'title'       => __('Icon', 'k2cl_customizer'),
            'name'        => 'option_icon',
            'url_name'    => 'customizer_option_icon',
            'type'        => 'image',
            'set'         => 'Set',
            'remove'      => 'Remove',
            'description' => __('Option Icon', 'k2cl_customizer'),
            'td_style'    => 'width:110px;',
            'td_class'    => 'image_option'
        ];

        $optionImage = [
            'title'       => __('Image', 'k2cl_customizer'),
            'name'        => 'option_image',
            'url_name'    => 'customizer_option_image',
            'type'        => 'image',
            'set'         => 'Set',
            'remove'      => 'Remove',
            'class'       => 'customizer-option-image',
            'description' => __('Component Image', 'k2cl_customizer'),
            'td_style'    => 'width:110px;',
            'td_class'    => 'image_option'
        ];

        $optionEnable = [
            'title'    => __('Enable', 'k2cl_customizer'),
            'name'     => 'option_enable',
            'type'     => 'select',
            'options'  => $yesNoOptions,
            'class'    => 'customizer-enable',
            'default'  => '1',
            'td_style' => 'width:50px;'
        ];

        $optionDefault = [
            'title'    => __('Default', 'k2cl_customizer'),
            'name'     => 'option_default',
            'type'     => 'select',
            'options'  => $yesNoOptions,
            'class'    => 'customizer-default',
            'default'  => '0',
            'td_style' => 'width:50px;',
            'td_class' => 'image_option'
        ];


        $optionsFields = [
            'title'        => __('Options', 'k2cl_customizer'),
            'name'         => 'options',
            'class'        => 'image_options',
            'fields'       => [
                ['type' => 'hidden', 'data' => $optionId],
                ['type' => 'text', 'data' => $optionGroup],
                ['type' => 'text', 'data' => $optionName],
                ['type' => 'textarea', 'data' => $optionDescription],
                ['type' => 'number', 'data' => $optionPrice],
                ['type' => 'image', 'data' => $optionIcon],
                ['type' => 'image', 'data' => $optionImage],
                ['type' => 'select', 'data' => $optionEnable],
                ['type' => 'select', 'data' => $optionDefault]
            ],
            'description'  => __('Customizer options', 'k2cl_customizer'),
            'row_class'    => 'customizer-option-row',
            'popup_button' => __('Options', 'k2cl_customizer'),
            'popup_title'  => __('Options', 'k2cl_customizer'),
            'add_label'    => __('Add option', 'k2cl_customizer')
        ];

        $components = [
            'title'         => __('Components', 'k2cl_customizer'),
            'name'          => 'customizer[components]',
            'id'            => 'customizer-components-table',
            'fields'        => [
                ['type' => 'hidden', 'data' => $componentId],
                ['type' => 'select', 'data' => $componentType],
                ['type' => 'text', 'data' => $componentName],
                ['type' => 'select', 'data' => $componentMultiple],
                ['type' => 'image', 'data' => $componentImage],
                ['type' => 'select', 'data' => $componentEnable],
                ['type' => 'popup', 'data' => $optionsFields]
            ],
            'description'   => __('Component options', 'k2cl_customizer'),
            'class'         => 'striped',
            'add_btn_label' => __('Add component', 'k2cl_customizer')
        ];

        $html .= '<br />';
        $values = $this->get_saved_values_for_edit();
        $html .= $this->add_admin_element_group($components, false, $values);

        $component_row_template = $this->js_row_component($components);
        $component_row_template = preg_replace("/\r|\n/", "", $component_row_template);
        $component_row_template = preg_replace('/\s+/', ' ', $component_row_template);
        $html .= '<script>var component_row_template=' . json_encode($component_row_template) . ';</script>';

        $option_row_template = $this->js_row_component($optionsFields, true);
        $option_row_template = preg_replace("/\r|\n/", "", $option_row_template);
        $option_row_template = preg_replace('/\s+/', ' ', $option_row_template);
        $html .= '<script>var option_row_template=' . json_encode($option_row_template) . ';</script>';

        $html .= '</div>';
        //component section
        $html .= '<div class="block-form">';
        $html .= '</div>';
        echo $html;
    }

    /**
     * Save customizer options
     *
     * @param $post_id
     */
    public function save_post_customizer($post_id)
    {
        if (!wp_is_post_autosave($post_id) && !wp_is_post_revision($post_id)) {
            $customizerData = $this->prepare_customizer_before_save($post_id);
            update_post_meta($post_id, self::CUSTOMIZER_DATA_KEY, $customizerData);

            if (!empty($_POST[self::CUSTOMIZER_SETTINGS_KEY])) {
                update_post_meta($post_id, self::CUSTOMIZER_SETTINGS_KEY, $_POST[self::CUSTOMIZER_SETTINGS_KEY]);
            } else {
                delete_post_meta($post_id, self::CUSTOMIZER_SETTINGS_KEY);
            }
        }
    }

    /**
     * @param $post_id
     *
     * @return array
     */
    public function prepare_customizer_before_save($post_id)
    {
        $customizerData = !empty($_POST[self::CUSTOMIZER_DATA_KEY]) ? $_POST[self::CUSTOMIZER_DATA_KEY] : [];
        if (empty($customizerData)) {
            return [];
        }

        if (!empty($customizerData)) {
            foreach ($customizerData as $keyCustomizer => $customizer) {
                if (empty($customizer['component_id'])) {
                    $customizerData[$keyCustomizer]['component_id'] = $post_id . '_' . $keyCustomizer;
                }

                if (!empty($customizer['options'])) {
                    foreach ($customizer['options'] as $keyOption => $option) {
                        if (empty($option['option_id'])) {
                            $customizerData[$keyCustomizer]['options'][$keyOption]['option_id'] = $post_id . '_' . $keyCustomizer . '_' . $keyOption;
                        }
                    }
                }
            }
        }

        return $customizerData;
    }

    /**
     * @return array
     */
    public function get_all_products()
    {
        $result = [
            '' => __('Select Product', 'k2cl_customizer')
        ];

	    if (K2CL_Customizer_Public::is_woocommerce_enabled())
	    {
		    $args = ['post_type' => 'product', 'posts_per_page' => 999999];

		    $posts = get_posts($args);
		    foreach ($posts as $post){
			    $product = wc_get_product($post->ID);
			    if ($product && $product->is_type('simple')) {
				    $result[ $post->ID ] = $post->post_name;
			    }
		    }
	    }

        return $result;
    }

    /**
     * @param $image
     * @param $item_id
     * @param $item
     *
     * @return string
     */
    public function get_thumbnail_customizer($image, $item_id, $item)
    {
        $data = K2CL_Customizer_Public::get_customizer_data_from_item($item);
        if (empty($data)) {
            return $image;
        }

        return K2CL_Customizer_Public::generateImage($item_id, $item);
    }

    /**
     * @param $keys
     *
     * @return array
     */
    public function get_hidden_order_meta($keys)
    {
        $keys[] = 'customizer_id';

        return $keys;
    }

    /**
     * @param $items
     * @param $order
     *
     * @return mixed
     */
    public function order_get_items($items, $order)
    {
        foreach ($items as $id => $_item) {
            if (!empty($_item['customizer_id'])) {
                $customizerData = get_post($_item['customizer_id']);
                $items[$id]['name'] = !empty($customizerData) ? $customizerData->post_title : $items[$id]['name'];
            }
        }

        return $items;
    }

    /**
     * @param $item_id
     * @param $item
     * @param $_product
     */
    public function get_admin_order_item_render($item_id, $item, $_product)
    {
        $html = '';

        $data = K2CL_Customizer_Public::get_customizer_data_from_item($item);
        if (empty($data)) {
            return;
        }

        $html .= '<div class="customizer_meta">';
        //generate image
        $imageHtml = K2CL_Customizer_Public::generateImage($item_id, $item);
        $html .= $imageHtml;
        $html .= K2CL_Customizer_Public::get_customizer_options_formatted($data, true, true, true);
        $html .= '</div>';

        echo $html;
    }

    /**
     * @param bool $start
     *
     * @return string|void
     */
    public function get_wrap_html($start = true)
    {
        if (!$start) {
            return '</tbody></table></div></div></div>';
        }

        return '<div class="customizer-wrap">' .
        '<div id="" class="o-metabox-container"><div class="block-form">' .
        '<table class="wp-list-table widefat fixed pages o-root"><tbody>';
    }

    /**
     * @param         $data
     * @param         $type
     * @param string  $selected_value
     * @param boolean $showLabel
     * @param int     $parent_id
     *
     * @return string
     */
    public function add_admin_element($data, $type, $selected_value = '', $showLabel = true, $parent_id = null)
    {
        $data = $this->prepare_element_data($data);
        $selected_value = !empty($selected_value) || ($selected_value === "0") ? $selected_value : $data['default'];

        if (in_array($type, ['hidden'])) {
            $showLabel = false;
        }
        $html = '';
        if (!$showLabel && $type != 'hidden') {
            $html .= '<td class="' . $data['td_class'] . '">';
        }
        if ($showLabel) {
            $html .= '<td class="label">' . $data['title'];
            if (!empty($data['description'])) {
                $html .= '<div class="acd-desc">' . $data['description'] . '</div>';
            }
            $html .= '</td><td>';
        }

        switch ($type) {
            case 'text':
            case 'number':
            case 'hidden':
		        $html .= '<input name="' . esc_attr($data['name']) . '" id="' . esc_attr($data['id']) . '"' .
		                 ' type="' . $type . '"';
		        if ($type == 'number') {
			        $selected_value = (float)$selected_value;
			        if (!empty($data['price'])) {
				        $html .= ' step=".01"';
			        }
		        }
		        $html .= ' value="' . esc_attr($selected_value) . '"' . ' 
	                    class="' . esc_attr($data['class']) . '" style="width:100%;" />';
                break;
            case 'textarea':

                $html .= '<textarea name="' . esc_attr($data['name']) . '"' .
                    ' id="' . esc_attr($data['id']) . '" style="' . esc_attr($data['css']) . '"' .
                    ' class="' . esc_attr($data['class']) . '">' . esc_textarea($selected_value) . '</textarea>';
                break;
            case 'optgroup_select':
                $html .= '<select name="' . esc_attr($data['name']) . '" id="' .
                    esc_attr($data['id']) . '"' .
                    'class="' . esc_attr($data['class']) . '">';

                foreach ($data['options'] as $group) {
                    $html .= '<optgroup label="' . $group['title'] . '">';
                    if (!empty($group['options'])) {
                        foreach ($group['options'] as $key => $val) {
                            $html .= '<option value="' . esc_attr($key) . '"';
                            $html .= selected($selected_value, $key, false);
                            $html .= '>' . $val . '</option>';
                        }
                    }
                    $html .= '</optgroup>';
                }
                $html .= '</select>';
                break;
            case 'select':
                $html .= '<select name="' . esc_attr($data['name']) . '" id="' .
                    esc_attr($data['id']) . '"' .
                    'class="' . esc_attr($data['class']) . '">';

                foreach ($data['options'] as $key => $val) {
                    $html .= '<option value="' . esc_attr($key) . '"';
                    $html .= selected($selected_value, $key, false);
                    $html .= '>' . $val . '</option>';

                }
                $html .= '</select>';
                break;
            case 'checkbox':
                $html .= '<input name="' . esc_attr($data['name']) . '" id="' .
                    esc_attr($data['id']) . '"' .
                    'class="' . esc_attr($data['class']) . '"' .
                    ' type="checkbox" value="1" ' . ($selected_value ? 'checked="checked"' : '') . ' />';
                break;
            case 'image' :
                $html .= '<div class="' . $data["class"] . '">' .
                    '<button class="button add-image">' . __('Add', 'k2cl_customizer') . '</button>' .
                    '<button class="button delete-image">' . __('Delete', 'k2cl_customizer') . '</button>' .
                    '<input type="hidden" name="' . $data["name"] . '" value="' . $selected_value . '">';

                $html .= '<div class="image-preview">';
                if ($selected_value) {
                    $html .= '<img src="' . $selected_value . '" />';
                }
                $html .= '</div></div>';
                break;

            case 'popup':
                $html .= $this->add_admin_element_group($data, true, $selected_value, $parent_id);
                break;
            default:
                $html .= '';
                break;
        }
        if ($type != 'hidden') {
            $html .= '</td>';
        }

        return $html;
    }

    /**
     * @param $element
     *
     * @return mixed
     */
    public function prepare_element_data($element)
    {
        $availableFields = [
            'name',
            'id',
            'class',
            'default',
            'css',
            'description',
            'title',
            'td_style',
            'td_class',
            'row_class'
        ];

        foreach ($availableFields as $key) {
            if (!isset($element[$key])) {
                $element[$key] = '';
            }
        }

        return $element;
    }

    /**
     * @param         $data
     * @param boolean $popup
     * @param array   $saved_values
     * @param int     $parent_id
     *
     * @return string
     */

    public function add_admin_element_group($data, $popup = false, $saved_values = [], $parent_id = null)
    {
        if (empty($data)) {
            return '';
        }
        $data = $this->prepare_element_data($data);

        $html = '';

        if ($popup) {
            add_thickbox();
            $popup_id = uniqid("customizer-modal-");
            $html .= "<a class='customizer-modal-trigger button button-primary button-large {$data['class']}' data-toggle='customizer-modal' data-target='#$popup_id' data-modalid='$popup_id'>{$data["popup_title"]}</a>";
            $html .= '<div class="customizerModal fade customizer-modal" id="' . $popup_id . '" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="customizerModal-dialog">
                                          <div class="customizerModal-content">
                                            <div class="customizerModal-header">
                                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                              <h4 class="customizerModal-title" id="myModalLabel' . $popup_id . '">' . $data["popup_title"] . '</h4>
                                            </div>
                                            <div class="customizerModal-body">';
        }

        $html .= '<table id="' . $data["id"] . '"' .
            'class="' . esc_attr($data['class']) . ' wp-list-table widefat fixed striped">';
        $html .= '<thead><tr>';

        if (!empty($data['fields'])) {
            foreach ($data["fields"] as $field) {
                $elementType = !empty($field['type']) ? $field['type'] : 'text';

                if ($elementType == 'hidden') {
                    continue;
                }
                $elementData = $field['data'];
                $elementData = $this->prepare_element_data($elementData);

                $html .= '<th style="' . $elementData['td_style'] . '" class="' . $elementData['td_class'] . '">' . $elementData['title'] . '</th>';
            }
        }

        $html .= '<td style="width: 20px;"></td>';
        $html .= '</tr></thead><tr>';

        if (!empty($saved_values)) {
            foreach ($saved_values as $i => $component) {

                $html .= '<tr class="' . $data['row_class'] . '">';

                if (!empty($data['fields'])) {
                    foreach ($data["fields"] as $field) {

                        $elementData = $field['data'];
                        $elementType = !empty($field['type']) ? $field['type'] : 'text';

                        $name = isset($elementData['name']) ? $elementData['name'] : '';
                        $default_field_value = isset($component[$name]) ? $component[$name] : '';

                        if ($popup) {
                            $name = self::CUSTOMIZER_DATA_KEY . "[$parent_id][options][$i][" . $name . "]";
                        } else {
                            $name = self::CUSTOMIZER_DATA_KEY . "[$i][" . $name . "]";
                        }
                        $elementData['name'] = $name;

                        if ($elementType == 'popup') {
                            $parent_id = ($elementType == 'popup') ? $i : '';
                        }

                        $html .= $this->add_admin_element($elementData, $elementType, $default_field_value, false,
                            $parent_id);
                    }
                    $html .= '<td>';
                    if ($popup) {
                        $html .= '<a class="remove_option">';
                    } else {
                        $html .= '<a class="remove_component">';
                    }
                    $html .= '<span class="dashicons dashicons-no-alt"></span></a>';
                    $html .= '</td></tr>';
                }
            }

        }

        $html .= '</tbody></table><br />';
        if ($popup) {
        	$string_parent_id = $parent_id ? $parent_id : '{{id}}';
        	$additional_class = !empty($data['class']) ? $data['class'] : '';
        	$html .= '<a class="button add_option_button ' . $additional_class . '" data-component="' . $string_parent_id . '">' .
	                 $data['add_label'] . '</a>&nbsp;' .
	                 '<a class="button close" data-dismiss="modal" aria-hidden="true">' .
                    __('Close', 'k2cl_customizer') . '</a>';
        } else {
        	$html .= '<a class="button add_component_button">' . __('Add Component', 'k2cl_customizer') . '</a>';
        }

        if ($popup) {
            $html .= '</div></div></div></div>';
        }


        return $html;
    }

    /**
     * @return array|mixed
     */
    public function get_saved_values_for_edit()
    {
        $data = K2CL_Customizer_Public::get_customizer_meta();
        $data = !empty($data[self::CUSTOMIZER_DATA_KEY]) ? $data[self::CUSTOMIZER_DATA_KEY] : [];

        return $data;
    }

    /**
     * @param         $data
     * @param boolean $options
     *
     * @return string
     */

    public function js_row_component($data, $options = false)
    {

        $html = '<tr class="component-row">';
        if (!empty($data['fields'])) {
            foreach ($data["fields"] as $field) {
                $type = $field['type'];
                $field = $field['data'];
                if ($options) {
                    $field['name'] = self::CUSTOMIZER_DATA_KEY . '[{{component_id}}][options][{{id}}][' . $field['name'] . ']';
                } else {
                    $field['name'] = self::CUSTOMIZER_DATA_KEY . '[{{id}}][' . $field['name'] . ']';
                }

                $html .= $this->add_admin_element($field, $type, '', false);
            }
        }
        $html .= '<td><a class="remove_component"><span class="dashicons dashicons-no-alt"></span></a></td></tr>';

        return $html;
    }

    /**
     * @param null    $customizer_id
     * @param boolean $components_only
     *
     * @return array
     */
    public function getAllOptions($customizer_id = null, $components_only = false)
    {
        $result = [];
        $data = K2CL_Customizer_Public::get_customizer_meta($customizer_id);
        $data = !empty($data[self::CUSTOMIZER_DATA_KEY]) ? $data[self::CUSTOMIZER_DATA_KEY] : [];

        if (empty($data)) {
            return [];
        }

        foreach ($data as $component) {
            if ($components_only) {
                $result[$component['component_id']] = $component['component_name'];
            } else {
                $options = [];
                if (
                !empty($component['options'])
                ) {
                    foreach ($component['options'] as $option) {
                        $options[$option['option_id']] = $component['component_name'] . ' > ' . $option['option_name'];
                    }
                }
                $result[] = [
                    'title'   => $component['component_name'],
                    'options' => $options
                ];
            }
        }

        return $result;
    }

    private function removeDirectory($path)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? self::removeDirectory($file) : unlink($file);
        }
        rmdir($path);
        return;
    }

    public function add_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

}
