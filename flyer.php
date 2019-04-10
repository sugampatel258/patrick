<?php
/*
Plugin Name: WooCommerce - Fyler of Last visited products
Plugin URL: https://www.atsw-anhaenger.at
Description: Plugin about to check last visited products and create a flyer based on that visited product.
Version: 1.0
Author: Patrick
Author URI: https://www.atsw-anhaenger.at
*/

if (!defined('ABSPATH'))
    exit;

/**
 * Check if WooCommerce is activated
 */
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
    function is_woocommerce_activated() {
        if ( ! class_exists( 'woocommerce' ) ) { return; }
    }
}

define( 'MY_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

if(is_admin()) {
    require_once('includes/flyer-setting.php');
    require_once('includes/flyer-column.php');
}
/** Enqueue scripts for admin */
add_action( 'admin_enqueue_scripts', 'flyer_enqueue' );
function flyer_enqueue($hook) {
    wp_enqueue_script( 'flyer-adminjs', MY_PLUGIN_PATH.'assets/js/flyer-admin.js');
    wp_localize_script( 'flyer-adminjs', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
/** Enqueue styles for admin */
add_action('admin_head', 'myadmin_stylesheet' );
function myadmin_stylesheet() {
    wp_enqueue_style( 'flyer-admin', MY_PLUGIN_PATH.'assets/css/admin.css');
}
/** Enqueue scripts for frontend user */
add_action('wp_enqueue_scripts', 'flyer_front_script');
function flyer_front_script($hook) {
    wp_enqueue_script( 'flyer-frontend', MY_PLUGIN_PATH.'assets/js/frontend.js', array(), '1.0.0', true );
    wp_localize_script( 'flyer-frontend', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

/** Create Flyer setting page under woocommerce menu */
function flyer_settings_submenu_page() {
    add_submenu_page( 'woocommerce', 'Flyer Settings', 'Flyer Settings', 'manage_options', 'flyer-settings', 'flyer_settings_callback' ); 
}

add_action('admin_menu', 'flyer_settings_submenu_page',99);

/* Store Product Details After a Successful Order - WooCommerce */
add_action( 'woocommerce_thankyou', 'checkout_save_user_meta');
 
function checkout_save_user_meta( $order_id ) {
     
    $order = new WC_Order( $order_id );
    $user_id = $order->get_user_id();
     
    if ( $order ) {  // Define your condition here
        // Check Ordered product is not in cookie
        $products = $order->get_items();        

        global $woocommerce;
        // Get recently viewed product cookies data
        $viewed_products_cart = ! empty( $_COOKIE['woocommerce_recently_viewed_cart'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed_cart'] ) : array();
        $viewed_products_single = ! empty( $_COOKIE['woocommerce_recently_viewed_single'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed_single'] ) : array();
        $viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
        
        /** Merge all three array in single array */ 
        $total_products = array_merge($viewed_products_cart, $viewed_products_single);
        $total_products = array_merge($total_products, $viewed_products);

        /** Check and remove purchased product from total_products */
        foreach ($total_products as $key => $value) {
            $prod = explode(':', $value);
            foreach ($products as $product) {
                if($product->get_product_id() == $prod[0]) {
                    unset($total_products[$key]);
                }  
            }
        }
        /** Get score from total_products */
        $score = array();
        foreach ($total_products as $key => $value) {
            $prod = explode(':', $value);
            $score[] = $prod[1];
        }
        rsort($score);

        /** Find highest scored product */
        $scored_products = array();
        $score = array_slice($score, 0, 2);
        foreach ($total_products as $key => $value) {
            $prod = explode(':', $value);
            if(in_array($prod[1], $score) && !in_array($prod[0], $scored_products)) {
                $scored_products[] = $prod[0];
            }
        }
       $scored_products = array_slice($scored_products, 0, 2);
        
        // if(sizeof($total_products) > 2 && sizeof($total_products) <= 3) $get_products = 1;
        // $viewed_products = array_slice($total_products, -(sizeof($score) - $get_products), 2);
        
        // If no data, quit
        if ( empty( $scored_products ) )
            return __( '' );

        add_post_meta($order_id, 'last_product_ids', $scored_products);
    }
 
}

function product_to_cookie($product_id, $label, $score) {
    $cookieName = 'woocommerce_recently_viewed'.$label;
    // Check cookie is empty or not
    if ( empty( $_COOKIE[$cookieName] ) )
        $viewed_products = array();
    else
        $viewed_products = (array) explode( '|', $_COOKIE[$cookieName] );

    $product_ids = array();
    foreach ($viewed_products as $key => $value) {
        $product = explode(':', $value);
        $product_ids[] = $product[0];
    }
    // Check product is already available in cookie array
    if ( ! in_array( $product_id, $product_ids ) ) {
        $viewed_products[] = $product_id . ':' . $score;
    } else if ( in_array( $product_id, $product_ids ) ) {
        foreach ($viewed_products as $key => $value) {
            $product = explode(':', $value);
            if($product_id == $product[0]) {
                $total_score = $product[1] + $score;
                $viewed_products[$key] = $product_id . ':' . $total_score;
            }
        }
    }
    if ( sizeof( $viewed_products ) >= 10 ) {
        array_shift( $viewed_products );
    }
    // Store for session only
    wc_setcookie( $cookieName, implode( '|', $viewed_products ) );



}

add_action( 'wp_ajax_store_visited_products', 'store_visited_products' );
add_action( 'wp_ajax_nopriv_store_visited_products', 'store_visited_products' );

function store_visited_products() {
    if(!isset($_POST['product_id']))
        return;
    product_to_cookie($_POST['product_id'], '', 1);
    exit;
}

add_action( 'wp_ajax_store_visited_products_cart', 'store_visited_products_cart' );
add_action( 'wp_ajax_nopriv_store_visited_products_cart', 'store_visited_products_cart' );

function store_visited_products_cart() {
    if(!isset($_POST['product_id']))
        return;
    product_to_cookie($_POST['product_id'], '_cart', 3);
    exit;
}

function track_recently_view_product() {
    if ( ! is_singular( 'product' ) ) {
        return;
    }
    global $post;
    product_to_cookie($post->ID, '_single', 2);
}

add_action( 'template_redirect', 'track_recently_view_product', 20 );

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
/**
 * WooCommerce Loop Product Thumbs
 **/
 if ( ! function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {
    function woocommerce_template_loop_product_thumbnail() {
        echo woocommerce_get_product_thumbnail();
    } 
 }
/**
 * WooCommerce Product Thumbnail
 **/
 if ( ! function_exists( 'woocommerce_get_product_thumbnail' ) ) {
    
    function woocommerce_get_product_thumbnail( $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
        global $post, $woocommerce;
         $output = '<div class="product-image" data-product_id="'.$post->ID.'">';
            if ( has_post_thumbnail() ) {
                $output .= get_the_post_thumbnail( $post->ID, $size ); 
            } else {
                $output .= '<img src="'. woocommerce_placeholder_img_src() .'" alt="Placeholder" width="' . $placeholder_width . '" height="' . $placeholder_height . '" />';
            }
            $output .= '</div>';
            return $output;
    }
 }
?>