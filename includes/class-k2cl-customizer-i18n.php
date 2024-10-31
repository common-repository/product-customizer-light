<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link              http://k2-service.com/shop/product-customizer/
 * @author            K2-Service <plugins@k2-service.com>
 *
 * @package           K2CL_Customizer
 * @subpackage        K2CL_Customizer/includes
 */
class K2CL_Customizer_i18n
{


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
    	load_plugin_textdomain(
            'k2cl_customizer',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }
}
