<?php
function update_vendor_role ($args,$form_items) {

	wp_update_user( array( 'ID' => $args['user_id'], 'role' => 'wc_product_vendors_admin_vendor' ) );

	$term_id = get_term_by('name',$args['vendor_name'],'wcpv_product_vendors'); 
	$term_id = $term_id->term_id;

	/*Update term meta*/
	$vendor_data['email'] 				= $args['user_email'];
	$vendor_data['profile'] 			= $args['vendor_desc'];
	$vendor_data['admins'] 				= $args['user_id'];
	$vendor_data['enable_bookings']		= 'yes';
	print_r($args);
	update_term_meta( $term_id, 'vendor_data', $vendor_data );
}

add_action('wcpv_shortcode_registration_form_process','update_vendor_role');
?>