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
                add_action('wp_ajax_ebp_add_product', array($this, 'ebp_add_product'));
                add_action('wp_ajax_ebp_sort_addons', array($this, 'ebp_sort_addons'));
                add_action('wp_ajax_ebp_get_article_html', array($this, 'ebp_get_article_html'));
                add_action('wp_ajax_ebp_remove_product', array($this, 'ebp_remove_product'));
                add_filter('woocommerce_product_data_tabs', array($this, 'ebp_product_data_tab'), 20);
                add_action('woocommerce_product_data_panels', array($this, 'ebp_product_data_panel'));
                add_action('init', array($this, 'ebp_register_script'));
                add_action('admin_enqueue_scripts', array($this, 'ebp_enqueue_style'));
                add_filter('wc_get_template_part', array($this, 'ebp_custom_product_template'), 10, 3);
                add_action('ebp_product_layout', array($this, 'ebp_product_layout_builder'));
                add_action('wp_enqueue_scripts', array($this, 'enqueue_front_scripts'));

            }
            public function ebp_remove_product(){
                $prod_id = $_POST['productId'];
                $pub_prod_id = $_POST['publicationId'];
                $_products = get_post_meta($pub_prod_id, 'wcbp_products_addons_values', true);
                $_products = json_decode($_products);
                $index = 0;
                foreach($_products as $key => $_product){
                    $x = $_product;
                    if($_product->id == $prod_id){                        
                        break;
                    }
                    $index = $index + 1;
                };
                unset($_products[$index]);            

                if(update_post_meta($pub_prod_id, 'wcbp_products_addons_values', $_products)){
                    wp_send_json_success();
                };
                
                wp_die();
            }
            public function ebp_get_article_html(){
                $index = $_POST['index'];
                
                $html = $this->product_data_panel_html($index);
                wp_send_json_success($html);
                wp_die();
            }
            public function ebp_sort_addons()
            {
                $sort_order = $_POST['sort_order'];
                $prod_id = $_POST['productId'];

                $products = get_post_meta($prod_id, 'wcbp_products_addons_values', true);

                $sorted_products = $this->sort_products($products, $sort_order);
                $json_add_ons = json_encode($sorted_products);
                update_post_meta($prod_id, 'wcbp_products_addons_values', $json_add_ons);

                wp_send_json_success();
                wp_die();

            }
            public function sort_products($product_array_to_be_sorted, $sortOrderString)
            {
                $sortOrderArray = explode("&sort=", substr($sortOrderString, 5));
                $product_array_to_be_sorted = json_decode($product_array_to_be_sorted,true);
                $c1 = count($product_array_to_be_sorted);
                $c2 = count($sortOrderArray);
                if (count($product_array_to_be_sorted) != count($sortOrderArray)) {
                    echo ("error arrays different size");
                    exit();
                }
                $index = 0;
                foreach ($sortOrderArray as $key => $seqNumber) {
                    $index = $index + 1;
                    $product_array_to_be_sorted[$seqNumber - 1]['sequence'] = $index;
                }
                usort($product_array_to_be_sorted, function ($a, $b) {return strcmp($a['sequence'], $b['sequence']);});
                return $product_array_to_be_sorted;
            }

            public function ebp_add_product()
            {
                $prod_id;
                $pub_prod_id = $_POST['pub_prod_id'];
                $summary_title = $_POST['summary_title'];
                $article_title = $_POST['article_title'];
                $articlename = $_POST['articlename'];
                $price = $_POST['price'];
                $introductory_text = $_POST['introductory_text'];
                $author_name = $_POST['author_name'];
                $author_title = $_POST['author_title'];
                $region = $_POST['region'];
                $publication_date = $_POST['publication_date'];
                $filename = $_POST['filename'];
                $article_attachment_id = $_POST['article_attachment_id'];
                $article_attachment_url = $_POST['article_attachment_url'];

                if (isset($_POST['productId'])):
                    $prod_id = $_POST['productId'];
                else:
                    $prod_id = 0;
                endif;

                $product = array(
                    'ID' => $prod_id,
                    'post_title'            => $article_title,
                    'post_content'          => $introductory_text,
                    'post_status'           => 'publish',
                    'post_type'             => "product",
                    'xx'                    => $summary_title,
                    'yy'                    => $articlename,                    
                );

                if ($prod_id != 0) {
                    // UPDATE PRODUCT (ARTICLE)
                    $returned_post_id = wp_update_post($product, true);
                    
                    if (is_wp_error($returned_post_id)) {
                        $errors = $post_id->get_error_messages();

                        wp_send_json_error($errors);
                        wp_die();
                    }
                } else {
                    // NEW PRODUCT (ARTICLE)

                    $post_id = wp_insert_post($product, $error);
                    wp_set_object_terms($post_id, 'simple', 'product_type');

                    $_products = get_post_meta($pub_prod_id, 'wcbp_products_addons_values', true);                    
                    $_products = json_decode($_products);
                    if($_products === null){
                        $_products = array();
                        $newseq = 1;
                        $newtext = 'id='.$post_id;
                        $newAddon = array('id'=> $post_id, 'text'=> $newtext, 'region'=>'none' , 'sequence'=> $newseq);
                        array_push($_products, $newAddon);
                    } else {
                        $_products = json_decode($_products);
                        $newseq = count($_products) + 1;
                        $newtext = 'id='.$post_id;
                        $newAddon = array('id'=> $post_id, 'text'=> $newtext, 'region'=>'none' , 'sequence'=> $newseq);
                        array_push($_products, $newAddon);
                    }
                    
                    $_products = json_encode($_products, true);
                    $x = update_post_meta($pub_prod_id, 'wcbp_products_addons_values', $_products); 
                }
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                // $meta = get_post_meta($prod_id, '_downloadable_files', true);

                // $download = array();
                // array_push($download, $filename);
                // array_push($download, md5($article_attachment_url));
                // array_push($download, $article_attachment_url);
                // $downloads[$md5_num] = $download; // Insert the new download to the array of downloads

                // $resp = update_post_meta($prod_id, '_downloadable_files', $downloads);

                $product = get_product($prod_id);
                
                $downloads = (array) $product->get_downloads(); 

                // Only added once (avoiding repetitions
                //if( sizeof($downloads) == 0 ){
                    // Get post thumbnail data
                    $thumb_id = get_post_thumbnail_id( $product->get_id() );
                    $src_img  = wp_get_attachment_image_src( $thumb_id, 'full');
                    $img_meta = wp_get_attachment_metadata( $thumb_id, false );

                    // Prepare download data
                    $file_title = $img_meta['image_meta']['title'];
                    $file_url   = reset($src_img);
                    $file_md5   = md5($file_url);

                    $download  = array(
                        'id'   => $file_title,
                        'name' => $file_md5,
                        'file' => $file_url,
                    );//= new WC_Product_Download(); // Get an instance of the WC_Product_Download Object

                    
                    $downloads[$md5_num] = $download; // Insert the new download to the array of downloads

                    $product->set_downloads($downloads); // Set new array of downloads
                // }
               
               
               
               
               
               
               
               
               
               
               
               
                update_post_meta($prod_id, '_price', $price);
                update_post_meta($prod_id, 'publication_date', $publication_date);
                update_post_meta($prod_id, 'author_name', $author_name);
                update_post_meta($prod_id, 'author_title',$author_title);
                update_post_meta($prod_id, 'article_region', $region);

            }

            public function ebp_get_articles_for_product()
            {
                $prod_id = $_POST['productId'];

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
                        $region = get_post_meta($_product->id, 'article_region', true);
                        $desc = $prod->get_description();
                        $authorName = get_post_meta($_product->id, 'author_name', true);
                        $authorPosition = get_post_meta($_product->id, 'author_position', true);
                        $price = $prod->get_price();
                        $downloadUrls = $prod->get_downloads();
                        $publication_date = get_post_meta($_product->id, 'publication_date', true);
                        $sequence = $_product->sequence;
                        foreach ($downloadUrls as $key => $data) {
                            $downloadUrl = $data['file'];                            
                            $downloadName = $data['name'];
                            break;
                        }

                        array_push($arr, array(
                            'id' => $id,
                            'title' => $title,
                            'name' => $name,
                            'short' => $short,
                            'region' => $region,
                            'introText' => $desc,
                            'authorName' => $authorName,
                            'authorTitle' => $authorPosition,
                            'price' => $price,
                            'publicationDate' => $publication_date,
                            'downloadUrl' => $downloadUrl,
                            
                            'downloadName' => $downloadName,
                            'sequence' => $sequence,
                            
                            
                        ));
                    }
                }
                usort($arr, function ($a, $b) {return strcmp($a["sequence"], $b["sequence"]);});
                echo json_encode($arr);
                die();
            }

            public function enqueue_front_scripts()
            {
                if (is_product()) {
                    wp_enqueue_style('wcbp-bundle-product-style', plugins_url('/assets/css/frontend_styles.css', __FILE__), array(), '1.0');
                    wp_register_script('masonry', plugins_url('/assets/js/masonry.min.js', __FILE__), array('jquery'), '1.0', true);
                    wp_register_script('matchheight', plugins_url('/assets/js/jquery.matchHeight-min.js', __FILE__), array('jquery'), '1.0', true);
                    wp_register_script('wcbp-bundle-product', plugins_url('/assets/js/es-frontend-script.js', __FILE__), array('jquery', 'matchheight', 'masonry'), '1.1', true);

                    wp_enqueue_script('wcbp-bundle-product');
                }
            }
            
            public function ebp_product_layout_builder() {
                $prod_id = get_the_ID();
                $_products = get_post_meta($prod_id, 'wcbp_products_addons_values', true);
                $_products = json_decode($_products);
                if (!empty($_products)) {
                    $selection = get_post_meta($prod_id, 'wcbp_product_addons_selection', true);?>
                    <div class="style=" display: flex; flex-direction: column;>
                        <?php
                        $ids = array();
                        foreach ($_products as $key => $_product) {
                            $prod = wc_get_product($_product->id);
                            $ids[] = $_product->id;?>
                            <div class="es_article_region">
                                <?PHP echo (get_post_meta($_product->id, 'article_region', true)); ?>
                            </div>
                            <div class="wcbp_prod_addon " style="display: flex; flex-direction: column;">
                                
                                <div style="display: flex; flex-direction: column; border: double;">
                                    <div style="display:flex; flex-direction: row;width: 100%">
                                        <div class="details">
                                            <span class="wcbp-title">
                                                <a href="<?php
                                                if (get_post_meta($prod_id, 'wcbp_disable_bundle_tems_link', true) == 'no') {
                                                    echo esc_url(get_the_permalink($prod->get_id()));
                                                } else {
                                                    echo 'javascript:void(0);';
                                                }?>
                                                "><?php
                                                    echo esc_html($prod->get_name()); ?>
                                                </a>
                                            </span>
                                        <div class="es_author_name">
                                            <?PHP $aa = get_post_meta($_product->id, 'author_name', true);?>
                                        </div>
                                        <div class="es_article_description">
                                            <?PHP echo ($prod->get_description()); ?>
                                        </div>
                                        <div class="es_article_author_name">
                                            <?PHP echo (get_post_meta($_product->id, 'author_name', true)); ?>
                                        </div>
                                        <div class="es_author_positon">
                                            <?PHP echo (get_post_meta($_product->id, 'author_position', true)); ?>
                                        </div>
                                        <span class="wcbp-price"><?php echo esc_html(get_woocommerce_currency_symbol()); ?><span class="price"><?php echo esc_html(number_format($prod->get_price(), 2, '.', '')); ?></span></span>
                                        <input type="checkbox" name="prod_<?php echo esc_attr($prod->get_id()); ?>" id="cp_prod_<?php echo esc_attr($prod->get_id()); ?>" data-product-id="<?php echo esc_attr($prod->get_id()); ?>">
                                        <span class="es_article_select">Select</span>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $qty = $prod->get_min_purchase_quantity();
                            if (isset($_POST['wcbp_bundle_products_nonce']) && wp_verify_nonce(wc_clean($_POST['wcbp_bundle_products_nonce']), 'wcbp_bundle_products_nonce')) {
                                $qty = isset($_POST['quantity']) ? wc_stock_amount($_POST['quantity']) : $prod->get_min_purchase_quantity();
                            }
                            if ($prod->is_purchasable() && $prod->is_in_stock()) {
                                woocommerce_quantity_input(array(
                                    'input_name' => 'product_' . $prod->get_id(),
                                    'min_value' => apply_filters('woocommerce_quantity_input_min', $prod->get_min_purchase_quantity(), $prod),
                                    'max_value' => apply_filters('woocommerce_quantity_input_max', $prod->get_max_purchase_quantity(), $prod),
                                    'input_value' => $qty,
                                ), null, false);
                            } 
                        }               
                        if ('yes' == $selection) {
                            ?>
                            <input type="hidden" name="wcbp_product_bundle_ids" value="" id="wcbp_product_bundle_ids">
                            <?php 
                        } else {?>
                            <input type="hidden" name="wcbp_product_bundle_ids" value="<?php echo esc_html(implode(',', $ids)); ?>" id="wcbp_product_bundle_ids">
                            <?php
                        }
                        $this->wcbp_get_price_html($prod_id);?>
                        </div>
                        <?php
                } else {
                    echo '<p>' . esc_html__('Product addons not available', 'wc-bundle') . '</p>';
                }
            }
            public function wcbp_get_price_html( $product_id ) {
                $_product=wc_get_product($product_id); 
                $price=0;
                $selection=get_post_meta($product_id, 'wcbp_product_addons_selection', true);
                $price_type=get_post_meta($product_id, 'wcbp_bundle_prod_pricing', true);
                if ( 'fixed_pricing' == $price_type ) {
                    $price=$_product->get_price();
                } elseif ( 'per_product_bundle' == $price_type ) {
                    $price=$_product->get_price();
                    $_products=get_post_meta($product_id, 'wcbp_products_addons_values', true);
                    $_products=json_decode($_products);
                    if ( !empty($_products) && 'yes' !== $selection ) {
                        foreach ( $_products as $item_id ) {
                            $item=wc_get_product($item_id->id);
                            $price+=$item->get_price();
                        }
                    }
                } else {
                    $price=0;
                } 
                ?>
                <div class="wcpb_bundle_total">
                    <?php wp_nonce_field( 'wcbp_bundle_products_nonce', 'wcbp_bundle_products_nonce' ); ?>
                    
                    
                    <?php 
                    $is_in_stock = false;
                    $ids_product = get_post_meta($product_id, 'wcbp_products_addons_values', true);
                    $ids_product = json_decode($ids_product);
                    
                    foreach ($ids_product as $id_product) {
                        $_product = wc_get_product( $id_product->id );
                        if (! $_product->is_in_stock() ) {
                            $is_in_stock = true;
                        }
                        
                    }
                    
                    if ( $is_in_stock ) {
                        ?>
                    <!--<p class="stock out-of-stock">
                        <?php esc_html_e('Out of stock', 'wc-bundle'); ?></p>--->
                        <?php 
                    } else {
                        ?>
                    <p class="price wcpb_bundle_price"> 
                        <?php 
                        esc_html_e('Bundle Total: ', 'wc-bundle');
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
                    'ajaxurl' => admin_url('admin-ajax.php'),
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
                    'label' => __('Articles', 'ernaspark-extend-bundle-products'),
                    'target' => 'article_product_data',
                    'class' => array(),
                );
                unset($product_data_tabs['wcbp_bundle']);
                return $product_data_tabs;
            }
            public function build_articles() {
                $prod_id = get_the_ID();
                $_products = get_post_meta($prod_id, 'wcbp_products_addons_values', true);
                $_products = json_decode($_products);
                if (!empty($_products)) {
                    wp_enqueue_media();
                    $selection = get_post_meta($prod_id, 'wcbp_product_addons_selection', true);?>
                    <?php
                    $index = 0; 
                    $ids = array();
                    foreach ($_products as $key => $_product) {
                        $index = $index + 1;
                        echo($this->product_data_panel_html($index));
                        
                    }                    
                }
            }
            
            public function product_data_panel_html($index){
                return  
                    '<details id="details-block-' . $index .'" open style="width: row;height: auto; border-style: dashed">'.
                                '<summary>
                                    <div class="options_group align-items-center" style="width: auto">
                                        <div class="">
                                            <h4>
                                                <p id="summary-title-' . $index . '">Article Title -'
                                                   . $index.
                                               '</p>
                                            </h4>
                                        </div>
                                        <div class="summary-edit-button button " id="summary-button-edit-'. $index .'" name="'.$index .'">Edit</div>
                                        <div class="summary-button "></div>
                                        <div class="summary-remove-button button " id="summary-button-remove-'.$index.'" name="'.$index.'">Remove</div>
                                        <span class="dashicons dashicons-list-view ui-sortable-handle handle"></span>
                                    </div>
                                </summary>
                                <div class="panel-wrap product_data">
                                    <div class="options_group" style="width: auto;height: auto; padding: 10px;">
                                        <div class="form-group row form-field">
                                            <label for="article-title-'.$index.'" class="col-sm-2 col-form-label"> Article Title</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="article-title-'.$index.'" id="article-title-'.$index.'" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="purchase_note-'.$index.'" class="col-sm-2 col-form-label">Purchase note</label>
                                            <textarea class="form-control" style="" name="purchase_note-'.$index.'" id="purchase_note-'.$index.'" placeholder="">
                                                                        </textarea>
                                        </div>
                                        <div class="form-group row">
                                            <label for="article-name-'.$index.'" class="col-sm-2 col-form-label">Article Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="article-name-'.$index.'" id="article-name-'.$index.'" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="price-'.$index.'" class="col-sm-2 col-form-label">Price</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="price-'.$index.'" id="price-'.$index.'" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="introductory-text-'.$index.'" class="col-sm-2 col-form-label">Introductory Text</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="introductory-text-'.$index.'" id="introductory-text-'.$index.'">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="author-name-'.$index.'" class="col-sm-2 col-form-label">Author Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="author-name-'.$index.'" id="author-name-'.$index.'">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="author-title-'.$index.'" class="col-sm-2 col-form-label">Author Title</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="author-title-'.$index.'" id="author-title-'.$index.'">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="region-'.$index.'" class="col-sm-2 col-form-label">Region</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="region-'.$index.'" id="region-'.$index.'">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="publication-date-'.$index.'" class="col-sm-2 col-form-label">Publication Date</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" name="publication-date-'.$index.'" id="publication-date-'.$index.'" required>
                                            </div>
                                        </div>
    
                                        <p>Selected File
                                        <p>
                                        <div class="form-group row">
                                            <a href="#" id="filename-'.$index.'">filename</a>
                                        </div>
                                        <div>
                                            <input id="upload_image_button-'.$index.'" type="button" class="button" value="Upload Article PDF" />
                                            <input type="hiddenXX" name="image_attachment_id" id="article_attachment_id-'  .$index. '" value="" required>
                                            <input type="hiddenXX" name="image_attachment_url" id="article_attachment_url-'.$index. '" value="" required>
                                        </div>
    
                                        <div class="form-group row">
                                            <div class="col-sm-6"></div>
                                            <button type="submit" class="col-sm-2 button" name="'.$index.'" id="save-'  .$index.'">Save</button>
                                            <button type="submit" class="col-sm-2 button" name="'.$index.'" id="cancel-'.$index.'">Close</button>
                                            <button type="submit" class="col-sm-2 button" name="'.$index.'" id="remove-'.$index.'">Remove</button>
    
                                        </div>
                                    </div>
                                </div>
                            </details>';
                            
                }
            public function ebp_product_data_panel() {
                global $woocommerce, $post;
                $prod_id = $post->ID;
                wp_nonce_field('wcbp_bundle_product_nonce', 'wcbp_bundle_product_nonce');
                echo '';?>
                <div id="wcbp_custom_product_bundle">
                    <div class="options_group">
                        <div id="article_product_data" class="panel woocommerce-options-panel">
                            <div class="container">
                                <div class="col-xs-10">
                                    <div class="form" id="articles-form">
                                        <input type='hiddenXX' id='product_id' value='<?php echo ($prod_id); ?>'>
                                        <form action="#">
                                            <div style="width: auto;height: auto; border-style: dotted; padding: 10px;">
                                                <div style="width:auto" id="sortable">
                                                    <?php
                                                    $this->build_articles();
                                                    ?>
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
                </div>
                <?PHP
            }

        };

        $GLOBALS['ernaspark-extend-bundle-products'] = new Ernaspark_Extend_Bundle_Products;
    }
}
