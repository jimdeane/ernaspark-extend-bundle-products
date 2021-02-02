<?php

/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?> >
	<?php	
	do_action('woocommerce_before_single_product_summary');
	?>
	<div class="summary entry-summary">
		<div>
			<div>
				<div>
					<?PHP 	
					$terms = wp_get_post_terms($product->get_id(),"product_cat");
					$caption = "<h4>No product category set</h4>";
					$countOfCategories = 0;
					foreach($terms as $category){
						if($category->name == "Publications"){
							$countOfCategories++;
							$caption = "<h4>Printed Publication</h4>";
						}
						if($category->name == "Reports"){
							$countOfCategories++;
							$caption = "<h4>Printed Report</h4>";
						}
					}
					if($countOfCategories > 1){
						$caption = "<h4>Both Publication and Report categories set!</h4>";
					}
					echo($caption);?>
					<div>
						<?PHP echo($product->get_name()); ?>
					</div>	
					<div>
						<?PHP
						echo($product->get_description());				
						echo($product->get_short_description()); ?>
					</div>
					<?PHP echo wp_kses_post(wc_get_stock_html($product)); ?>
					<div class="wcbp_bottom wcbp_prod_addon">
						<span>Price : <?PHP echo(get_woocommerce_currency_symbol());echo(number_format($product->get_regular_price(),2,'.',''));  ?></span>
						<input type="checkbox" name="main_product" id="main_product_select" ></input>
						<span id="main_product_select_text">Select</span>
					</div>
					<form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
						<div  style="display: flex; flex-direction: column;">
							<h4>Individual Articles for Download</h4>
							<?PHP
							do_action('ebp_product_layout');
							?>
							<div style="display: flex; flex-direction: row;">
								<div>
									<?php
									$qty = $product->get_min_purchase_quantity();
									if (isset($_POST['wcbp_bundle_products_nonce']) && wp_verify_nonce(wc_clean($_POST['wcbp_bundle_products_nonce']), 'wcbp_bundle_products_nonce')) {
										$qty = isset($_POST['quantity']) ? wc_stock_amount($_POST['quantity']) : $product->get_min_purchase_quantity();
									}
									if ($product->is_purchasable() && $product->is_in_stock()) {
										do_action('woocommerce_before_add_to_cart_form'); 
									}?>
								</div>
								<div>								
									<?php
									do_action('woocommerce_before_add_to_cart_quantity');
									do_action('woocommerce_after_add_to_cart_quantity');
									woocommerce_quantity_input( array(
										'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
										'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
										'input_value' => $qty,
									));
									?>
									<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
									<?php do_action('woocommerce_after_add_to_cart_button'); ?>
								</div>
							</div>
						<div>
					</form>
					<div class="summary entry-summary">
					</div>
					<?php do_action('woocommerce_after_add_to_cart_form');?>							
				</div>
			</div>		
		</div>
		<?php		
		do_action('woocommerce_after_single_product_summary');
		?>
	</div>
	<?php
	do_action('woocommerce_after_single_product'); ?>
</div>
