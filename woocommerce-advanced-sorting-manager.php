<?php
/*
Plugin Name: WooCommerce Advanced Sorting Manager
Description: Manage WooCommerce default and custom sorting options with a settings page.
Version: 1.0.0
Author: Farid Mia
Text Domain: woocommerce-advanced-sorting
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add filters for catalog ordering
add_filter('woocommerce_default_catalog_orderby_options', 'wc_add_advanced_sorting_options');
add_filter('woocommerce_catalog_orderby', 'wc_add_advanced_sorting_options');
add_filter('woocommerce_get_catalog_ordering_args', 'wc_custom_sorting_logic');

// Custom sorting logic
function wc_custom_sorting_logic($args)
{
    switch ($_GET['orderby'] ?? '') {
        case 'alphabetical':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'reverse_alpha':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'by_stock':
            $args['meta_key'] = '_stock_status';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'ASC';
            break;
        case 'review_count':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_wc_review_count';
            $args['order'] = 'DESC';
            break;
        case 'on_sale_first':
            $args['meta_key'] = '_sale_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
    }
    return $args;
}

// Add default and custom sorting options dynamically
function wc_add_advanced_sorting_options($options)
{
    $settings = get_option('wc_advanced_sorting_settings', [
        'default' => [
            'menu_order' => 1,
            'popularity' => 1,
            'rating' => 1,
            'date' => 1,
            'price' => 1,
            'price-desc' => 1,
        ],
        'custom' => [
            'alphabetical' => 1,
            'reverse_alpha' => 1,
            'by_stock' => 1,
            'review_count' => 1,
            'on_sale_first' => 1,
        ]
    ]);

    $default_options = [
        'menu_order' => __('Default sorting', 'woocommerce-advanced-sorting'),
        'popularity' => __('Sort by popularity', 'woocommerce-advanced-sorting'),
        'rating' => __('Sort by average rating', 'woocommerce-advanced-sorting'),
        'date' => __('Sort by latest', 'woocommerce-advanced-sorting'),
        'price' => __('Sort by price: low to high', 'woocommerce-advanced-sorting'),
        'price-desc' => __('Sort by price: high to low', 'woocommerce-advanced-sorting'),
    ];

    $custom_options = [
        'alphabetical' => __('Sort by name: A to Z', 'woocommerce-advanced-sorting'),
        'reverse_alpha' => __('Sort by name: Z to A', 'woocommerce-advanced-sorting'),
        'by_stock' => __('Sort by availability', 'woocommerce-advanced-sorting'),
        'review_count' => __('Sort by review count', 'woocommerce-advanced-sorting'),
        'on_sale_first' => __('Show sale items first', 'woocommerce-advanced-sorting'),
    ];

    // Add default options
    foreach ($default_options as $key => $label) {
        if (!empty($settings['default'][$key])) {
            $options[$key] = $label;
        }
    }

    // Add custom options
    foreach ($custom_options as $key => $label) {
        if (!empty($settings['custom'][$key])) {
            $options[$key] = $label;
        }
    }

    return $options;
}

// Add settings page
add_action('admin_menu', 'wc_advanced_sorting_add_admin_menu');
add_action('admin_init', 'wc_advanced_sorting_settings_init');

function wc_advanced_sorting_add_admin_menu()
{
    add_options_page(
        __('Advanced Sorting Settings', 'woocommerce-advanced-sorting'),
        __('Sorting Manager', 'woocommerce-advanced-sorting'),
        'manage_options',
        'woocommerce-advanced-sorting',
        'wc_advanced_sorting_settings_page'
    );
}

function wc_advanced_sorting_settings_init()
{
    register_setting(
        'wc_advanced_sorting',
        'wc_advanced_sorting_settings',
        [
            'sanitize_callback' => 'wc_advanced_sorting_sanitize_settings',
        ]
    );

    add_settings_section(
        'wc_advanced_sorting_section',
        __('Manage Sorting Options', 'woocommerce-advanced-sorting'),
        null,
        'wc_advanced_sorting'
    );

    $default_options = [
        'menu_order' => __('Default sorting', 'woocommerce-advanced-sorting'),
        'popularity' => __('Sort by popularity', 'woocommerce-advanced-sorting'),
        'rating' => __('Sort by average rating', 'woocommerce-advanced-sorting'),
        'date' => __('Sort by latest', 'woocommerce-advanced-sorting'),
        'price' => __('Sort by price: low to high', 'woocommerce-advanced-sorting'),
        'price-desc' => __('Sort by price: high to low', 'woocommerce-advanced-sorting'),
    ];

    $custom_options = [
        'alphabetical' => __('Sort by name: A to Z', 'woocommerce-advanced-sorting'),
        'reverse_alpha' => __('Sort by name: Z to A', 'woocommerce-advanced-sorting'),
        'by_stock' => __('Sort by availability', 'woocommerce-advanced-sorting'),
        'review_count' => __('Sort by review count', 'woocommerce-advanced-sorting'),
        'on_sale_first' => __('Show sale items first', 'woocommerce-advanced-sorting'),
    ];

    add_settings_field(
        'default_sorting_options',
        __('Default Sorting Options', 'woocommerce-advanced-sorting'),
        'wc_advanced_sorting_checkbox_render',
        'wc_advanced_sorting',
        'wc_advanced_sorting_section',
        ['options' => $default_options, 'group' => 'default']
    );

    add_settings_field(
        'custom_sorting_options',
        __('Custom Sorting Options', 'woocommerce-advanced-sorting'),
        'wc_advanced_sorting_checkbox_render',
        'wc_advanced_sorting',
        'wc_advanced_sorting_section',
        ['options' => $custom_options, 'group' => 'custom']
    );
}

function wc_advanced_sorting_sanitize_settings($settings)
{
    $default_options = [
        'menu_order',
        'popularity',
        'rating',
        'date',
        'price',
        'price-desc',
    ];

    $custom_options = [
        'alphabetical',
        'reverse_alpha',
        'by_stock',
        'review_count',
        'on_sale_first',
    ];

    // Set default options
    foreach ($default_options as $option) {
        $settings['default'][$option] = isset($settings['default'][$option]) ? 1 : 0;
    }

    // Set custom options
    foreach ($custom_options as $option) {
        $settings['custom'][$option] = isset($settings['custom'][$option]) ? 1 : 0;
    }

    return $settings;
}



function wc_advanced_sorting_checkbox_render($args)
{
    $settings = get_option('wc_advanced_sorting_settings', []);
    $group = $args['group'];
    $options = $args['options'];

    foreach ($options as $key => $label) {
        $checked = isset($settings[$group][$key]) ? $settings[$group][$key] : 1;
        echo '<label>';
        echo '<input type="checkbox" name="wc_advanced_sorting_settings[' . esc_attr($group) . '][' . esc_attr($key) . ']" value="1" ' . checked(1, $checked, false) . '> ';
        echo esc_html($label);
        echo '</label><br>';
    }
}

function wc_advanced_sorting_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Advanced Sorting Settings', 'woocommerce-advanced-sorting'); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wc_advanced_sorting');
            do_settings_sections('wc_advanced_sorting');
            submit_button(__('Save Changes', 'woocommerce-advanced-sorting'));
            ?>
        </form>
    </div>
    <?php
}


add_filter('woocommerce_catalog_orderby', 'wc_advanced_sorting_customize_orderby');
function wc_advanced_sorting_customize_orderby($sortby_options)
{
    // Get saved settings
    $settings = get_option('wc_advanced_sorting_settings', []);
    $default_options = !empty($settings['default']) ? $settings['default'] : [];
    $custom_options = !empty($settings['custom']) ? $settings['custom'] : [];

    // Combine default and custom options
    $all_options = array_merge($default_options, $custom_options);

    // Filter options: keep only checked items
    foreach ($all_options as $key => $enabled) {
        if (!$enabled) {
            unset($sortby_options[$key]);
        }
    }

    return $sortby_options;
}





add_filter('woocommerce_get_catalog_ordering_args', 'wc_custom_sorting_logic_ajax');
function wc_custom_sorting_logic_ajax($args)
{
    $orderby = $_GET['orderby'] ?? ''; // Get the orderby parameter

    switch ($orderby) {
        case 'alphabetical':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'reverse_alpha':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'by_stock':
            $args['meta_key'] = '_stock_status';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'ASC';
            break;
        case 'review_count':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_wc_review_count';
            $args['order'] = 'DESC';
            break;
        case 'on_sale_first':
            $args['meta_key'] = '_sale_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
    }

    return $args;
}

add_action('wp_enqueue_scripts', 'wc_advanced_sorting_enqueue_scripts');
function wc_advanced_sorting_enqueue_scripts()
{
    

    wp_enqueue_style(
        'wc-sorting-theme-style', 
        plugin_dir_url(__FILE__) . 'assets/css/loading.css', 
        array(), 
        time(), 
        'all' 
    );
    wp_enqueue_script('jquery');

    // Enqueue custom AJAX script
    wp_enqueue_script('wc-advanced-sorting-ajax', plugin_dir_url(__FILE__) . 'assets/js/wc-advanced-sorting.js', array('jquery'), time(), true);

    // Localize script for AJAX URL
    wp_localize_script('wc-advanced-sorting-ajax', 'wc_ajax_url', array('url' => admin_url('admin-ajax.php')));
}

add_action('wp_ajax_ajax_sort_products', 'handle_ajax_sort_products');
add_action('wp_ajax_nopriv_ajax_sort_products', 'handle_ajax_sort_products');

function handle_ajax_sort_products() {
    // Retrieve the selected sorting option
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';

    // Define query arguments
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
    );

    switch ($orderby) {
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;

        case 'rating':
            $args['meta_key'] = '_wc_average_rating';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;

        case 'date':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;

        case 'price':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;

        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'alphabetical':
            $args['orderby']  = 'title';
            $args['order']    = 'ASC';
            break;
        case 'reverse_alpha':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'by_stock':
            $args['meta_key'] = '_stock_status';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'ASC';
            break;
        case 'review_count':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_wc_review_count';
            $args['order'] = 'DESC';
            break;
        case 'on_sale_first':
            $args['meta_key'] = '_sale_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;

        default:
            $args['orderby'] = 'menu_order';
            break;
    }

    // Query WooCommerce products
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product'); // WooCommerce product template
        }
    } else {
        echo '<p>No products found.</p>';
    }

    wp_reset_postdata();
    wp_die(); // Important for AJAX calls
}

