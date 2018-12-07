<?php
/*
Plugin Name: Woocommerce Author Rating
Plugin URI: https://abcd.com/
Description: Plugin to rate woocommerce authors.
Version: 1.0
Author: Mr.awesome
Author URI: https://abcd.com/
License: GPLv2
*/

/*Create Database on active plugin*/

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'buyer_rating';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
		  `authorid` int(11) NOT NULL,
		  `buyerid` int(11) NOT NULL,
		  `rating_number` int(11) NOT NULL,
		  `total_points` int(11) NOT NULL,
		  `created` datetime NOT NULL,
		  `modified` datetime NOT NULL,
		  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Block, 0 = Unblock',
		   PRIMARY KEY (`rating_id`)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

register_activation_hook( __FILE__, 'jal_install' );

/* Register Buyer Ratings Menu in My account page */
/*
 * Step 1. Add Link (Tab) to My Account menu
 */
add_filter ( 'woocommerce_account_menu_items', 'add_buyer_rating_menu', 40 );
function add_buyer_rating_menu( $menu_links ){
 
	$menu_links = array_slice( $menu_links, 0, 5, true ) 
	+ array( 'buyer-ratings' => 'Buyer Ratings' )
	+ array_slice( $menu_links, 5, NULL, true ); 
	return $menu_links; 
}
/*
 * Step 2. Register Permalink Endpoint
 */
add_action( 'init', 'add_buyer_rating_endpoints' );
function add_buyer_rating_endpoints() {
 
	add_rewrite_endpoint( 'buyer-ratings', EP_PAGES );
 
}
/*
 * Step 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action( 'woocommerce_account_buyer-ratings_endpoint', 'add_buyer_rating_menu_endpoints' );
function add_buyer_rating_menu_endpoints() {
 
 $args = array(
    'role'    => 'shop_manager',
    'order'   => 'DESC'
);

global $wpdb;
$query = "SELECT rating_number, FORMAT((total_points / rating_number),1) as average_rating , authorid FROM woo_buyer_rating WHERE authorid = 1 AND status = 1 GROUP BY authorid";
$result = $wpdb->get_results($query);
print_r($result);
	$users = get_users($args);
	foreach ($users as $user) 
	{
	   echo "buyer_".$user->ID;
	   echo '<input name="rating" value="0" id="rating_star" type="hidden" postID="1" />';
    	echo'<div class="overall-rating">(Average Rating <span id="avgrat">'.$result['average_rating'].'</span>
    		 Based on <span id="totalrat">'.$result['rating_number'].'</span> rating)</span></div>';
	} 
}

/*Inclue CSS and JS file*/

add_action('wp_enqueue_scripts', 'buyer_ratings_enqueue_func');
function buyer_ratings_enqueue_func() {
    wp_register_style( 'buyer-rating', plugins_url('css/rating.css',__FILE__ ));
    wp_enqueue_style( 'buyer-rating' );
    wp_enqueue_script( 'jquery-lib',  'https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', array( 'jquery' ) );
   /* wp_enqueue_script( 'rating_custom',  plugins_url('js/rating_custom.js',__FILE__ ), array( 'jquery' ) );*/
    wp_enqueue_script( 'buyer-rating',  plugins_url('js/rating.js',__FILE__ ), array( 'jquery' ) );
    
}

/* Ajax URL*/

function my_enqueue() {

    wp_enqueue_script( 'ajax-script', plugins_url('js/rating_custom.js',__FILE__ ), array('jquery') );

    wp_localize_script( 'ajax-script', 'my_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue' );

/* Fetch Rating for buyer*/

/*function get_buyer_ratings($bid)
{
	global $wpdb;
	$query = "SELECT rating_number, FORMAT((total_points / rating_number),1) as average_rating FROM woo_buyer_rating WHERE authorid = 1 AND status = 1";
	$result = $wpdb->get_results($query);
	return $result;
}*/


add_action('wp_ajax_rate_buyer', 'rate_buyer');
add_action('wp_ajax_nopriv_rate_buyer', 'rate_buyer');

function rate_buyer() {

	if(!empty($_POST['points'])){

    $authorid = '1'; //$_POST['authorid'];
    $buyerid = '4';
    $rating_default_number = 1;
    $points = $_POST['points'];
	global $wpdb;
	
    //Check the rating row with same post ID
    $prevRatingQuery = "SELECT * FROM woo_buyer_rating WHERE authorid = ".$authorid;
    $prevRatingResult = $wpdb->get_results($prevRatingQuery);

    if(count($prevRatingResult)> 0):
        $rating_default_number = $prevRatingResult['rating_number'] + $rating_default_number;
        $points = $prevRatingResult['total_points'] + $points;

	    //Update rating data into the database       
	    $wpdb->update('woo_buyer_rating', array('authorid' => $authorid,'buyerid' => $buyerid, 'rating_number' => $rating_default_number,'total_points' => $points,'created' => date("Y-m-d H:i:s"),'modified' => date("Y-m-d H:i:s"),),array( 'authorid' => $authorid ),array( '%s', '%d','%s','%s','%s'),array( '%d'));

    else:
        //Insert rating data into the database
        $wpdb->insert('woo_buyer_rating', array('authorid' => $authorid,'buyerid' => $buyerid, 'rating_number' => $rating_default_number,'total_points' => $points,'created' => date("Y-m-d H:i:s"),'modified' => date("Y-m-d H:i:s"),),array( '%s', '%d','%s','%s','%s'));
    endif;
    
    //Fetch rating deatails from database
    $query2 = "SELECT rating_number, FORMAT((total_points / rating_number),1) as average_rating FROM woo_buyer_rating WHERE authorid = ".$authorid." AND status = 1";
    
    $ratingRow = $wpdb->get_results($query2);
    if(count($ratingRow)>0){
        $ratingRow['status'] = 'ok';
    }else{
        $ratingRow['status'] = 'err';
    }    
    //Return json formatted rating data
    echo $ratingRow['status'];
    die();

}
}

?>