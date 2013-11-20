<?php
/**
 * Plugin Name: CGC Delete Users
 * Plugin URI: http://cgcookie.com
 * Description: Simpler Delete Users functionality from Network Admin Users screen.
 * Author: Brian DiChiara
 * Author URI: http://briandichiara.com
 * Version: 0.0.1
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

function cgcdu_ms_user_row_actions( $actions, $user ){
	$actions['delete'] = str_replace( 'action=deleteuser', 'action=cgcdu-delete-user', $actions['delete'] );
	return $actions;
}

add_filter( 'ms_user_row_actions', 'cgcdu_ms_user_row_actions', 10, 2 );

function cgcdu_handle_delete_user(){
	$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
	$user = isset( $_GET['id'] ) ? array( (int)sanitize_text_field( $_GET['id'] ) ) : array();

	// for bulk eidts
	if ( $action == 'allusers' ) {
		$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
		$user = isset( $_POST['allusers'] ) ? (array)$_POST['allusers'] : array();
	}

	if( $action != 'cgcdu-delete-user' )
		return;

	if ( ! ( current_user_can( 'manage_network_users' ) && current_user_can( 'delete_users' ) ) )
		wp_die( __( 'You do not have permission to access this page.' ) );

	foreach( $user as $user_id ){
		if ( ! current_user_can( 'delete_user', $user_id ) )
			continue;

		wpmu_delete_user( $user_id );
	}

	$var = count( $user ) <= 1 ? 'delete' : 'all_delete';

	wp_redirect( add_query_arg( array( 'updated' => 'true', 'action' => $var ), network_admin_url( 'users.php' ) ) );
	exit();
}

add_action( 'admin_init', 'cgcdu_handle_delete_user' );

function cgcdu_confirm_delete(){ ?>
	<script>jQuery(function($){
		$('a.delete').click(function(){
			return confirm('This cannot be undone. This will completely delete the user. Are you sure you want to delete this users account, profile, meta data, and posts?');
		});
		$('#form-user-list select[name="action"] option[value="delete"]').attr( 'value', 'cgcdu-delete-user' );
	});
	</script>
<?php }

add_action( 'admin_head', 'cgcdu_confirm_delete' );
