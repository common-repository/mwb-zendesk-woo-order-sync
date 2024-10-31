<?php
/**
 * Exit if accessed directly
 *
 * @package mwb-zendesk-woo-order-sync/Library
 */

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This file manages to send order details to Zendesk.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    mwb-zendesk-woo-order-sync
 * @subpackage mwb-zendesk-woo-order-sync/Library
 */

if ( ! class_exists( 'MWB_ZENDESK_Settings' ) ) {
	/**
	 * This file manages to send order details to Zendesk.
	 *
	 * @link       https://wpswings.com/
	 * @since      1.0.0
	 *
	 * @package    mwb-zendesk-woo-order-sync
	 * @subpackage mwb-zendesk-woo-order-sync/Library
	 */
	class MWB_ZENDESK_Settings {

		/**
		 * Constructor of the class for fetching the endpoint.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->mwb_set_actions();
		}
		/**
		 * Adding actions for settings.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		public function mwb_set_actions() {

			add_action( 'admin_menu', array( $this, 'register_mwb_zndsk_menu_page' ) );
			add_action( 'add_meta_boxes', array( $this, 'mwb_zndsk_add_meta_boxes' ) );
			add_action( 'wp_ajax_mwb_zndsk_save_order_config_options', array( $this, 'save_order_config_options' ) );
		}
		/**
		 * Create/Register menu items for the plugin.
		 *
		 * @since 1.0
		 */
		public function register_mwb_zndsk_menu_page() {

			add_menu_page(
				esc_html__( 'Zendesk Order Sync', 'zndskwoo' ),
				esc_html__( 'Zendesk Order Sync', 'zndskwoo' ),
				'manage_options',
				'mwb-zendesk-order-sync',
				array( $this, 'mwb_zndsk_account_settings' ),
				'dashicons-tickets-alt',
				58
			);

			add_submenu_page( 'mwb-zendesk-order-sync', esc_html__( 'Account Settings', 'zndskwoo' ), esc_html__( 'Account Settings', 'zndskwoo' ), 'manage_options', 'mwb-zendesk-order-sync' );

			add_submenu_page( 'mwb-zendesk-order-sync', esc_html__( 'Order Configuration', 'zndskwoo' ), esc_html__( 'Order Configuration', 'zndskwoo' ), 'manage_options', 'mwb-zendesk-order-config', array( $this, 'mwb_zndsk_order_configuration_html' ) );
		}
		/**
		 * Admin settings.api_body
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		public function mwb_zndsk_account_settings() {

			$details = get_option( 'mwb_zndsk_account_details', array() );

			?>
			<div class="zndsk_setting_ticket_wrapper">
				<div class="zndsk_setting_wrapper">
					<h2><?php esc_html_e( 'Zendesk Account Settings', 'zndskwoo' ); ?></h2>
					<form action="" method="post">
						<table class="zndsk_setting_table">
							<tbody>
								<tr>
									<td class="zendesk-column zendesk-col-left  zendesk-url-column"><strong>Zendesk Url</strong></td>
									<td class="zendesk-column zendesk-col-right"><input type="text" class="setting_text" name="zndsk_setting_zendesk_url" value="<?php echo isset( $details['acc_url'] ) ? esc_html( $details['acc_url'] ) : ''; ?>"/></td>
									<td class="zendesk-err-message zendesk-column"><span>
									<?php
									if ( get_option( 'zendesk_url_error' ) ) {
										esc_html_e( 'Invalid URL', 'zndskwoo' ); }
									?>
									</span></td>
								</tr>
								<tr>
									<td class="zendesk-column zendesk-col-left zendesk-email-column"><strong>Zendesk Admin Email</strong></td>
									<td class="zendesk-column zendesk-col-right"><input type="text" class="setting_text" name="zndsk_setting_zendesk_user_email" value="<?php echo isset( $details['acc_email'] ) ? esc_html( $details['acc_email'] ) : ''; ?>"/></td>
									<td class="zendesk-err-message zendesk-column"><span>
									<?php
									if ( get_option( 'zendesk_email_error' ) ) {
										echo esc_html( __( 'Invalid Email', 'zndskwoo' ) ); }
									?>
									</span></td>
								</tr>
								<tr>
									<td class="zendesk-column zendesk-col-left zendesk-pass-column"><strong>Zendesk API Token</strong></td>
									<td class="zendesk-column zendesk-col-right"><input type="password" class="setting_text" name="zndsk_setting_zendesk_api_token" value="<?php echo isset( $details['acc_api_token'] ) ? esc_html( $details['acc_api_token'] ) : ''; ?>" placeholder=""/>
										<p><a target="_blank" href="https://support.zendesk.com/hc/en-us/articles/226022787-Generating-a-new-API-token-">Generating a new API token &rarr;</a></p>
									</td>
								</tr>
								<tr>
									<td class="zendesk-column zendesk-col-left zendesk-email-check-column"><strong>To activate email notifications when a customer raise a ticket, simply tick the checkbox.</strong></td>
									<td>
										<input type="checkbox" id="enable_mail_notification" name="enable_mail_notification" value="on" 
										<?php
										if ( isset( $details ) && isset( $details['mail_check'] ) ) {

											if ( 'on' === $details['mail_check'] ) {
												checked( $details['mail_check'], 'on' );
											}
										}
										?>
										>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="zendesk-submit">
										<input type="submit" class="button button-primary" name="zndsk_setting_save_btn" value="Submit">
									</td>
								</tr>
								<tr>
									<td >
										<p class="sucessful-msg"> 
										<?php
										if ( ! get_option( 'zendesk_email_error' ) && ! get_option( 'zendesk_url_error' ) ) {
											esc_html_e( 'Your Credential is successfully saved !', 'zndskwoo' );
										}
										?>
										  </p>
									</td>
								</tr>
							</tbody>
						</table>
						<?php wp_nonce_field( 'zndsk_submit', 'zndsk_secure_check' ); ?>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Order Configuration content.
		 *
		 * @since    2.0.2
		 */
		public function mwb_zndsk_order_configuration_html() {

			include_once MWB_ZENDESK_DIR_PATH . 'admin-templates/order-configuration.php';
		}

		/**
		 * Adding meta box for showing zendesk tickets.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		public function mwb_zndsk_add_meta_boxes() {

			$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
			add_meta_box( 'mwb_zendesk_tickets', __( 'Zendesk Tickets', 'woocommerce' ), array( $this, 'mwb_zndsk_tickets_config' ), $screen, 'side', 'core' );
		}
		/**
		 * Zendesk tickets layout.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		public function mwb_zndsk_tickets_config() {

			$tickets = MWB_ZENDESK_Manager::mwb_fetch_useremail();

			if ( ! empty( $tickets ) && 'empty_zndsk_account_details' === $tickets ) {

				?>
					<div style="display:block;">
						<span><?php echo esc_html_e( 'Please Setup Zendesk Account details.', 'zndskwoo' ); ?></span>
					</div>
				<?php

				return;
			}

			if ( ! empty( $tickets ) && 'zndsk_api_error' === $tickets ) {

				?>
					<div style="display:block;">
						<span><?php echo esc_html_e( 'Zendesk API Error. Please enter correct details or contact WPSwings support.', 'zndskwoo' ); ?></span>
					</div>
				<?php

				return;
			}

			if ( ! empty( $tickets ) && is_array( $tickets ) ) {

				foreach ( $tickets as $single_data ) {
					if ( ! empty( $single_data ) && is_array( $single_data ) ) {
						?>
					<div class="zndsk-ticket-content" style="display:block;">
						<button class="data zndsk_accordion">Ticket#<?php echo esc_attr( $single_data['id'] ); ?></button>
						<div class="zndsk_panel">
							<label class="head zendesk-status zendesk-status-left">Status:</label>
							<div class="zendesk-status-right zendesk-status">
								<span class="" data-status="<?php echo esc_attr( $single_data['status'] ); ?>"><?php echo esc_attr( $single_data['status'] ); ?>
								</span>
							</div>	
							<label class="head zendesk-sub zendesk-status-left">Subject:</label>
							<div class="zendesk-status-right zendesk-sub">
								<span class=""><?php echo esc_attr( $single_data['subject'] ); ?>
								</span>
							</div>

							<label class="head zendesk-description zendesk-status-left">Description:</label>
							<div class="zendesk-status-right zendesk-description">
								<span class=""><?php echo esc_attr( $single_data['description'] ); ?>
								</span>
							</div>
						</div>
					</div>
						<?php
					}
				}
			} else {
				?>
					<div class="zndsk-no-ticket" style="display:block;">
						<span><?php echo esc_html_e( 'No tickets found', 'zndskwoo' ); ?></span>
					</div>
				<?php
			}
		}

		/**
		 * Save Zendesk Order Configuration options.
		 *
		 * @since    2.0.2
		 */
		public function save_order_config_options() {

			check_ajax_referer( 'zndsk_security', 'zndskSecurity' );

			$order_config_options = array();

			$order_config_options['latest_orders_count'] = ! empty( $_POST['latest_orders_count'] ) ? sanitize_text_field( wp_unslash( $_POST['latest_orders_count'] ) ) : '';

			$order_config_options['source_kpi_fields']   = ! empty( $_POST['source_kpi_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['source_kpi_fields'] ) ) : array();
			$order_config_options['selected_kpi_fields'] = ! empty( $_POST['selected_kpi_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['selected_kpi_fields'] ) ) : array();

			$order_config_options['source_order_fields']   = ! empty( $_POST['source_order_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['source_order_fields'] ) ) : array();
			$order_config_options['selected_order_fields'] = ! empty( $_POST['selected_order_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['selected_order_fields'] ) ) : array();

			$enable_create_ticket_on_order_status = ! empty( $_POST['enable_create_ticket_on_order_status'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_create_ticket_on_order_status'] ) ) : '';
			$order_status_for_ticket = ! empty( $_POST['order_status_for_ticket'] ) ? sanitize_text_field( wp_unslash( $_POST['order_status_for_ticket'] ) ) : '';

			$mwb_create_subject_automatic = ! empty( $_POST['mwb_create_subject_automatic'] ) ? sanitize_text_field( wp_unslash( $_POST['mwb_create_subject_automatic'] ) ) : '';
			$mwb_create_comment_automatic = ! empty( $_POST['mwb_create_comment_automatic'] ) ? sanitize_text_field( wp_unslash( $_POST['mwb_create_comment_automatic'] ) ) : '';
			$mwb_create_tag_automatic = ! empty( $_POST['mwb_create_tag_automatic'] ) ? sanitize_text_field( wp_unslash( $_POST['mwb_create_tag_automatic'] ) ) : '';

			$selected_options_saved = false;
			$value_update = update_option( 'mwb_zndsk_order_config_options', $order_config_options );
			if ( $value_update ) {
				$selected_options_saved = true;
			}

			$value_update = update_option( 'enable_create_ticket_on_order_status', $enable_create_ticket_on_order_status );
			if ( $value_update ) {
				$selected_options_saved = true;
			}
			$value_update = update_option( 'order_status_for_ticket', $order_status_for_ticket );
			if ( $value_update ) {
				$selected_options_saved = true;
			}

			$value_update = update_option( 'mwb_create_subject_automatic', $mwb_create_subject_automatic );
			if ( $value_update ) {
				$selected_options_saved = true;
			}
			$value_update = update_option( 'mwb_create_comment_automatic', $mwb_create_comment_automatic );
			if ( $value_update ) {
				$selected_options_saved = true;
			}
			$value_update = update_option( 'mwb_create_tag_automatic', $mwb_create_tag_automatic );
			if ( $value_update ) {
				$selected_options_saved = true;
			}

			echo wp_json_encode( $selected_options_saved );

			wp_die();
		}
	}
}
$init = new MWB_ZENDESK_Settings();
