<?php
/*
Plugin Name: WooCommerce - Fyler of Last visited products
Plugin URL: https://www.atsw-anhaenger.at
Description: Plugin allows an admin to give any amount of discount in percentage to the most relevant products which user refers to. Here user can be either visitor or can be a registered user. This plugin automatically recognize the most relevant products user vise.
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

/** Setting menu link */
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'discount_page_settings_link');
function discount_page_settings_link( $links ) {
    $links[] = '<a href="' .
        admin_url( 'admin.php?page=flyer-settings' ) .
        '">' . __('Settings') . '</a>';
    return $links;
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
     
    if ( $order ) {  
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


add_filter( 'woocommerce_get_price_html', 'kd_custom_price_message' );
add_filter( 'woocommerce_cart_item_price', 'kd_custom_price_message' );
add_filter( 'woocommerce_cart_item_subtotal', 'kd_custom_price_message' ); // added
add_filter( 'woocommerce_cart_subtotal', 'kd_custom_price_message' ); // added
add_filter( 'woocommerce_cart_total', 'kd_custom_price_message' ); // added
function kd_custom_price_message( $price ) {
    
    global $post;
    $post_id = $post->ID;
    $prices = get_post_meta($post->ID, '_regular_price');
    
    if(isset($_GET['dis']) && !empty($_GET['dis'])) {
        WC()->session->set('post_id', $post->ID);
        WC()->session->set('dis', base64_decode($_GET['dis']) );
        return $price .'</br><p>Discount apply on checkout.</p>';
    } else {
        return $price;
    }
}


/* Set discount price for add to cart item */
function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
    if(!empty(WC()->session->get('dis')) && WC()->session->get('post_id') == $product_id) {

     $product = wc_get_product( $product_id );
     $price = $product->get_price();
     
     $discount = (WC()->session->get('dis') / 100) *  $price;
     $cart_item_data['dis_price'] = $price - $discount;

     return $cart_item_data;
    }
}
add_filter( 'woocommerce_add_cart_item_data', 'add_cart_item_data', 10, 3 );

function before_calculate_totals( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    foreach( $cart_obj->get_cart() as $key=>$value ) {
        if( isset( $value['dis_price'] ) ) {
            $price = $value['dis_price'];
            $value['data']->set_price( ( $price ) );
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );
?>