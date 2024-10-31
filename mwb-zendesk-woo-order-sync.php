<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link https://wpswings.com/
 * @since 1.0.0
 * @package mwb-zendesk-woo-order-sync
 *
 * @wordpress-plugin
 * Plugin Name: Order Sync with Zendesk for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/mwb-zendesk-woo-order-sync/
 * Description: Sends your WooCommerce order details to your Zendesk account.
 * Version: 2.1.2
 * Author: WP Swings
 * Author URI: https://wpswings.com/
 * License: GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Requires Plugins:  woocommerce
 * Text Domain: zndskwoo
 * Tested up to: 6.6.2
 * WC tested up to: 9.3.3
 * Domain Path: /languages
 */

use Stripe\Issuing\Authorization;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$activated = true;

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	$activated = false;
}
/**
 * Check if WooCommerce is active
 *
 * @since 1.0.0
 */

if ( $activated ) {

	if ( ! defined( 'MWB_ZENDESK_PREFIX' ) ) {
		define( 'MWB_ZENDESK_PREFIX', 'mwb_zendesk' );
	}

	if ( ! defined( 'MWB_ZENDESK_DIR' ) ) {
		define( 'MWB_ZENDESK_DIR', __DIR__ );
	}

	if ( ! defined( 'MWB_ZENDESK_DIR_URL' ) ) {
		define( 'MWB_ZENDESK_DIR_URL', plugin_dir_url( __FILE__ ) );
	}

	if ( ! defined( 'MWB_ZENDESK_DIR_PATH' ) ) {
		define( 'MWB_ZENDESK_DIR_PATH', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'MWB_ZENDESK_VERSION' ) ) {
		define( 'MWB_ZENDESK_VERSION', '2.1.2' );
	}

	register_activation_hook( __FILE__, 'mwb_zndsk_activation' );
	register_deactivation_hook( __FILE__, 'mwb_zndsk_deactivation' );
	add_action( 'wp_loaded', 'mwb_zndsk_activation' );

	/**
	 * Adding custom setting links at the plugin activation list.
	 *
	 * @param array  $links_array array containing the links to plugin.
	 * @param string $plugin_file_name plugin file name.
	 * @return array
	 */
	function zndsk_custom_settings_at_plugin_tab( $links_array, $plugin_file_name ) {

		if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {

			$links_array[] = '<a href="https://docs.wpswings.com/order-sync-with-zendesk-for-woocommerce/?utm_source=wpswings-zendesk-doc&utm_medium=zendesk-org-backend&utm_campaign=zendesk-doc" target="_blank"><img src="' . MWB_ZENDESK_DIR_URL . 'assets/images/Documentation.svg" style="vertical-align: middle;display: inline-block;width: 15px;max-width: 100%;margin: 0 5px;"></i>' . esc_html__( 'Documentation', 'zndskwoo' ) . '</a>';
			$links_array[] = '<a href="https://wpswings.com/submit-query/" target="_blank"><img src="' . MWB_ZENDESK_DIR_URL . 'assets/images/Support.svg" style="vertical-align: middle;display: inline-block;width: 15px;max-width: 100%;margin: 0 5px;"></i>' . esc_html__( 'Support', 'zndskwoo' ) . '</a>';
			$links_array[] = '<a href="https://wpswings.com/woocommerce-services/?utm_source=zendesk-services&utm_medium=zendesk-org-backend&utm_campaign=woocommerce-services" target="_blank"><img src="' . MWB_ZENDESK_DIR_URL . 'assets/images/Services.svg" style="vertical-align: middle;display: inline-block;width: 15px;max-width: 100%;margin: 0 5px;"></i>' . esc_html__( 'Services', 'zndskwoo' ) . '</a>';

		}

		return $links_array;
	}
	add_filter( 'plugin_row_meta', 'zndsk_custom_settings_at_plugin_tab', 10, 2 );


	/**
	 * Activation hook
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_activation() {
		do_action( 'mwb_zndsk_init' );
	}
	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
			}
		}
	);
	/**
	 * Deactivation hook
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_deactivation() {
	}
	/**
	 * Permission check
	 *
	 * @since    1.0.0
	 * @param string $request sends request as true.
	 * @return boolean true
	 */
	function mwb_zndsk_get_items_permissions_check( $request ) {

		return true;
	}
	/**
	 * Add connect api file
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_add_api_file_for_plugin() {
		// including supporting file of plugin.
		include_once MWB_ZENDESK_DIR . '/class-mwb-zendesk-connect-api.php';
		$mwb_zndsk_instance = MWB_ZENDESK_Connect_Api::get_instance();
		add_action( 'rest_api_init', array( $mwb_zndsk_instance, 'mwb_zndsk_register_routes' ) );
	}

	add_action( 'plugins_loaded', 'mwb_zndsk_add_api_file_for_plugin' );
	/**
	 * Enqueue scripts and styles
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_enqueue_script() {
		$screen        = get_current_screen();
		$valid_screens = array(
			'toplevel_page_mwb-zendesk-order-sync',
			'zendesk-order-sync_page_mwb-zendesk-order-config',

		);

		$hpos_screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		$hpos_screen = array( $hpos_screen );

		$valid_screens = array_merge( $valid_screens, $hpos_screen );
		if ( ! empty( $screen->id ) && in_array( $screen->id, $valid_screens, true ) ) {
			wp_enqueue_style( 'mwb-zndsk-admin-style', MWB_ZENDESK_DIR_URL . 'assets/zndsk-admin.css', false, MWB_ZENDESK_VERSION . time(), 'all' );
			wp_enqueue_script( 'mwb-zndsk-admin-script', MWB_ZENDESK_DIR_URL . 'assets/zndsk-admin.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable' ), MWB_ZENDESK_VERSION . time(), true );
			wp_localize_script(
				'mwb-zndsk-admin-script',
				'zndsk_ajax_object',
				array(
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'zndskSecurity'        => wp_create_nonce( 'zndsk_security' ),
					'zndskMailSuccess'     => __( 'Mail Sent Successfully.', 'zndskwoo' ),
					'zndskMailFailure'     => __( 'Mail not sent', 'zndskwoo' ),
					'zndskMailAlreadySent' => __( 'Mail already sent', 'zndskwoo' ),
				)
			);
		}
		wp_enqueue_script( 'mwb-zndsk-admin-global-script', MWB_ZENDESK_DIR_URL . 'assets/zndsk-admin-global.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), MWB_ZENDESK_VERSION . time(), true );
		wp_enqueue_style( 'mwb-zndsk-global-style', MWB_ZENDESK_DIR_URL . 'assets/zndsk-global.css', false, MWB_ZENDESK_VERSION . time(), 'all' );
		add_thickbox();
		// Deactivation screen.
		wp_enqueue_script( 'crm-connect-hubspot-sdk', '//js.hsforms.net/forms/shell.js', array(), time(), false );
	}
	add_action( 'admin_enqueue_scripts', 'mwb_zndsk_enqueue_script' );
	add_action( 'wp_enqueue_scripts', 'mwb_zndsk_ticket_script' );


	// Add settings link on plugin page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mwb_mzwos_plugin_settings_link' );

	/**
	 * Settings link.
	 *
	 * @since    1.0.0
	 * @param   Array $links    Settings link array.
	 */
	function mwb_mzwos_plugin_settings_link( $links ) {

		$my_link = array(
			'<a href="' . admin_url( 'admin.php?page=mwb-zendesk-order-sync' ) . '">' . __( 'Settings', 'mwb-zendesk-woo-order-sync' ) . '</a>',
		);
		return array_merge( $my_link, $links );
	}

	/**
	 * Add script function
	 *
	 * @return void
	 */
	function mwb_zndsk_ticket_script() {
		wp_enqueue_script( 'mwb-zndsk-ticket-script', MWB_ZENDESK_DIR_URL . 'assets/zndsk-ticket.js', false, MWB_ZENDESK_VERSION, 'all' );
		wp_enqueue_style( 'mwb-zndsk-public-style', MWB_ZENDESK_DIR_URL . 'assets/zndsk-public-ticket.css', false, MWB_ZENDESK_VERSION, 'all' );
		wp_localize_script(
			'mwb-zndsk-ticket-script',
			'zndsk_ajax_ticket_object',
			array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'zndskSecurity' => wp_create_nonce( 'zndsk_ticket_email' ),
			)
		);
		add_thickbox();
	}
	/**
	 * Show plugin development notice
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_admin_notice__success() {

		$suggest_sent    = get_option( 'zendesk_suggestions_sent', '' );
		$suggest_ignored = get_option( 'zendesk_suggestions_later', '' );
		?>
		<div class="notice notice-success mwb-zndsk-form-div" style="<?php echo ( '1' === $suggest_sent || '1' === $suggest_ignored ) ? 'display: none;' : 'display: block;'; ?>">
			<p><?php esc_html_e( 'Support the MWB Zendesk Woo Order Sync plugin development by sending us tracking data( we just want your Email Address and Name that too only once ).', 'zndskwoo' ); ?></p>
			<input type="button" class="button button-primary mwb-accept-button" name="mwb_accept_button" value="Accept">
			<input type="button" class="button mwb-reject-button" name="mwb_reject_button" value="Ignore">
		</div>
		<div style="display: none;" class="loading-style-bg" id="zndsk_loader">
			<img src="<?php echo esc_url( MWB_ZENDESK_DIR_URL . 'assets/images/loader.gif' ); ?>">
		</div>
		<?php
	}
} else {
	/**
	 * Error notice
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_plugin_error_notice() {
		?>
			<div class="error notice is-dismissible">
			<p><?php esc_html_e( 'WooCommerce is not activated, please activate WooCommerce first to install and use Order Sync with Zendesk for WooCommerce plugin.', 'zndskwoo' ); ?></p>
			</div>
			<style>
			#message{display:none;}
			</style>
			<?php
	}

	add_action( 'admin_init', 'mwb_zndsk_plugin_deactivate' );
	/**
	 * Deactivation hook
	 *
	 * @since    1.0.0
	 */
	function mwb_zndsk_plugin_deactivate() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'mwb_zndsk_plugin_error_notice' );
	}
}