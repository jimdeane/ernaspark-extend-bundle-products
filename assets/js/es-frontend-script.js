function wcpb_may_be_disable_btn() {
    // ****************************
    // Ernaspark Publication / Articles Override
    // ****************************
    jQuery(".product-type-bundle_product .cart").addClass("wcbp_bottom");
    if (jQuery("#wcbp_product_bundle_ids").val())
        var items = jQuery("#wcbp_product_bundle_ids").val().split(",").length;
    var qty = 1;
    jQuery(".wcbp_product_addons .wcbp_loop").each(function (index) {
        if (
            jQuery(this).find("[type=checkbox]").is(":checked") &&
            wcbp.options.selection == true
        ) {
            qty += Number(jQuery(this).find(".quantity .qty").val());
        } else {
            qty += Number(jQuery(this).find(".quantity .qty").val());
        }
    });
    console.log(qty);
    let mainProductSelect = jQuery("#main_product_select").prop("checked");
    if (
        (jQuery("#wcbp_product_bundle_ids").val() != ""
            && parseInt(wcbp.options.bundle_min) <= parseInt(items)
            && parseInt(wcbp.options.products_minimum_qty) <= parseInt(qty)
        ) || mainProductSelect
    ) {
        console.log(items);
        console.log("enable");
        jQuery(".wcbp_bottom .single_add_to_cart_button").removeAttr(
            "disabled"
        );
    } else {
        console.log("disabled");
        jQuery(".wcbp_bottom .single_add_to_cart_button").attr(
            "disabled",
            "disabled"
        );
    }
}
jQuery(".wcbp_product_addons .qty").change(function () {
    wcpb_may_be_disable_btn();
});
jQuery(document).ready(function ($) {
    jQuery(".wcbp_product_addons.grid .wcbp_prod_addon").matchHeight();
    $(".wcbp_product_addons.masonry .wcbp_row").masonry({
        // options...
        itemSelector: ".wcbp_col_3",
        //columnWidth: 200
    });
    console.log('wcbp.options ');
    console.log(wcbp.options);
    wcpb_may_be_disable_btn();

    function wcbp_refresh_price() {
        var temp = 0;
        var price = 0;
        var qty = 1;
        var mainProductSelect = jQuery("#main_product_select").prop("checked");

        var bundle_ids = jQuery("#wcbp_product_bundle_ids");
        if (jQuery("#wcbp_product_bundle_ids").val()) {
            var ids = jQuery("#wcbp_product_bundle_ids").val().split(",");
            if (wcbp.options.pricing !== "fixed_pricing") {
                if (wcbp.options.pricing === "per_product_bundle" || wcbp.options.pricing === "per_product_only"){
                    bundlePrice = parseFloat(wcbp.options.bundle_price);
                    if(mainProductSelect) {
                        price = parseFloat(bundlePrice)
                    }
                }
                
                // jQuery.each(ids, function (key, item) {
                for (let i in ids) {
                    if (wcbp.options[ids[i]] && wcbp.options[ids[i]].price) {
                        temp = wcbp.options[ids[i]].price;

                        if ( jQuery(".wcbp_product_addons .wcbp_loop").length > 0 ) {
                            qty = jQuery(".wcbp_product_addons")
                                .find('.quantity input[name="product_' + ids[i] + '"]' )
                                .val();

                            if (qty) temp = parseFloat(temp) * parseInt(qty);
                        }

                        price = parseFloat(price) + parseFloat(temp);

                        price = price.toFixed(2);
                        jQuery(
                            ".wcpb_bundle_total .wcpb_bundle_price .wcpb_bundle_price"
                        ).html(price);
                    }
                };
                // jQuery(".wcpb_bundle_total").show();
            }
        } else {
            if (wcbp.options.pricing !== "fixed_pricing") {
                if (wcbp.options.pricing === "per_product_bundle"|| wcbp.options.pricing === "per_product_only")
                    price = parseFloat(wcbp.options.bundle_price);
                price = price.toFixed(2);
                jQuery(
                    ".wcpb_bundle_total .wcpb_bundle_price .wcpb_bundle_price"
                ).html(price);
            }
        }
    }
    if (
        wcbp.options.pricing === "per_product_only" ||
        wcbp.options.pricing === "per_product_bundle"
    ) {
        wcbp_refresh_price();
        if (jQuery(".wcbp_product_addons .wcbp_loop ").length > 0) {
            jQuery(document).on(
                "click",
                ".wcbp_product_addons .wcbp_loop .quantity input",
                function () {
                    wcbp_refresh_price();
                }
            );
        }
    }
    jQuery(document).on(
        "click",
        '.wcbp_prod_addon input[type="checkbox"]',
        function (e) {
            var select = jQuery(this);
            if (select.attr("id") == "main_product_select") {
                console.log("main body select");
                var productId = $('#wcbp_main_product_id').val();
            } else {
                productid = "";

            }
            wcbp_refresh_price();

            if (select.is(":checked")) {
                var prod_id = select.attr("data-product-id");
                var product_ids = jQuery("#wcbp_product_bundle_ids").val();
                if (product_ids && product_ids !== "") {
                    product_ids = product_ids.split(",");
                    product_ids.push(prod_id);
                    jQuery("#wcbp_product_bundle_ids").val(
                        product_ids.join(",")
                    );
                } else {
                    product_ids = [];
                    product_ids.push(prod_id);
                    jQuery("#wcbp_product_bundle_ids").val(
                        product_ids.join(",")
                    );
                }
                wcbp_refresh_price();
            } else {
                var prod_id = select.attr("data-product-id");
                var product_ids = jQuery("#wcbp_product_bundle_ids").val();
                if (product_ids && product_ids !== "") {
                    product_ids = product_ids.split(",");
                    product_ids = jQuery.grep(product_ids, function (value) {
                        return value != prod_id;
                    });
                    jQuery("#wcbp_product_bundle_ids").val(
                        product_ids.join(",")
                    );
                }
                wcbp_refresh_price();
            }
            wcpb_may_be_disable_btn();
        }
    );
});
