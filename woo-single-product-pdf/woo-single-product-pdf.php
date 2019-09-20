<?php
/**
 * Plugin Name: Woocommerce product PDF
 * Plugin URI: http://wsm.eu/
 * Description: Download woocommerce single product details in pdf
 * Version: 1.0
 * Author: WSM
 * Author URI: http://wsm.eu/
 */

 if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    /* 
    **Add download button using meta field
    */
    add_action('woocommerce_product_meta_end','add_pdf_download_button' );
    function add_pdf_download_button($download_button) {

        $lang = get_bloginfo('language');
        $lang = preg_replace('/-.*/', '', $lang);

        echo'<a href="javascript:void(0);" product_id="'.get_the_ID().'" class="button generateSinglePDF" id="generateSinglePDF" with_logo="yes">'.(($lang != 'en') ? "Herunterladen " : "Download").'  <i class="fa fa-file-pdf-o"></i></a>';
        echo'<a href="javascript:void(0);" product_id="'.get_the_ID().'" class="button generateSinglePDF" id="generateSinglePDFNologo" style="margin-left:20px;" with_logo="no">'.(($lang != 'en') ? "Herunterladen2 " : "Download2").'  <i class="fa fa-file-pdf-o"></i></a>';
    }

    /*
    **Register js file to use ajax
    */
    function woo_download_pdf_enqueue_js() {

        if(is_product()):
        wp_enqueue_style( 'pdf_custom', plugin_dir_url(__FILE__) . 'css/pdf_css.css' );
        wp_enqueue_script( 'pdf_custom', plugin_dir_url(__FILE__) . '/js/pdf_custom.js', array( 'jquery' ), $woocommerce->version, true );
        wp_localize_script( 'pdf_custom', 'woo_doo_pdf', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ,'ajax_nonce' => wp_create_nonce('woo_download_pdf') ) );
        endif;

    }
    add_action('wp_enqueue_scripts', 'woo_download_pdf_enqueue_js');

    /*
    ** Ajax function to get all product data
    */

    add_action('wp_ajax_download_product_details_pdf', 'download_product_details_pdf');
    add_action('wp_ajax_nopriv_download_product_details_pdf', 'download_product_details_pdf');

    function download_product_details_pdf(){
        check_ajax_referer( 'woo_download_pdf', 'secure_download' );
        $product_id      = $_POST['product_id'];
        $checklogo       = $_POST['checklogo'];

        if(!empty($product_id)){
            require_once( 'woo-single-pdf-template.php' );
        } else {
            echo 'Something went wrong!!';
        }
        die();
    }
    /*
    ** Delete downloaded file
    */

    add_action('wp_ajax_delete_downloaded_pdf', 'delete_downloaded_pdf');
    add_action('wp_ajax_nopriv_delete_downloaded_pdf', 'delete_downloaded_pdf');

    function delete_downloaded_pdf(){
        check_ajax_referer( 'woo_download_pdf', 'secure_delete' );
        $DownloadedFile = $_POST['filepath'];
        if(wp_delete_file( $DownloadedFile )){
            echo "File deleted";
        } else {
            echo 'something went wrong';
        }
        die();
    }
} else {
    function woo_single_pdf_plugin_missing_notice() {
            echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Woocommerce product PDF requires WooCommerce to be installed and active.', 'woo-single-product-pdf' )) . '</p></div>';
        }
    add_action( 'admin_notices', 'woo_single_pdf_plugin_missing_notice');
}


?>