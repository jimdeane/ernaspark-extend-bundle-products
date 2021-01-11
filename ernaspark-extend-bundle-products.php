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
                add_action('wp_ajax_ebp_get_articles', array($this, 'ebp_get_articles_for_product'));

                add_filter('woocommerce_product_data_tabs', array($this, 'ebp_product_data_tab'), 20);
                add_action('woocommerce_product_data_panels', array($this, 'ebp_product_data_panel'));
                add_action('init', array($this, 'ebp_register_script'));
                add_action('admin_enqueue_scripts', array($this, 'ebp_enqueue_style'));
                add_filter('wc_get_template_part', array($this, 'ebp_custom_product_template'), 10, 3);
                add_action('ebp_product_layout', array($this, 'ebp_product_layout_builder'));
                add_action('wp_enqueue_scripts', array($this, 'enqueue_front_scripts')); 
               
            }

            public function ebp_get_articles_for_product() {
                $prod_id = 5736; // get_the_ID();
                $_products = get_post_meta($prod_id, 'wcbp_products_addons_values', true);
                $_products = json_decode($_products);
                $arr = array();
                if (!empty($_products)) {
                    $selection = get_post_meta($prod_id, 'wcbp_product_addons_selection', true);
                    $ids = array();
                    foreach ($_products as $key => $_product) {
                        $id = $_product->id;
                        $prod = wc_get_product($_product->id);
                        $ids[] = $_product->id; 
                        $title = $prod->get_title();
                        $name = $prod->get_name(); 
                        $short = $prod->get_short_description();                         
                        $region = get_post_meta( $_product->id, 'article_region', true );
                        $desc = $prod->get_description();
                        $authorName = get_post_meta( $_product->id, 'author_name', true );  
                        $authorPosition = get_post_meta( $_product->id, 'author_position', true );                        
                        $price = $prod->get_price();                            
                        array_push( $arr, array('id' => $id, 
                                                'ttile' => $title, 
                                                'name'=> $name, 
                                                'short'=> $short, 
                                                'region'=> $region, 
                                                'desc'=> $desc, 
                                                'authorName'=> $authorName, 
                                                'authorPosition', $authorPosition,
                                                'price'=> $price ));
                    }
                }                
                
                // $arr = array('id'=>1, 'name'=>'name');
                echo json_encode($arr);
                die();
            }

            public function enqueue_front_scripts() {
                if (is_product()) {
                    wp_enqueue_style('wcbp-bundle-product-style', plugins_url('/assets/css/frontend_styles.css', __FILE__), array(), '1.0');
                    wp_register_script('masonry', plugins_url('/assets/js/masonry.min.js', __FILE__), array('jquery'), '1.0', true);
                    wp_register_script('matchheight', plugins_url('/assets/js/jquery.matchHeight-min.js', __FILE__), array('jquery'), '1.0', true);
                    wp_register_script('wcbp-bundle-product', plugins_url('/assets/js/es-frontend-script.js', __FILE__), array('jquery', 'matchheight', 'masonry'), '1.1', true);

                    wp_enqueue_script('wcbp-bundle-product');
                }
            }
            public function ebp_product_layout_builder()
            {
                $prod_id = get_the_ID();
                $_products = get_post_meta($prod_id, 'wcbp_products_addons_values', true);
                $_products = json_decode($_products);
                if (!empty($_products)) {
                    $selection = get_post_meta($prod_id, 'wcbp_product_addons_selection', true); ?>
                    <div class="style=" display: flex; flex-direction: column;"">
                        <?php
                        $ids = array();
                        foreach ($_products as $key => $_product) {
                            $prod = wc_get_product($_product->id);
                            $ids[] = $_product->id; ?>
                            <div class="es_article_region" ><?PHP echo(get_post_meta( $_product->id, 'article_region', true ));  ?></div>
                            <div class="wcbp_prod_addon " style="display: flex; flex-direction: row;">
                                <div style="width: 100px;">
                                    <?php
                                    if (has_post_thumbnail($prod->get_id())) {
                                        echo get_the_post_thumbnail($prod->get_id());
                                    } else {
                                        echo '<img src="' . esc_url(WC_UBP_URL . 'assets/images/placeholder.png') . '" >';
                                    } ?>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <div style="display:flex; flex-direction: row;width: 100%">
                                        <div class="details">
                                            <span class="wcbp-title"><a href="<?php
                                            if (get_post_meta($prod_id, 'wcbp_disable_bundle_tems_link', true) == 'no') {
                                                echo esc_url(get_the_permalink($prod->get_id()));
                                            } else {
                                                echo 'javascript:void(0);';
                                            }
                                            ?>
                                            "><?php echo esc_html($prod->get_name()); ?></a></span>
                                            <div class="es_author_name"><?PHP $aa = get_post_meta( $_product->id, 'author_name', true );?></div>
                                            <div class="es_article_description"><?PHP echo($prod->get_description());  ?></div>
                                            <div class="es_article_author_name"><?PHP echo(get_post_meta( $_product->id, 'author_name', true ));  ?></div>
                                            <div class="es_author_positon"><?PHP echo(get_post_meta( $_product->id, 'author_position', true ));  ?></div>
                                            <span class="wcbp-price"><?php echo esc_html(get_woocommerce_currency_symbol()); ?><span class="price"><?php echo esc_html(number_format($prod->get_price(), 2, '.', '')); ?></span></span>
                                            <input type="checkbox" name="prod_<?php echo esc_attr($prod->get_id()); ?>" id="cp_prod_<?php echo esc_attr($prod->get_id()); ?>" data-product-id="<?php echo esc_attr($prod->get_id()); ?>">
                                            <span class="es_article_select">Select</span>
                                        </div>
                                    </div>
                                <?php
                            

                            if (!$prod->is_in_stock()) {?>
                                    <p class="stock out-of-stock">
                                        <?php esc_html_e('Out of stock', 'wc-bundle'); ?>
                                    </p><?php
                                    } ?>
                                </div>
                            </div>

                            <?php
                            $qty = $prod->get_min_purchase_quantity();
                            if (isset($_POST['wcbp_bundle_products_nonce']) && wp_verify_nonce(wc_clean($_POST['wcbp_bundle_products_nonce']), 'wcbp_bundle_products_nonce')) {
                                $qty = isset($_POST['quantity']) ? wc_stock_amount($_POST['quantity']) : $prod->get_min_purchase_quantity();
                            }
                            if ($prod->is_purchasable() && $prod->is_in_stock()) {
                                woocommerce_quantity_input(array(
                                    'input_name'  => 'product_' . $prod->get_id(),
                                    'min_value'   => apply_filters('woocommerce_quantity_input_min', $prod->get_min_purchase_quantity(), $prod),
                                    'max_value'   => apply_filters('woocommerce_quantity_input_max', $prod->get_max_purchase_quantity(), $prod),
                                    'input_value' => $qty,
                                ), null, false);
                            }
                        }
                            if ('yes' == $selection) {
                            ?>
                                <input type="hidden" name="wcbp_product_bundle_ids" value="" id="wcbp_product_bundle_ids">
                            <?php } else { ?>
                                <input type="hidden" name="wcbp_product_bundle_ids" value="<?php echo esc_html(implode(',', $ids)); ?>" id="wcbp_product_bundle_ids">
                            <?php
                            }
                            $this->wcbp_get_price_html($prod_id);
                            ?>
                    </div>
                <?php
                } else {
                    echo '<p>' . esc_html__('Product addons not available', 'wc-bundle') . '</p>';
                }
            }
            public function wcbp_get_price_html($product_id)
            {
                $_product = wc_get_product($product_id);
                $price = 0;
                $selection = get_post_meta($product_id, 'wcbp_product_addons_selection', true);
                $price_type = get_post_meta($product_id, 'wcbp_bundle_prod_pricing', true);
                if ('fixed_pricing' == $price_type) {
                    $price = $_product->get_price();
                } elseif ('per_product_bundle' == $price_type) {
                    $price = $_product->get_price();
                    $_products = get_post_meta($product_id, 'wcbp_products_addons_values', true);
                    $_products = json_decode($_products);
                    if (!empty($_products) && 'yes' !== $selection) {
                        foreach ($_products as $item_id) {
                            $item = wc_get_product($item_id->id);
                            $price += $item->get_price();
                        }
                    }
                } else {
                    $price = 0;
                }
                ?>
                <div class="wcpb_bundle_total">
                    <?php wp_nonce_field('wcbp_bundle_products_nonce', 'wcbp_bundle_products_nonce'); ?>


                    <?php
                    $is_in_stock = false;
                    $ids_product = get_post_meta($product_id, 'wcbp_products_addons_values', true);
                    $ids_product = json_decode($ids_product);

                    foreach ($ids_product as $id_product) {
                        $_product = wc_get_product($id_product->id);
                        if (!$_product->is_in_stock()) {
                            $is_in_stock = true;
                        }
                    }

                    if ($is_in_stock) {
                    ?>
                        <!--<p class="stock out-of-stock">
                        <?php esc_html_e('Out of stock', 'wc-bundle'); ?></p>--->
                    <?php
                    } else {
                    ?>
                        <p class="price wcpb_bundle_price">
                            <?php
                            esc_html_e('Total : ', 'wc-bundle');
                            echo esc_html(get_woocommerce_currency_symbol()) . '<span class="wcpb_bundle_price">' . esc_html(number_format($price, 2)) . '</span>';
                            ?>
                        </p>
                    <?php } ?>
                </div>
            <?php
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
                wp_localize_script('ebp_appjs', 'ebpadmin', array(
					'ajaxurl'	=>	admin_url('admin-ajax.php')
				));
                wp_register_style('ebp_appcss', plugins_url('/assets/css/app.css', __FILE__), false, "1.0.0", 'all');
            }
            public function ebp_enqueue_style()
            {
                wp_enqueue_style('ebp_appcss');
                wp_enqueue_script('ebp_appjs');               
            }
            public function ebp_product_data_tab($product_data_tabs)
            {
                $product_data_tabs[''] = array(
                    'label'  => __('Articles', 'ernaspark-extend-bundle-products'),
                    'target' => 'article_product_data',
                    'class'  => array()
                );
                unset($product_data_tabs['wcbp_bundle']);
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
