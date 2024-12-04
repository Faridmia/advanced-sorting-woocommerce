jQuery(document).ready(function ($) {
    // Prevent the form from submitting and causing a page reload
    $(document).on('change', '.woocommerce-ordering .orderby', function (e) {
        e.preventDefault(); // Prevent the default behavior of the dropdown

        const sortBy = $(this).val(); // Get the selected sorting option

        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('orderby', sortBy); // Update the 'orderby' query parameter
        window.history.pushState({}, '', currentUrl); // Update the browser URL

        console.log('Updated URL:', currentUrl.href); // Debugging log
        console.log(sortBy)
        $.ajax({
            url: wc_ajax_url.url, // The AJAX URL set in wp_localize_script
            type: 'POST',
            data: {
                action: 'ajax_sort_products',
                orderby: sortBy,
            },
            beforeSend: function () {
                $('.wp-block-woocommerce-product-template').addClass('loading'); // Add loading class
            },
            success: function (response) {
                console.log(response)
                $('.wp-block-woocommerce-product-template').html(response); // Replace product list with response
                $('.wp-block-woocommerce-product-template').removeClass('loading'); // Remove loading class
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            },
        });
    });

    // Prevent form submission if it's wrapping the sorting dropdown
    $(document).on('submit', '.woocommerce-ordering', function (e) {
        e.preventDefault(); // Prevent form submission
    });
});
