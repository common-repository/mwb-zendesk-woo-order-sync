<?php
/**
 * Exit if accessed directly
 *
 * @package mwb-zendesk-woo-order-sync/Library
 */

use Automattic\WooCommerce\Utilities\OrderUtil;
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

if ( ! class_exists( 'MWB_ZENDESK_Manager' ) ) {
	/**
	 * This file manages to send order details to Zendesk.
	 *
	 * @link       https://wpswings.com/
	 * @since      1.0.0
	 *
	 * @package    mwb-zendesk-woo-order-sync
	 * @subpackage mwb-zendesk-woo-order-sync/Library
	 */
	class MWB_ZENDESK_Manager {
		/**
		 * Initialize the class and set its object.
		 *
		 * @since    1.0.0
		 * @var $instance
		 */
		private static $instance;
		/**
		 * Initialize the class and set its object.
		 *
		 * @since    1.0.0
		 */
		public static function get_instance() {

			self::$instance = new self();
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
		/**
		 * Constructor of the class for fetching the endpoint.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->mwb_zndsk_set_locale();
			$this->mwb_load_dependecy();
			add_action( 'init', array( $this, 'mwb_add_endpoint' ) );
			add_action( 'admin_init', array( $this, 'mwb_zndsk_save_account_details' ) );
			$details = get_option( 'mwb_zndsk_account_details', array() );
			if ( ! empty( $details ) && is_array( $details ) ) {
				add_action( 'woocommerce_account_ticket-history_endpoint', array( $this, 'mwb_my_account_endpoint_content_main' ) );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'mwb_log_history_link' ), 40 );
				add_filter( 'manage_users_columns', array( $this, 'add_ticket_to_user_table' ) );
				add_action( 'manage_users_custom_column', array( $this, 'add_ticket_to_user_table_content' ), 10, 3 );

				add_action( 'woocommerce_order_status_changed', array( $this, 'mwb_create_automatic_zendesk_ticket_order_status' ), 10, 3 );
			}
			add_action( 'init', array( $this, 'mwb_public_function_call' ) );
			add_action( 'init', array( $this, 'mwb_public_ticket_update_function_call' ) );
			// Add the Zendesk Email.
			add_filter( 'woocommerce_email_classes', array( $this, 'mwb_email_add_file_for_zendesk' ), 10, 1 );
		}


		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function mwb_zndsk_set_locale() {

			$this->mwb_zndsk_load_plugin_textdomain();
		}
		/**
		 * Load file dependency.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function mwb_load_dependecy() {
			require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-settings.php';
			require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-global-functions.php';
		}
		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function mwb_zndsk_load_plugin_textdomain() {

			$var = load_plugin_textdomain(
				'zndskwoo',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);
		}
		/**
		 * Ticket fetch function function
		 *
		 * @since 2.0.2
		 * @param array $columns .
		 */
		public function add_ticket_to_user_table( $columns ) {
			$columns['user_id'] = 'Tickets';
			return $columns;
		}
		/**
		 * Create_user_ticket function
		 *
		 * @since 2.0.2
		 * @return void
		 */
		public function mwb_public_ticket_update_function_call() {
			require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-global-functions.php';
			update_user_ticket();
		}
		/**
		 * Create_user_ticket function
		 *
		 * @since 2.0.2
		 * @return void
		 */
		public function mwb_public_function_call() {
			require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-global-functions.php';
			create_user_ticket();
		}
		/**
		 * Ticket fetch function function
		 *
		 * @since 2.0.2
		 * @param array $value .
		 * @param array $column_name .
		 * @param array $user_id .
		 */
		public function add_ticket_to_user_table_content( $value, $column_name, $user_id ) {
			$user = get_userdata( $user_id );
			if ( 'user_id' === $column_name ) {
				$author_obj = get_user_by( 'id', $user_id );
				$email      = $author_obj->data->user_email;
				return '<a href="admin-ajax.php?action=mwb_zndsk_ticket&id=' . $email . '&nonce=' . wp_create_nonce( 'zndsk_ticket' ) . '" title="User Ticket History" class="thickbox">Show Tickets</a>';
			}
			return $value;
		}
		/**
		 * Create a setting tab function
		 *
		 * @since 2.0.2
		 * @param array $menu_links fggh.
		 */
		public function mwb_log_history_link( $menu_links ) {
			$menu_links = array_slice( $menu_links, 0, 5, true ) + array( 'ticket-history' => 'Ticket-History' ) + array_slice( $menu_links, 5, null, true );
			return $menu_links;
		}
		/**
		 * Create endpoint function
		 *
		 * @since 2.0.2
		 * @return void
		 */
		public function mwb_add_endpoint() {
			add_rewrite_endpoint( 'ticket-history', EP_PAGES );

			add_shortcode( 'mwb-ticket-history', array( $this, 'wps_mzwos_show_ticket_history' ) );
			flush_rewrite_rules();
		}


		/**
		 * Show the ticket history through shortcode.
		 */
		public function wps_mzwos_show_ticket_history() {
			ob_start();
			if ( ! is_user_logged_in() ) {
				echo '<div class="woocommerce">';
				wc_get_template( 'myaccount/form-login.php' );
				echo '</div>';
			} else {

				include_once MWB_ZENDESK_DIR . '/Library/mwb-zendesk-woo-ticket-shortcode.php';
			}
			return ob_get_clean();
		}
		/**
		 * Endpoint content function
		 *
		 * @since 2.0.2
		 * @return void
		 */
		public function mwb_my_account_endpoint_content_main() {

			$user_data          = wp_get_current_user();
			$user_mail          = $user_data->data->user_email;
			$email = '';
			$select_array_email = array();
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// HPOS Enabled.
				$customer_orders    = wc_get_orders(
					array(
						'limit' => -1,
						'customer_user' => get_current_user_id(),
						'type'   => wc_get_order_types(),
						'status' => array_keys( wc_get_order_statuses() ),
					)
				);
			} else {
				$customer_orders    = get_posts(
					array(
						'numberposts' => -1,
						'meta_key'    => '_customer_user', //phpcs:ignore
					'meta_value'  => get_current_user_id(), //phpcs:ignore
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
					)
				);

			}

			if ( ! empty( $customer_orders ) && is_array( $customer_orders ) ) {
				foreach ( $customer_orders as $key => $value ) {
					$order      = wc_get_order( $value->ID );
					$order_data = $order->get_data();
					$user_email = $order_data['billing']['email'];
					if ( ! in_array( $user_email, $select_array_email, true ) ) {
						array_push( $select_array_email, $order_data['billing']['email'] );
					}
					$user_data = wp_get_current_user();
					$email = '';
					if ( empty( $email ) ) {
						$email = $user_data->data->user_email;
					}
				}
				?>
				<div id="select_box_email" class="mwb-zendesk-select-opt">
					<label for="email"> <?php esc_html_e( 'Choose your Billing Email', 'zndskwoo' ); ?></label>
					<select id="mwb-zendsk-email">
					<?php foreach ( $select_array_email as $key => $value ) { ?>
						<option <?php echo ( $email == $value ) ? 'selected' : ''; ?>  value="<?php echo esc_html( $value ); ?>"><?php echo esc_html( $value ); ?></option> 
						<?php
						$email = $value;
					}
					?>
					</select>
				</div>
				<?php
			}

			$object = new MWB_ZENDESK_Manager();

			$object->mwb_my_account_endpoint_content( $email );
		}
		/**
		 * Endpoint content function
		 *
		 * @since 2.0.2
		 * @param array $email .
		 * @return void
		 */
		public function mwb_my_account_endpoint_content( $email = '' ) {
			$user_data = wp_get_current_user();
			if ( empty( $email ) ) {
				$email = $user_data->data->user_email;
			}
			$tickets = self::mwb_fetch_useremail( $email );
			self::table_for_tickets( $tickets );
		}
		/**
		 * Register routes for the order details class.
		 *
		 * @since    1.0.0
		 */
		public function mwb_zndsk_register_routes() {

			register_rest_route(
				'zndskwoo',
				'/order_details',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'mwb_zndsk_woo_order_sync' ),
						'permission_callback' => 'mwb_zndsk_get_items_permissions_check',
					),
				)
			);
		}
		/**
		 * Send zendesk client's details
		 *
		 * @since    1.0.0
		 */
		public function send_clients_details() {

			$status          = false;
			$suggestion_sent = get_option( 'zendesk_suggestions_sent', false );
			if ( $suggestion_sent ) {
				$status = 'already-sent';
				return $status;
			}
			$email     = get_option( 'admin_email', '' );
			$admin     = get_user_by( 'email', $email );
			$admin_id  = $admin->ID;
			$firstname = get_user_meta( $admin_id, 'first_name', true );
			$lastname  = get_user_meta( $admin_id, 'last_name', true );
			$site_url  = ! empty( $admin->user_url ) ? $admin->user_url : '';
			$to        = sanitize_email( 'integrations@wpswings.com' );
			$subject   = "Zendesk Woo Customer's Details";
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$message   = 'First Name:- ' . $firstname . '<br/>';
			$message  .= 'Last Name:- ' . $lastname . '<br/>';
			$message  .= 'Admin Email:- ' . $email . '<br/>';
			$message  .= 'Site Url:- ' . $site_url . '<br/>';
			$status    = wp_mail( $to, $subject, $message, $headers );
			return $status;
		}
		/**
		 * Sends response to zendesk.
		 *
		 * @since    1.0.0
		 * @param array $request   requested parameters from zendesk.
		 * @return array  $data      order details
		 */
		public function mwb_zndsk_woo_order_sync( $request ) {

			$post_params = $request->get_params();

			$response = array();

			$customer_email = ! empty( $post_params['email'] ) ? sanitize_text_field( wp_unslash( $post_params['email'] ) ) : '';

			if ( ! empty( $customer_email ) ) {

				$handled_order_config_options = mwb_zndskwoo_get_order_config_options();

				$zendesk_order_config_data = array();

				$kpi_fields   = mwb_zndskwoo_get_customer_kpi_fields_for_zendesk( $customer_email, $handled_order_config_options );
				$order_fields = mwb_zndskwoo_get_customer_order_fields_for_zendesk( $customer_email, $handled_order_config_options );

				$zendesk_order_config_data['kpi_fields']   = ! empty( $kpi_fields ) ? $kpi_fields : esc_html__( 'No KPI data found', 'zndskwoo' );
				$zendesk_order_config_data['order_fields'] = ! empty( $order_fields ) ? $order_fields : esc_html__( 'No Order data found', 'zndskwoo' );

				$data = wp_json_encode( $zendesk_order_config_data );

				return $data;
			}
		}
		/**
		 * Saving zendesk account details.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		public function mwb_zndsk_save_account_details() {

			if ( ! empty( $_REQUEST['zndsk_secure_check'] ) ) {

				$sanitized_nonce = sanitize_text_field( wp_unslash( $_REQUEST['zndsk_secure_check'] ) );

				$nonce_value = wc_get_var( $sanitized_nonce );
			} else {
				$nonce_value = '';
			}

			if ( isset( $nonce_value ) && wp_verify_nonce( $nonce_value, 'zndsk_submit' ) ) {

				if ( isset( $_POST['zndsk_setting_save_btn'] ) ) { // Input var okay.

					if ( isset( $_POST['zndsk_setting_zendesk_user_email'] ) ) {
						$email = sanitize_text_field( wp_unslash( $_POST['zndsk_setting_zendesk_user_email'] ) );// Input var okay.
					}
					if ( isset( $_POST['zndsk_setting_zendesk_url'] ) ) {
						$website = sanitize_text_field( wp_unslash( $_POST['zndsk_setting_zendesk_url'] ) );// Input var okay.
					}
					if ( isset( $_POST['enable_mail_notification'] ) ) {
						$mail_check = 'on';
					} else {
						$mail_check = 'off';
					}

					$api_token = ! empty( $_POST['zndsk_setting_zendesk_api_token'] ) ? sanitize_text_field( wp_unslash( $_POST['zndsk_setting_zendesk_api_token'] ) ) : ''; // Input var okay.

					$emailerror   = '';
					$websiteerror = '';

					if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						$emailerror = true;
					}

					if ( ! filter_var( $website, FILTER_VALIDATE_URL ) ) {
						$websiteerror = true;
					}

					update_option( 'zendesk_email_error', $emailerror );
					update_option( 'zendesk_url_error', $websiteerror );

					$zendesk_acc_details = array(
						'acc_url'       => $website,
						'acc_email'     => $email,
						'acc_api_token' => $api_token,
						'mail_check'    => $mail_check,
					);

					if ( true === $emailerror || true === $websiteerror ) {

						delete_option( 'mwb_zndsk_account_details' );
					} else {
						update_option( 'mwb_zndsk_account_details', $zendesk_acc_details );
					}
				}
			}
		}

		/**
		 * Getting email of contact from zendesk.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param array $email .
		 */
		public static function mwb_fetch_useremail( $email = '' ) {
			global $post;

			$zndsk_acc_details = get_option( 'mwb_zndsk_account_details' );

			$response = array();
			if ( empty( $zndsk_acc_details['acc_email'] ) || empty( $zndsk_acc_details['acc_url'] ) ) {

				return 'empty_zndsk_account_details';
			}

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// HPOS Enabled.
				$order = wc_get_order( get_the_ID() );
			} elseif ( is_array( $post ) || is_object( $post ) ) {

					$order = wc_get_order( $post->ID );
			}
			if ( empty( $email ) ) {
				$email = $order->get_billing_email();
			}
			$url = $zndsk_acc_details['acc_url'] . "/api/v2/users/search.json?query='" . $email . " ' ";

			$basic = '';

			if ( ! empty( $zndsk_acc_details['acc_api_token'] ) ) {

				$basic = base64_encode( $zndsk_acc_details['acc_email'] . '/token:' . $zndsk_acc_details['acc_api_token'] );
			} elseif ( ! empty( $zndsk_acc_details['acc_pass'] ) ) {

				$basic = base64_encode( $zndsk_acc_details['acc_email'] . ':' . $zndsk_acc_details['acc_pass'] );
			} else {

				return 'empty_zndsk_account_details';
			}

			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . $basic,
			);

			$response = wp_remote_get(
				$url,
				array(
					'headers'   => $headers,
					'sslverify' => false,
				)
			);

			if ( is_wp_error( $response ) ) {

				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {

				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
			}

			if ( 200 === $status_code || '200' === $status_code ) {

				$api_body = wp_remote_retrieve_body( $response );
				$api_body = json_decode( $api_body );
				if ( $api_body ) {

					$users = $api_body->users;
					foreach ( $users as $key => $value ) {
						if ( $value->email === $email ) {
							$response = self::mwb_fetch_user_tickets( $value->id );
							return $response;
						} else {
							$users = $api_body->users;
							return $users;
						}
					}
				}
			} else {
				return 'zndsk_api_error';
			}
		}
		/**
		 * Getting user tickets from zendesk.
		 *
		 * @since    1.0.0
		 * @param array $user_id   user id of contact.
		 * @return array  $data      order details
		 */
		public static function mwb_fetch_user_tickets( $user_id ) {
			$zndsk_acc_details = get_option( 'mwb_zndsk_account_details' );

			$url = $zndsk_acc_details['acc_url'] . '/api/v2/users/' . $user_id . '/tickets/requested.json';

			$basic = '';

			if ( ! empty( $zndsk_acc_details['acc_api_token'] ) ) {

				$basic = base64_encode( $zndsk_acc_details['acc_email'] . '/token:' . $zndsk_acc_details['acc_api_token'] );
			} elseif ( ! empty( $zndsk_acc_details['acc_pass'] ) ) {

				$basic = base64_encode( $zndsk_acc_details['acc_email'] . ':' . $zndsk_acc_details['acc_pass'] );
			}

			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . $basic,
			);

			$response = wp_remote_get(
				$url,
				array(
					'headers'   => $headers,
					'sslverify' => false,
				)
			);
			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
			}
			if ( 200 === $status_code || '200' === $status_code ) {

				$api_body = wp_remote_retrieve_body( $response );

				if ( $api_body ) {
					$api_body = json_decode( $api_body );
				}
			}
			$tickets       = $api_body->tickets;
			$ticket_fields = array();
			foreach ( $tickets as $key => $single_ticket ) {
				update_option( get_current_user_id(), $single_ticket->requester_id );
				$ticket_fields[] = array(
					'id'          => $single_ticket->id,
					'subject'     => $single_ticket->subject,
					'description' => $single_ticket->description,
					'status'      => $single_ticket->status,
					'url'         => $single_ticket->url,
				);
			}

			return $ticket_fields;
		}
		/**
		 * Table for ticket function
		 *
		 * @since 2.0.2
		 * @param array $tickets .
		 * @return void
		 */
		public static function table_for_tickets( $tickets ) {
			include_once MWB_ZENDESK_DIR_PATH . 'admin-templates/ticket.php';
		}

		/**
		 * Function to register email class for email notification.
		 *
		 * @param array $email_classes as email class.
		 * @return array
		 */
		public function mwb_email_add_file_for_zendesk( $email_classes ) {

			require_once MWB_ZENDESK_DIR_PATH . 'emails/class-mwb-zendesk-email-for-ticket.php';

			// add the email class to the list of email classes that WooCommerce loads.

			$email_classes['mwb_zendesk_email_for_ticket']        = new Mwb_zendesk_email_for_ticket();
			return $email_classes;
		}

		/**
		 * Function to Create Zendesk Ticket on order status.
		 *
		 * @param int    $order_id order id.
		 * @param string $old_status order old status.
		 * @param string $new_status order new status.
		 * @return void
		 */
		public function mwb_create_automatic_zendesk_ticket_order_status( $order_id, $old_status, $new_status ) {
			$enable_create_ticket_on_order_status = get_option( 'enable_create_ticket_on_order_status' );
			$order_status_for_ticket = get_option( 'order_status_for_ticket' );

			if ( 1 == $enable_create_ticket_on_order_status ) {

				$order_status_for_ticket = str_replace( 'wc-', '', $order_status_for_ticket );
				;

				if ( $order_status_for_ticket == $new_status ) {

					$order          = wc_get_order( $order_id );
					if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
						// HPOS usage is enabled.
						$mwb_automatic_ticket_created = $order->get_meta( 'mwb_automatic_ticket_created', true );
					} else {
						$mwb_automatic_ticket_created = get_post_meta( $order_id, 'mwb_automatic_ticket_created', true );
					}
					$subject     = get_option( 'mwb_create_subject_automatic' );
					$comment     = get_option( 'mwb_create_comment_automatic' );
					$tags        = get_option( 'mwb_create_tag_automatic' );

					if ( 'done' != $mwb_automatic_ticket_created && ! empty( $subject ) && ! empty( $comment ) && ! empty( $tags ) ) {

						$userid         = $order->get_user_id();

						$user               = get_user_by( 'id', $userid );
						$billing_first_name = $order->get_billing_first_name();
						$billing_last_name  = $order->get_billing_last_name();

						$priority    = 'Low';

						$organisation = '';
						$email        = $order->get_billing_email();
						$phone        = $order->get_billing_phone();

						require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-global-functions.php';
						create_user_on_ticket_submisison( $email, $phone, $organisation );

						$ticket = array(
							'ticket' => array(
								'subject'   => $subject,
								'comment'   => array(
									'body' => $comment,
								),
								'requester' => array(
									'name'     => 'user',
									'email'    => $email,
									'priority' => $priority,
								),
								'tags'      => $tags,
							),
						);

						$ticket = wp_json_encode( $ticket );
						$zndsk_acc_details = get_option( 'mwb_zndsk_account_details' );
						$basic = base64_encode( $zndsk_acc_details['acc_email'] . '/token:' . $zndsk_acc_details['acc_api_token'] );
						$url  = $zndsk_acc_details['acc_url'] . '/api/v2/tickets.json';
						$data = wp_remote_post(
							$url,
							array(
								'headers' => array(
									'Content-Type'  => 'application/json',
									'Authorization' => 'Basic ' . $basic,
								),
								'body'    => $ticket,
								'method'  => 'POST',
							)
						);

						if ( is_wp_error( $data ) ) {

								$status_code = $data->get_error_code();
								$res_message = $data->get_error_message();

						} else {

							$status_code = wp_remote_retrieve_response_code( $data );
							$res_message = wp_remote_retrieve_response_message( $data );
							$res_body    = wp_remote_retrieve_body( $data );
							$res_data = json_decode( $res_body, true );
							$ticket_id = $res_data['ticket']['id'];
							$order->add_order_note( esc_html( 'Automatic Zendesk Ticket Created with ticket number - ', 'zndskwoo' ) . esc_html( $ticket_id ) );
							if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
								// HPOS usage is enabled.
								$order->update_meta_data( 'mwb_automatic_ticket_created', 'done' );
								$order->save();
							} else {
								update_post_meta( $order_id, 'mwb_automatic_ticket_created', 'done' );
							}
						}
					}
				}
			}
		}
	}
}
