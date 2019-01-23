<?php
/*
Plugin Name: Review Your Customers
Plugin URI: https://abcd.com/
Description: Plugin to rate and review WooCommerce customers.
Version: 1.0
Author: Mr.awesome
Author URI: https://mrawesome.com/
License: GPLv2
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 ** Check if WooCommerce is active
 *
 */

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	/*Create Database on active plugin*/
	function jal_install()
	{
		global $jal_db_version;
		$jal_db_version = '1.0';
		global $wpdb;
		$table_name = $wpdb->prefix . 'customer_rating';
		$table_name2 = $wpdb->prefix . 'user_selection';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
	  	`rating_id` int(11) NOT NULL AUTO_INCREMENT,
	  	`authorid` int(11) NOT NULL,
		`customerid` int(11) NOT NULL,
		`rating_number` int(11) NOT NULL,
		`review_comment` varchar(255) NOT NULL,
		`total_points` int(11) NOT NULL,
		`created` datetime NOT NULL,
		`modified` datetime NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Block, 0 = Unblock',
		PRIMARY KEY (`rating_id`)
		) $charset_collate;";
		$sql2 = "CREATE TABLE $table_name2 (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_role` varchar(50) NOT NULL,
		`voter_role` varchar(100) NOT NULL,
		PRIMARY KEY (`id`)
		) $charset_collate;";
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($sql);
		dbDelta($sql2);
		add_option('jal_db_version', $jal_db_version);
	}

	register_activation_hook(__FILE__, 'jal_install');
	

	function get_selected_user_role()
	{
		global $wpdb;
		$query = "SELECT user_role , voter_role FROM  " . $wpdb->prefix . "user_selection WHERE id = '1'";
		return $wpdb->get_results($query);
	}

	/* Register Review Your Customer tab in My Account page */
	/*
	* Step 1. Add Link (Tab) to My Account menu
	*/
	add_filter('woocommerce_account_menu_items', 'add_customer_rating_menu', 40);
	function add_customer_rating_menu($menu_links)
	{
		$menu_links = array_slice($menu_links, 0, 5, true) + array(
			'customer-ratings' => 'Booking Reviews'
		) + array_slice($menu_links, 5, NULL, true);
		//Check If user is allowed to review
		$current_user = wp_get_current_user();
		$userCanRate = 'administrator,wc_product_vendors_admin_vendor,wc_product_vendors_manager_vendor';
		if ($userCanRate) {
			$CanVote = explode(",", $userCanRate);
			if (!in_array($current_user->roles[0], $CanVote)) {
				unset($menu_links['customer-ratings']);
			}
		}

		return $menu_links;
	}

	/*
	* Step 2. Register Permalink Endpoint
	*/
	add_action('init', 'add_customer_rating_endpoints');
	function add_customer_rating_endpoints()
	{
		add_rewrite_endpoint('customer-ratings', EP_ROOT | EP_PAGES);
	}
	
	
	/*
	 * Change endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	function my_custom_endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars['customer-ratings'] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Booking Reviews', 'woocommerce' );

			remove_filter( 'the_title', 'my_custom_endpoint_title' );
		}

		return $title;
	}

	add_filter( 'the_title', 'my_custom_endpoint_title' );
	

	/*
	* Step 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
	*/
	add_action('woocommerce_account_customer-ratings_endpoint', 'add_customer_rating_menu_endpoints');
	function add_customer_rating_menu_endpoints()
	{
		$current_user = wp_get_current_user();
		/*$userToRate = get_selected_user_role();*/
		$userCanAcessPage = array('administrator','wc_product_vendors_admin_vendor','wc_product_vendors_manager_vendor');
		if ((isset($userCanAcessPage)) && (!in_array($current_user->roles[0], $userCanAcessPage))) {
			echo "<h3>Cheating uhh !!!  ;) You are not allowed to view this page.</h3>";
		}
		else {
			global $userRole;
			global $wpdb;
			$userRoleToRate = 'customer';
			/*(!empty($userToRate)) ? $userRoleToRate = $userToRate[0]->user_role : "customer";*/
			
			// Get all product ids for the logged in vendor
			$product_ids = WC_Product_Vendors_Utils::get_vendor_product_ids();
			$booking_ids = array();
			// Get all order ids for vendor products
			if (!empty($product_ids)) {
				foreach($product_ids as $product_id) {
					$order_ids_by_product = get_order_ids_by_product_id($product_id);
					//print_r($order_ids_by_product);
					if(!empty($order_ids_by_product)) {
						$booking_ids[] = get_booking_ids($order_ids_by_product);
					}					
				}
			}
			$booking_ids = array_flatten($booking_ids);
			
			/*print_r($booking_ids);*/

			if (empty($booking_ids)) {
				echo '<p>' . __('There are no bookings available.', 'woocommerce-product-vendors') . '</p>';
			}

			if ((!empty($_GET['bid'])) && (in_array($_GET['bid'], $booking_ids))) {
				$b_ID = $_GET['bid'];
				$userdata = get_userdata($b_ID);
				$user_nicename = (!empty($userdata)) ? $userdata->user_nicename : '';
				$result = get_customer_ratings($b_ID);
				$result_review_comment = get_customer_review_comment($b_ID);
				$review_comment = (!empty($result_review_comment[0]->review_comment)) ? $result_review_comment[0]->review_comment : '';
				$average_rating = (!empty($result)) ? $result[0]->average_rating : '';
				$rating_number = (!empty($result[0]->rating_number)) ? $result[0]->rating_number : '';
				echo '<form id="author_review_submit" method="POST"><div class="customer_star_rating_wrapper">';
				echo '<span class="customer_nickname">Your rating<span class="required">*</span> ' . $user_nicename . '</span>
			   			<input name="rating" value="' . intval(floor($average_rating)) . '" id="rating_star_' . $b_ID . '" type="hidden" postID="1" customerID="' . $b_ID . '" ratingAVG="' . $average_rating . '" />';

				echo '<div class="review_comment">
			   			<br/>
			   			<label>Your review (optional)</label>
			   			<textarea name="review_comment" id="review_comment">' . $review_comment . '</textarea>
			   		  </div>';
				echo '<br/><input type="submit" name="Submit" value="Submit" id="Submit">';
				echo '<p><a class="all_customers" href=?bid=all>Back to all reviews</a></p>';
				echo '</div>';
			} else {
				echo '<div class="customers_list">';
				foreach($booking_ids as $booking_id) {
					/*foreach($booking_id as $booking_id2) {*/
						$booking 			= new WC_Booking($booking_id);
						$bookingProductID 	= $booking->get_product()->get_id();

						if (($booking->get_status() == 'complete') && (in_array($bookingProductID, $product_ids))) {
							$start_date 	= $booking->get_start_date();
							$end_date 		= $booking->get_end_date();
							echo '<div style="float:left;width:100%;"><span style="margin-right:10px;"></span><a class="customer_link" href=?bid=' . $booking_id . '>ID #' . $booking_id . '</a><span style="margin-left:10px;"><strong>Booking Dates: </strong></span><span class="start_date">' . $start_date . '  -  </span><span class="end_date">' . $end_date . '</span><span class="brating">'.customer_all_ratings($booking_id).'</span></div>';
						}
					/*}*/
				}

				echo '</div></form>';
			}
		}
	}

	add_shortcode('ryc-reviews-vendor', 'add_customer_rating_menu_endpoints');
	
	/*Include CSS and JS file*/
	add_action('wp_enqueue_scripts', 'customer_ratings_enqueue_func');
	function customer_ratings_enqueue_func()
	{
		/*if (is_account_page()){*/
			wp_register_style('customer-rating', plugins_url('css/rating.css', __FILE__));
			wp_enqueue_style('customer-rating');
			//this line breaks the vendor dashboard's features if run globally. Please check.
			/*wp_enqueue_script( 'jquery-lib',  'https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', array( 'jquery' ) ); */

			wp_enqueue_script('customer-rating', plugins_url('js/rating.js', __FILE__) , array(
			'jquery'
			));
			wp_enqueue_script('rating_custom', plugins_url('js/rating_custom.js', __FILE__) , array(
			'jquery'
			));
			wp_localize_script('rating_custom', 'my_ajax_object', array(
			'ajax_url' => admin_url('admin-ajax.php')
			));
		/*}*/
		
	}
	/*Load dashicons for all user roles*/
	function ww_load_dashicons()
	{
		wp_enqueue_style('dashicons');
	}
	
	add_action('wp_enqueue_scripts', 'ww_load_dashicons');	
	

	/* Fetch existing ratings for customer*/
	function get_customer_ratings($bid)
	{
		global $wpdb;
		$query = "SELECT customerid, SUM(rating_number) as rating_number, AVG(FORMAT((total_points / rating_number),1)) as average_rating , authorid FROM  " . $wpdb->prefix . "customer_rating WHERE customerid = $bid AND status = 1 GROUP BY customerid";
		return $wpdb->get_results($query);
	}

	function get_customer_review_comment($bid)
	{
		$currentAuthorId =  get_current_user_id();
		global $wpdb;
		$query = "SELECT review_comment FROM  " . $wpdb->prefix . "customer_rating WHERE customerid = $bid AND authorid = $currentAuthorId";
		return $wpdb->get_results($query);
	}

	add_action('wp_ajax_rate_customer', 'rate_customer');
	add_action('wp_ajax_nopriv_rate_customer', 'rate_customer');
	function rate_customer()
	{
		if (!empty($_POST['points'])) {
			$authorid =  get_current_user_id();
			$customerid = $_POST['bid'];
			$rvw_cmt = $_POST['review_comment'];
			$rating_default_number = 1;
			$points = $_POST['points'];
			global $wpdb;

			// Check the rating row with same post ID
			$prevRatingQuery = "SELECT * FROM " . $wpdb->prefix . "customer_rating WHERE authorid = " . $authorid . " AND customerid = " . $customerid;
			$prevRatingResult = $wpdb->get_results($prevRatingQuery);
			if (count($prevRatingResult) > 0):
				$rating_default_number = $prevRatingResult['rating_number'] + $rating_default_number;
				$points = $prevRatingResult['total_points'] + $points;

				// Update rating data into the database
				$wpdb->update($wpdb->prefix . 'customer_rating', array(
					'rating_number' => $rating_default_number,
					'total_points' => $points,
					'review_comment' => $rvw_cmt,
					'created' => date("Y-m-d H:i:s") ,
					'modified' => date("Y-m-d H:i:s") ,
				) , array(
					'authorid' => $authorid,
					'customerid' => $customerid
				) , array(
					'%s',
					'%d',
					'%s',
					'%s',
					'%s'
				) , array(
					'%d',
					'%d'
				));
			else:

				// Insert rating data into the database
				$wpdb->insert($wpdb->prefix . 'customer_rating', array(
					'authorid' => $authorid,
					'customerid' => $customerid,
					'review_comment' => $rvw_cmt,
					'rating_number' => $rating_default_number,
					'total_points' => $points,
					'created' => date("Y-m-d H:i:s") ,
					'modified' => date("Y-m-d H:i:s") ,
				) , array(
					'%s',
					'%d',
					'%s',
					'%s',
					'%s'
				));
			endif;

			// Fetch rating deatails from database
			$query2 = "SELECT rating_number, FORMAT((total_points / rating_number),1) as average_rating FROM " . $wpdb->prefix . "customer_rating WHERE authorid = " . $authorid . " AND status = 1";
			$ratingRow = $wpdb->get_results($query2);
			if (count($ratingRow) > 0) {
				$ratingRow['status'] = 'ok';
			}
			else {
				$ratingRow['status'] = 'err';
			}

			echo $ratingRow['status'];
			die();
		}
	}

	function customer_all_ratings($bid)
	{
		global $wpdb;
		$booking_review = '';
		$all_rating_query = "SELECT total_points,review_comment , authorid FROM " . $wpdb->prefix . "customer_rating WHERE customerid=" . $bid;
		$result = $wpdb->get_results($all_rating_query);
		if (!empty($result)) {
			$booking_review = '<div class="all_customer_review">';
			foreach($result as $bRating) {
				$bRating_number = $bRating->total_points;
				$ratingAuthorId = $bRating->authorid;
				$authorUserdata = get_userdata($ratingAuthorId);
				$ratingAuthor = (!empty($authorUserdata)) ? $authorUserdata->user_nicename : '';
				$booking_review.= '<div class="customer_star_rating"><div class="customer_name">' . $ratingAuthor . '</div>
		   			<div class="customer_rating_comment_wrapper"><ul class="customer_rating_ul">';
				for ($i = 0; $i <= 4; $i++) {
					if (($bRating_number <= $i)) {
						$booking_review.= '<span class="dashicons dashicons-star-empty"></span>';
					}
					else {
						$booking_review.= '<span class="dashicons dashicons-star-filled"></span>';
					}
				}

				$booking_review.= '</ul>';
		   			  /*<div class="customer_review_comment">' . $bRating->review_comment . '</div></div>';*/
			}

			$booking_review.= '</div>';
		}

		return $booking_review;
	}

	function booking_review_exists($bookingId)
	{
		global $wpdb;
		$booking_review = '';
		$booking_check_query = "SELECT total_points,review_comment , authorid FROM " . $wpdb->prefix . "customer_rating WHERE customerid=" . $bookingId;
		$booking_exists = $wpdb->get_results($booking_check_query);
		if (!empty($booking_exists)) {
			return 'review_exists';
		}
		else {
			return 'no_review';
		}
	}

	/*Get Order ID by product id*/
	function get_order_ids_by_product_id($product_id)
	{
		global $wpdb;
		$orderids = $wpdb->get_col("
        SELECT order_items.order_id
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        WHERE posts.post_type = 'shop_order'
        AND order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value = '$product_id'
    ");
		return $orderids;
	}

	/*Get booking ids from order id*/
	function get_booking_ids($order_id)
	{
		$booking_data = new WC_Booking_Data_Store();
		$booking_ids = $booking_data->get_booking_ids_from_order_id($order_id);
		return $booking_ids;
	}
	function buyer_all_ratings($bid){
		global $wpdb;
		$all_rating_query = "SELECT total_points,review_comment , authorid FROM ". $wpdb->prefix."buyer_rating WHERE buyerid=".$bid;
		return $wpdb->get_results($all_rating_query);
	}
}
else {
	function woocommerce_plugin_missing_notice()
	{
		echo '<div class="error"><p>' . sprintf(esc_html__('Review Your Customer requires WooCommerce to be installed and active.', 'wc-author-rating')) . '</p></div>';
	}

	add_action('admin_notices', 'woocommerce_plugin_missing_notice');
}

function array_flatten($array) { 
  if (!is_array($array)) { 
    return FALSE; 
  } 
  $result = array(); 
  foreach ($array as $key => $value) { 
    if (is_array($value)) { 
      $result = array_merge($result, array_flatten($value)); 
    } 
    else { 
      $result[$key] = $value; 
    } 
  } 
  return $result; 
} 
?>