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
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * @hooked woocommerce_show_product_sale_flash - 10
	 * @hooked woocommerce_show_product_images - 20
	 */
	do_action('woocommerce_before_single_product_summary');
	?>
	<div class="summary entry-summary">
		<div>
			<div>
				<div>
					<h4>Printed Report</h4> main product details
					<div>DpecialistReprotName</div>
					<div>SpecialistReportTitle</div>
					<div>SpecialistReportIntroText</div>
					<div>Main report cost quantity and add to cart</div>
					<div class="wcbp_bottom">
						<span>Price : £100.00</span>
						<?php
						$qty = $product->get_min_purchase_quantity();
						if (isset($_POST['wcbp_bundle_products_nonce']) && wp_verify_nonce(wc_clean($_POST['wcbp_bundle_products_nonce']), 'wcbp_bundle_products_nonce')) {
							$qty = isset($_POST['quantity']) ? wc_stock_amount($_POST['quantity']) : $product->get_min_purchase_quantity();
						}
						echo wp_kses_post(wc_get_stock_html($product));
						if ($product->is_purchasable() && $product->is_in_stock()) {
							do_action('woocommerce_before_add_to_cart_form'); ?>

							<form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
								<?php //do_action('woocommerce_before_add_to_cart_button'); ?>

								<?php
								do_action('woocommerce_before_add_to_cart_quantity');

								woocommerce_quantity_input(
									array(
										'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
										'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
										'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
									)
								);

								do_action('woocommerce_after_add_to_cart_quantity');
								?>

								<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>

								<?php do_action('woocommerce_after_add_to_cart_button'); ?>
							</form>

							<?php do_action('woocommerce_after_add_to_cart_form'); 
						 } ?>
					</div>
					<div>
						<h4>Individual Articles for Download</h4>
						<?PHP
						do_action('ebp_product_layout');
						?>
					</div>
				</div>
			</div>



			<?php
			/**
			 * Hook: woocommerce_single_product_summary.
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 * @hooked WC_Structured_Data::generate_product_data() - 60
			 */
			//do_action( 'woocommerce_single_product_summary' );
			?>
		</div>
		<?php
		/**
		 * Hook: woocommerce_after_single_product_summary.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action('woocommerce_after_single_product_summary');
		?>
	</div>

	<?php do_action('woocommerce_after_single_product'); ?>