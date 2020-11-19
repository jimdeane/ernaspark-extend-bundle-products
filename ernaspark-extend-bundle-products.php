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
                add_filter('wc_get_template_part', array($this, 'ebp_custom_product_template'), 10, 3);
                add_action('ebp_product_layout', array($this, 'ebp_product_layout_builder'));
            }
            public function ebp_product_layout_builder()
            {
                $prod_id = get_the_ID();
                $_products = get_post_meta($prod_id, 'wcbp_products_addons_values', true);
                $_products = json_decode($_products);
                if (!empty($_products)) {
                    $selection = get_post_meta($prod_id, 'wcbp_product_addons_selection', true); 
                    echo '<div class="wcbp_product_addons grid">';
                    echo '    <div class="wcbp_row">';

                    $cols = get_post_meta($prod_id, 'wcbp_grid_columns', true);
                    $cols = ($cols > 0) ? $cols : 3;
                    $ids = array();
                    foreach ($_products as $key => $_product) {
                        $prod = wc_get_product($_product->id);
                        $ids[] = $_product->id;
                        echo '<br>';
                        echo '<div>' . 'Product id' . $_product->id . '</div>';
                        echo '<br>';
                    }
                    echo '</div></div>';
                }
            }
            public function ebp_custom_product_template($template, $slug, $name)
            {
                global $product;
                $x = is_singular('product');
                $y = $product->get_type();
                $z = get_post_meta($product->get_id(), 'wcbp_bundle_prod_layout', true);
                if (is_singular('product') && 'single-product' === $name && 'content' === $slug && 'bundle_product' === $product->get_type()) {
                    $template = ES_EBP_DIR . '/includes/content-single-product.php';
                }
                return $template;
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
                global $woocommerce, $post;
                $post_id = $post->ID;
                wp_nonce_field('wcbp_bundle_product_nonce', 'wcbp_bundle_product_nonce');
                echo ''; ?>
                        <div id="wcbp_custom_product_bundle">
                            <div class="options_group">
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

                            </div>
                        </div><?PHP
                                        }
                                    }
                                    $GLOBALS['ernaspark-extend-bundle-products'] = new Ernaspark_Extend_Bundle_Products;
                                }
                            };
