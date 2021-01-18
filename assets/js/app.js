//(function () {
console.log("!entered app.js");
var wp_media_post_id = 0; //wp.media.model.settings.post.id; // Store the old id
var set_to_post_id = 6093 ;//<?php echo $my_saved_attachment_post_id; ?>; // Set this
var product_id;
var products;
var i = 0;
jQuery(document).ready(function() {
    // Save new product
    var file_frame;
    jQuery('#upload_image_button-1').on('click', function( event ){
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            // Open frame
            file_frame.open();
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = set_to_post_id;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false,	// Set to true to allow multiple files to be selected
            library: {
            type: ['application/pdf' ]
            }
    
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();

            // Do something with attachment.id and/or attachment.url here
            jQuery( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
            jQuery( '#image_attachment_id' ).val( attachment.id );

            
        });

            // Finally, open the modal
            file_frame.open();
    });
    
    jQuery('#add-article-button').click(function() {
        i++;
        console.debug(i);
        addArticleHtml(i);
        addSummaryToggle(i);
        addCancelAction(i);
    });
    jQuery(function() {
        jQuery("#sortable").sortable({
            stop: function(event, ui) {
                var sorted = jQuery("#sortable").sortable("serialize", {
                    key: "sort",
                });
                console.debug(sorted);
            }
        });
        jQuery("#sortable").disableSelection();
        jQuery("#sortable").sortable("option", "handle", ".handle")
    });
    addExistingArticles();

});



function addSummaryToggle(index) {
    detailsBlockId = '#details-block-' + index;
    jQuery(detailsBlockId).click(function(event) {
        event.preventDefault();
    });
    jQuery('#summary-button-edit-' + index).click(function() {
        console.debug('edit clicked');
        jQuery(this).closest("details").attr("open", "open");
        jQuery(this).hide();
        jQuery(this).parent().find(".summary-remove-button").hide();
    });
}

function addCancelAction(index) {
    jQuery("#cancel-" + index).click(function() {
        console.debug("cancel clicked");
        detailsSection = jQuery(this).closest("details");
        detailsSection.removeAttr("open");
        detailsSection.find(".summary-remove-button").show();
        detailsSection.find(".summary-edit-button").show();
    });
}

function addExistingArticles() {
    console.debug("addExistingArticles");
    products = getBundleProducts();
    products.forEach(function(product, index) {
        articleIndex = index + 1;
        product_id = product.id;
        addArticleScripts(articleIndex);
        addSummaryToggle(articleIndex);
        addCancelAction(articleIndex);
        detailsBlockId = '#details-block-' + articleIndex;
        jQuery(detailsBlockId).removeAttr("open");
        jQuery('#summary-title-' + articleIndex).text(product.title);
        jQuery('#article-title-' + articleIndex).val(product.title);
        jQuery('#article-name-' + articleIndex).val(product.name);
        jQuery('#price-' + articleIndex).val(product.price);
        jQuery('#article-name-' + articleIndex).val(product.name);
        jQuery('#introductory-text-' + articleIndex).val(product.introText);
        jQuery('#author-name-' + articleIndex).val(product.authorName);
        jQuery('#author-title-' + articleIndex).val(product.authorTitle);
        jQuery('#region-' + articleIndex).val(product.region);
        jQuery('#publication-date-' + articleIndex).val(product.publicationDate);
        jQuery('#image-' + articleIndex).val(product.imageUrl);
        jQuery('#filename-' + articleIndex).attr('href',product.downloadUrl);
        jQuery('#filename-' + articleIndex).text(product.title);       
        jQuery('#article_attachment_id-' + articleIndex).val(product.downloadId)
    });
    jQuery('#upload_image_button-1').click(function(){
        console.log('in upload button click');
    });
    i = articleIndex;
};

function addArticleScripts(index) {
    console.debug(index);
    jQuery('#save-' + index).click(function(e){
        e.preventDefault();
        index = jQuery(this).attr("name");
        console.debug("save clicked" + index);

        var summary_title = 	     jQuery('#summary-title-' + index).text();
        var article_title =          jQuery('#article-title-' + index).val();
        var articlename =            jQuery('#article-name-' + index).val();
        var price =                  jQuery('#price-' + index).val();
        var introductory_text =      jQuery('#introductory-text-' + index).val();
        var author_name =            jQuery('#author-name-' + index).val();
        var author_title =           jQuery('#author-title-' + index).val();
        var region =                 jQuery('#region-' + index).val();
        var publication_date =       jQuery('#publication-date-' + index).val();
        var filename =               jQuery('#filename-' + index).text();       
        var article_attachment_id =  jQuery('#article_attachment_id-' + index).val()

        jQuery.ajax({
            url: ebpadmin.ajaxurl,
            async: false,
            dataType: 'json',
            type : 'post',
            delay: 250,
            data: {	                    
                action: 'ebp_add_product',
                productId:            products[index-1].id,
                summary_title: 	       summary_title,	 
                article_title:         article_title,
                articlename:           articlename,      
                price:                 price,        
                introductory_text:     introductory_text,
                author_name:           author_name,  
                author_title:          author_title,        
                region:                region,        
                publication_date:      publication_date,
                filename:              filename,
                article_attachment_id: article_attachment_id
            },
            success: function(data, status) {
                console.debug("product save succeeded");           
            },
            cache: false
          });  
    
    })
    jQuery('#remove-' + index).click(function() {
        index = jQuery(this).attr("name");
        alert('remove clicked ' + index);
        removeProduct(index);
        removeDetailsBlock(index);
    });
    jQuery('#summary-button-remove-' + index).click(function() {
        index = jQuery(this).attr("name");
        alert('summary button remove clicked ' + index);
        removeProduct(index);
        removeDetailsBlock(index);
    });
    jQuery("#image-button-" + index).click(function() {
        console.debug("add image button");
        alert("Wordpress Media selection for Image");
    });
    jQuery("#download-button-" + index).click(function() {
        console.debug("add article button");
        alert("Wordpress Media selection for Article");
    });
}

function removeDetailsBlock(index) {
    console.debug("removing details block-" + index);
    jQuery("#details-block-" + index).remove();
}

function getBundleProducts() {
    console.debug('get bundled products');
    var productId = jQuery('#product_id').val();
    jQuery
    var productList;
    jQuery.ajax( {
	    url: ebpadmin.ajaxurl,
        async: false,
	    dataType: 'json',
        type : 'post',
	    delay: 250,
	    data: {	                    
	        action: 'ebp_get_articles',
            productId: productId
	    },
        success: function(data, status) {
            var jsonData = data;
            productList = data; //JSON.parse(jsonData); 
            console.log(productList);           
        },
        cache: false
	  });
    console.log(productList);
    return productList;    
}

function removeProduct(productId) {
    console.debug(productId);
    tempProducts = [];
    products = products.filter(product => product.id != productId);
    console.debug('removed' + JSON.stringify(products));
}
function loadData(href) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", href, false);
    xmlhttp.send();
    return xmlhttp.responseText;
};


//})();