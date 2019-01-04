<?php
/*
Plugin Name: Woocommerce Author Rating
Plugin URI: https://abcd.com/
Description: Plugin to rate woocommerce authors.
Version: 1.0
Author: Mr.awesome
Author URI: https://mrawesome.com/
License: GPLv2
*/

/**
** Check if WooCommerce is active
**/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/*Create Database on active plugin*/
	global $jal_db_version;
	$jal_db_version = '1.0';

	function jal_install() {
		global $wpdb;
		global $jal_db_version;

		$table_name = $wpdb->prefix . 'buyer_rating';
		$table_name2 = $wpdb->prefix . 'user_selection';
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
			  `authorid` int(11) NOT NULL,
			  `buyerid` int(11) NOT NULL,
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

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		dbDelta( $sql2 );

		add_option( 'jal_db_version', $jal_db_version );
	}

	register_activation_hook( __FILE__, 'jal_install' );


	add_action('admin_menu', 'wc_author_rating');
	function wc_author_rating(){
	    add_menu_page('Author Rating', 'Author Rating', 'manage_options', 'get_all_users', 'get_all_users' );
	   // add_submenu_page('get-all-users', 'Select User to Rate', 'Select User to Rate', 'manage_options', 'my-menu' );
	}

	function get_all_users(){
		global $wpdb;
		global $wb;
		global $wp_roles;
     	$roles = $wp_roles->get_names(); 
     	$selectedUser = get_selected_user_role();
     	if($selectedUser){
     		$selectedVoter = explode(",", $selectedUser[0]->voter_role);
     	}
     	?>
     	<h2>User Rating Settings</h2>
     	<form id="user_roles" method="POST">     		
			<table class="form-table">
			    <tbody>
					<tr>
						<td><p>Select User To Rate</p></td>
					</tr>
					<tr>
						<td>
							<select name="user_role">
								<?php foreach ( $wp_roles->roles as $key=>$value ): ?>
								<option value="<?php echo $key; ?>"  <?php if(!empty($selectedUser[0]->user_role)) { echo ($selectedUser[0]->user_role == $key)? 'selected="selected"' : ''; } ?>><?php echo $value['name']; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><p>Select User Who can Rate(multiple can be selected)</p></td>
					</tr>
					<tr>
						<td>
							<select multiple="multiple" name="voter_role[]">
								<?php foreach ( $wp_roles->roles as $key=>$value ): ?>
								<?php $selected = in_array( $key, $selectedVoter ) ? ' selected="selected" ' : ''; echo $selected; ?>
								<option value="<?php echo $key; ?>"  <?php if(!empty($selected)) { echo $selected; } ?>><?php echo $value['name']; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>				
				</tbody>
			</table>
			<input class="button-primary" type="submit" name="Submit" value="Submit">
		</form>
<?php
		$userRole = '';
		if((!empty($_POST['user_role'])) && (isset($_POST)))  {
			
			$userRole = $_POST['user_role'];
			$voterRole = implode(",", $_POST['voter_role']); 

			if(empty($selectedUser[0]->user_role)){
				$wpdb->insert( $wpdb->prefix.'user_selection', array('user_role' => $userRole,'voter_role' => $voterRole),array( '%s','%s'));
			} else {
				$wpdb->update($wpdb->prefix.'user_selection',array('user_role' => $userRole,'voter_role' => $voterRole),array( 'id' => 1 ),array('%s'),array( '%d' ));
			}
		}
	}

function get_selected_user_role(){
		global $wpdb;
		$query = "SELECT user_role , voter_role FROM  ".$wpdb->prefix."user_selection WHERE id = '1'";
		return $wpdb->get_results($query);
}

	/* Register Buyer Ratings Menu in My account page */
	/*
	 * Step 1. Add Link (Tab) to My Account menu
	 */
	add_filter ( 'woocommerce_account_menu_items', 'add_buyer_rating_menu', 40 );
	function add_buyer_rating_menu( $menu_links ){
		$menu_links = array_slice( $menu_links, 0, 5, true ) 
		+ array( 'buyer-ratings' => 'Buyer Ratings' )
		+ array_slice( $menu_links, 5, NULL, true ); 
	
	/*Check If user is Allowed to vote*/
		$cUser = wp_get_current_user();
		$userCanRate = get_selected_user_role();
		if($userCanRate) {
		$CanVote = explode(",", $userCanRate[0]->voter_role);
			if(!in_array($cUser->roles[0], $CanVote)) {
	        	unset($menu_links['buyer-ratings']);
			}
		}
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
	$cUrrentuser 			= 	wp_get_current_user();
	$userToRate 			= 	get_selected_user_role();
	if($userToRate ) :
		$userCanAcessPage 	=  	explode(",", $userToRate[0]->voter_role);
	endif;
	
	if((isset($userCanAcessPage)) && (!in_array($cUrrentuser->roles[0], $userCanAcessPage))) {
		echo "<h3>cheating uhh !!!  ;) You are not Allowed to view this page.</h3>";
	} else {
		$userRoleToRate = '';
		 (!empty($userToRate)) ? $userRoleToRate = $userToRate[0]->user_role : "customer";

/*		 $args = array('role'=> $userRoleToRate,'order'=> 'DESC');*/

			global $wpdb;
			global $wb;

			$args1 = array(
			    'author'     =>  $cUrrentuser->ID,
			    'post_type'  => 'product',
			);

			$author_posts = get_posts( $args1 );
			$MyBuyers = '';
			if(!empty($author_posts))
			{
				$order_stats = array( 'wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed' );
				foreach ($author_posts as $value) {
					$OrderIdbyProduct = get_orders_ids_by_product_id($value->ID,$order_stats);
				}
				foreach ($OrderIdbyProduct as $OIdbyP) {
						$MyBuyers[] = get_customerorderid($OIdbyP);
				}
			}

			if(empty($MyBuyers)) {
				echo "<h3>No User found!!</h3>";
			} else {
				echo '<a class="all_buyers" href=?bid=all>All Buyers</a>';	
			}
			
			if((!empty($_GET['bid'])) && (in_array($_GET['bid'],$MyBuyers)))
			{
				$b_ID 						= $_GET['bid'];
				$userdata 					= get_userdata($b_ID);
				$user_nicename 				= (!empty($userdata)) ? $userdata->user_nicename : '';

				$result 					= get_buyer_ratings($b_ID);
				$result_review_comment 		= get_buyer_review_comment($b_ID);
				$review_comment 			= (!empty($result_review_comment[0]->review_comment)) ? $result_review_comment[0]->review_comment : '';
				$average_rating 			= (!empty($result)) ? $result[0]->average_rating : '';
				$rating_number  			= (!empty($result[0]->rating_number)) ? $result[0]->rating_number : '';

				echo '<form id="author_review_submit" method="POST"><div class="buyer_star_rating_wrapper">';
			   	echo '<span class="buyer_nickname">Rate '.$user_nicename.'</span>
			   			<input name="rating" value="'.intval(floor($average_rating)).'" id="rating_star_'.$b_ID.'" type="hidden" postID="1" buyerID="'.$b_ID.'" ratingAVG="'.$average_rating.'" />';
			   	if(empty($average_rating)){
			   		echo '<span>No Ratings !!</span>';
			   	} else {
			   		echo'<div class="overall-rating">(Average Rating <span id="avgrat">'.$average_rating.'</span>
		    		 Based on <span id="totalrat">'.$rating_number.'</span> rating)</span></div>';	    	
			   	}
			   	echo '<div class="review_comment">
			   			<br/>
			   			<label>Comment Your Review</label>
			   			<textarea name="review_comment" id="review_comment">'.$review_comment.'</textarea>
			   		  </div>';
			   	echo '<br/><input type="submit" name="Submit" value="Submit Review" id="Submit">';
			   	echo  '</div>';

			   	$buyer_all_rating 					= buyer_all_ratings($b_ID);
			   	if(!empty($buyer_all_rating)) {
				   	echo '<div class="all_buyer_review"><h3>Reviews</h3>';
				   	foreach ($buyer_all_rating as $bRating) {
				   		$bRating_number 			= $bRating->total_points;
				   		$ratingAuthorId 			= $bRating->authorid;

				   		$authorUserdata 			= get_userdata($ratingAuthorId);
						$ratingAuthor 				= (!empty($authorUserdata)) ? $authorUserdata->user_nicename : '';

				   		echo '<div class="buyer_star_rating"><div class="buyer_name">'.$ratingAuthor.'</div>
				   			<div class="buyer_rating_comment_wrapper"><ul class="buyer_rating_ul">';
				   		for($i=0;$i<=4;$i++)
				   		{
				   			if(($bRating_number <= $i)){
				   				echo '<span class="dashicons dashicons-star-empty"></span>';
				   			} else {
				   				echo '<span class="dashicons dashicons-star-filled"></span>';
				   			}			   					
				   		}
				   		echo '</ul>
				   			  <div class="buyer_review_comment">'.$bRating->review_comment.'</div></div>';
				   	}
				   	echo '</div>';
				}
		    	
			} else {
				echo '<div class="buyers_list">';
				if(!empty($MyBuyers)) {
					foreach ($MyBuyers as $key => $value) {
						echo '<a class="buyer_link" href=?bid='.$value.'>Rate buyer_'.$value.'</a>';
					}
				}
				echo '</div></form>';
			}
		}
}

add_shortcode( 'author-rating', 'add_buyer_rating_menu_endpoints' );

	/*Inclue CSS and JS file*/

	add_action('wp_enqueue_scripts', 'buyer_ratings_enqueue_func');
	function buyer_ratings_enqueue_func() {
	    wp_register_style( 'buyer-rating', plugins_url('css/rating.css',__FILE__ ));
	    wp_enqueue_style( 'buyer-rating' );
	    wp_enqueue_script( 'jquery-lib',  'https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', array( 'jquery' ) );
	    wp_enqueue_script( 'buyer-rating',  plugins_url('js/rating.js',__FILE__ ), array( 'jquery' ) ); 
	    wp_enqueue_script( 'rating_custom',  plugins_url('js/rating_custom.js',__FILE__ ), array( 'jquery' ) );    
	    wp_localize_script( 'rating_custom', 'my_ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );   
	}


	/* Fetch Rating for buyer*/

	function get_buyer_ratings($bid)
	{
		global $wpdb;
		$query = "SELECT buyerid, SUM(rating_number) as rating_number, AVG(FORMAT((total_points / rating_number),1)) as average_rating , authorid FROM  ".$wpdb->prefix."buyer_rating WHERE buyerid = $bid AND status = 1 GROUP BY buyerid";
		return $wpdb->get_results($query);
	}

	function get_buyer_review_comment($bid)
	{
		$Ccuthorid = current_author();
		global $wpdb;
		$query = "SELECT review_comment FROM  ".$wpdb->prefix."buyer_rating WHERE buyerid = $bid AND authorid = $Ccuthorid";
		return $wpdb->get_results($query);
	}

	add_action('wp_ajax_rate_buyer', 'rate_buyer');
	add_action('wp_ajax_nopriv_rate_buyer', 'rate_buyer');

	function rate_buyer() {

		if(!empty($_POST['points'])){
		    $authorid = current_author();
		    $buyerid = $_POST['bid'];
		    $rvw_cmt = $_POST['review_comment'];
		    $rating_default_number = 1;
		    $points = $_POST['points'];
			global $wpdb;
			
		    //Check the rating row with same post ID
		    $prevRatingQuery = "SELECT * FROM ". $wpdb->prefix."buyer_rating WHERE authorid = ".$authorid." AND buyerid = ".$buyerid;
		    $prevRatingResult = $wpdb->get_results($prevRatingQuery);

		    if(count($prevRatingResult)> 0):
		        $rating_default_number = $prevRatingResult['rating_number'] + $rating_default_number;
		        $points = $prevRatingResult['total_points'] + $points;

			    //Update rating data into the database       
			    $wpdb->update( $wpdb->prefix.'buyer_rating', array('rating_number' => $rating_default_number,'total_points' => $points,'review_comment'=>$rvw_cmt,'created' => date("Y-m-d H:i:s"),'modified' => date("Y-m-d H:i:s"),),array( 'authorid' => $authorid, 'buyerid' => $buyerid),array( '%s', '%d','%s','%s','%s'),array( '%d','%d'));
		    else:
		        //Insert rating data into the database
		        $wpdb->insert( $wpdb->prefix.'buyer_rating', array('authorid' => $authorid,'buyerid' => $buyerid,'review_comment'=>$rvw_cmt, 'rating_number' => $rating_default_number,'total_points' => $points,'created' => date("Y-m-d H:i:s"),'modified' => date("Y-m-d H:i:s"),),array( '%s', '%d','%s','%s','%s'));	        
		    endif;
		    
		    //Fetch rating deatails from database
		    $query2 = "SELECT rating_number, FORMAT((total_points / rating_number),1) as average_rating FROM ". $wpdb->prefix."buyer_rating WHERE authorid = ".$authorid." AND status = 1";
		    
		    $ratingRow = $wpdb->get_results($query2);
		    if(count($ratingRow)>0){
		        $ratingRow['status'] = 'ok';
		    }else{
		        $ratingRow['status'] = 'err';
		    }
		    echo $ratingRow['status'];
		    die();
		}
	}

	function current_author(){
		$CurrAuthor = wp_get_current_user();
		return $CurrAuthor->ID;
	}

	function buyer_all_ratings($bid){
		global $wpdb;
		$all_rating_query = "SELECT total_points,review_comment , authorid FROM ". $wpdb->prefix."buyer_rating WHERE buyerid=".$bid;
		return $wpdb->get_results($all_rating_query);
	}


/*Get Order ID by product id*/
function get_orders_ids_by_product_id( $product_id, $order_status ){
    global $wpdb;

    $orderids = $wpdb->get_col("
        SELECT order_items.order_id
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        WHERE posts.post_type = 'shop_order'
        AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
        AND order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value = '$product_id'
    ");

    return $orderids;
}

/*Get customers ID by order id*/

function get_customerorderid($order_id){

    // Get an instance of the WC_Order object
    $order = wc_get_order($order_id);

    // Get the user ID from WC_Order methods
    $user_id = $order->get_user_id(); // or $order->get_customer_id();

    return $user_id;
}

} else {

	function woocommerce_plugin_missing_notice() {
			echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Author Rating requires WooCommerce to be installed and active.', 'wc-author-rating' )) . '</p></div>';
		}
	add_action( 'admin_notices', 'woocommerce_plugin_missing_notice');
}

/*Load Dashicons for All user roles*/
function ww_load_dashicons(){
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'ww_load_dashicons');
?>