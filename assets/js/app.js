//(function () {
console.log("entered app.js");
var bundled_products_url = '..\\wp-content\\plugins\\ernaspark-extend-bundle-products\\bundled-products.json';
var template_url = "..\\wp-content\\plugins\\ernaspark-extend-bundle-products\\template.html";
var products = getBundleProducts();
var i = 0;
jQuery(document).ready(function() {
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
    products.forEach(function(product, index) {
        articleIndex = index + 1;
        addArticleHtml(articleIndex);
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
        jQuery('#download-' + articleIndex).val(product.downloadUrl);

    });
    i = articleIndex;
};

function addArticleHtml(index) {
    console.debug(index);
    temp = "<div id='new_" + index + "'></div>";
    jQuery("#sortable").append(temp);
    newid = "#new_" + index;
    new_html = loadData(template_url);
    new_html = new_html.replace(/index/g, index);
    jQuery(newid).append(new_html);
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

function getBundleProducts(productId) {
    console.debug('get bundled products');
    var productList;
    jQuery.ajax( {
	    url: ebpadmin.ajaxurl,
        async: false,
	    dataType: 'json',
        type : 'get',
	    delay: 250,
	    data: {	                    
	        action: 'ebp_get_articles'
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