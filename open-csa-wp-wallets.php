<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	************
	WALLETS MENU
	************
*/

function open_csa_wp_wallets_menu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csa_data = get_user_meta( $user->ID, 'open-csa-wp_user', true )) || $csa_data['role'] != "administrator" )
	) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', OPEN_CSA_WP_DOMAIN ) );
	}

    ?>

    <select name="user-from">
    <?php open_csa_wp_select_users(null, 'blabla'); ?>
    </select>
<?php
}
?>