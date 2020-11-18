//(function () {
var bundled_products_url = 'bundled-products.json';
products = preLoadProductData();
//removeProduct(103);
var i = 0;
$(document).ready(function() {
    $('#add-article-button').click(function() {
        i++;
        console.debug(i);
        addArticleHtml(i);
        addSummaryToggle(i);
        addCancelAction(i);
    });
    $(function() {
        jQuery("#sortable").sortable({
            stop: function(event, ui) {
                var sorted = $("#sortable").sortable("serialize", {
                    key: "sort",
                });
                console.debug(sorted);
            }
        });
        $("#sortable").disableSelection();
        $("#sortable").sortable("option", "handle", ".handle")
    });
    addExistingArticles();

});

function preLoadProductData() {
    return getBundleProducts();
}

function addSummaryToggle(index) {
    detailsBlockId = '#details-block-' + index;
    $(detailsBlockId).click(function(event) {
        event.preventDefault();
    });
    $('#summary-button-edit-' + index).click(function() {
        console.debug('edit clicked');
        $(this).closest("details").attr("open", "open");
        $(this).hide();
        $(this).parent().find(".summary-remove-button").hide();
    });
}

function addCancelAction(index) {
    $("#cancel-" + index).click(function() {
        console.debug("cancel clicked");
        detailsSection = $(this).closest("details");
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
        $(detailsBlockId).removeAttr("open");
        $('#summary-title-' + articleIndex).text(product.title);
        $('#article-title-' + articleIndex).val(product.title);
        $('#article-name-' + articleIndex).val(product.name);
        $('#price-' + articleIndex).val(product.price);
        $('#article-name-' + articleIndex).val(product.name);
        $('#introductory-text-' + articleIndex).val(product.introText);
        $('#author-name-' + articleIndex).val(product.authorName);
        $('#author-title-' + articleIndex).val(product.authorTitle);
        $('#region-' + articleIndex).val(product.region);
        $('#publication-date-' + articleIndex).val(product.publicationDate);
        $('#image-' + articleIndex).val(product.imageUrl);
        $('#download-' + articleIndex).val(product.downloadUrl);

    });
    i = articleIndex;
};

function addArticleHtml(index) {
    console.debug(index);
    temp = "<div id='new_" + index + "'></div>";
    jQuery("#sortable").append(temp);
    newid = "#new_" + index;
    new_html = loadData("template.html");
    new_html = new_html.replace(/index/g, index);
    $(newid).append(new_html);
    $('#remove-' + index).click(function() {
        index = $(this).attr("name");
        alert('remove clicked ' + index);
        removeProduct(index);
        removeDetailsBlock(index);
    });
    $('#summary-button-remove-' + index).click(function() {
        index = $(this).attr("name");
        alert('summary button remove clicked ' + index);
        removeProduct(index);
        removeDetailsBlock(index);
    });
    $("#image-button-" + index).click(function() {
        console.debug("add image button");
        alert("Wordpress Media selection for Image");
    });
    $("#download-button-" + index).click(function() {
        console.debug("add article button");
        alert("Wordpress Media selection for Article");
    });
}

function removeDetailsBlock(index) {
    console.debug("removing details block-" + index);
    $("#details-block-" + index).remove();
}

function getBundleProducts(productId) {
    console.debug('get bundled products');
    productList = JSON.parse(loadData(bundled_products_url));
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