<?php

namespace Ernaspark_Extend_Bundle_Products;

/**
 * Plugin Name: Ernaspark Extend Bundle Products
 * Plugin URI: https://example.com
 * Description: Ernaspark Extend Bundle Products
 * Version: 0.0.1
 * Author: Ernapsark Ltd
 * Author URI: https://www.ernapark.co.uk/
 * Developer: Ernaspark
 * Developer URI: https://www.ernapark.co.uk/
 * Text Domain: ernaspark-extend-bundle-products
 * 
 * Woo: 
 * WC requires at least: 3.0
 * WC tested up to: 3.6.4
 * 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
} //!defined('ABSPATH')
if (!defined('ES_EBP_DIR')) {
    define('ES_EBP_DIR', plugin_dir_path(__FILE__));
}
// Check to make sure WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // only run if there's no other class with this name
    if (!class_exists('Ernaspark_Extend_Bundle_Products')) {
        class Ernaspark_Extend_Bundle_Products
        {
            public function __construct()
            {
                add_filter('woocommerce_product_data_tabs', array($this, 'ebp_product_data_tab'), 20);
                add_action('woocommerce_product_data_panels', array($this, 'ebp_product_data_panel'));
                add_action('init', array($this, 'ebp_register_script'));
                add_action('admin_enqueue_scripts', array($this, 'ebp_enqueue_style'));
            }
            public function ebp_register_script()
            {
                $xx = plugins_url('/assets/js/app.js', __FILE__);
                wp_register_script('ebp_appjs', plugins_url('/assets/js/app.js', __FILE__));
                wp_register_style('ebp_appcss', plugins_url('/assets/css/app.css', __FILE__), false, "1.0.0", 'all');
            }
            public function ebp_enqueue_style()
            {
                wp_enqueue_script('ebp_appjs');
                wp_enqueue_style('ebp_appcss');
            }
            public function ebp_product_data_tab($product_data_tabs)
            {
                $product_data_tabs[''] = array(
                    'label'  => __('Articles', 'ernaspark-extend-bundle-products'),
                    'target' => 'article_product_data',
                    'class'  => array()
                );
                return $product_data_tabs;
            }
            public function ebp_product_data_panel()
            {
?>
                <div id="article_product_data" class="panel woocommerce-options-panel">
                    <?PHP
                    //require_once('template.html');

                    ?>
                    <div class="container">
                        <div class="col-xs-10">
                            <div class="form" id="articles-form">
                                <form action="#">
                                    <div style="width: auto;height: auto; border-style: dotted; padding: 10px;">
                                        <div style="width:auto" id="sortable">

                                        </div>
                                        <div>
                                            <br>
                                            <div class="button" id="add-article-button">Add Article</div>
                                            <div id="test-text"></div>
                                            <div class="" id="test"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
            }
        }
        $GLOBALS['ernaspark-extend-bundle-products'] = new Ernaspark_Extend_Bundle_Products;
    }
};
