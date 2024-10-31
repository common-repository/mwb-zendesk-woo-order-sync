<?php
/**
 * Exit if accessed directly
 *
 * @package mwb-zendesk-woo-order-sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The api-specific functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 * @package    mwb-zendesk-woo-order-sync
 */

require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-manager.php';
/**
 * The api-specific functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 * @package    mwb-zendesk-woo-order-sync
 * @author     WPSwings <webmaster@wpswings.com>
 */
class MWB_ZENDESK_Connect_Api {
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

	/**
	 * Initialize the class and set its object.
	 *
	 * @since 1.0.0
	 * @var $instance
	 */
	private $mwb_zendeskconnect_manager;

	/**
	 * Function to get instance
	 *
	 * @return array
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

		$this->mwb_zendeskconnect_manager = MWB_ZENDESK_Manager::get_instance();
		add_action( 'wp_ajax_mwb_zndsk_suggest_accept', array( $this, 'mwb_zndsk_suggest_accept' ) );
		add_action( 'wp_ajax_mwb_zndsk_suggest_later', array( $this, 'mwb_zndsk_suggest_later' ) );
		add_action( 'wp_ajax_mwb_zndsk_ticket', array( $this, 'mwb_zndsk_ticket' ) );
		add_action( 'wp_ajax_mwb_zndsk_tickt_email', array( $this, 'mwb_zndsk_tickt_email' ) );
		add_action( 'wp_ajax_mwb_zndsk_chat_history', array( $this, 'mwb_zndsk_chat_history_callback' ) );
	}
	/**
	 * Registering routes.
	 *
	 * @since    1.0.0
	 */
	public function mwb_zndsk_register_routes() {

		$this->mwb_zendeskconnect_manager->mwb_zndsk_register_routes();
	}
	/**
	 * Save suggestion in DB
	 *
	 * @since    1.0.0
	 */
	public function mwb_zndsk_suggest_later() {
		check_ajax_referer( 'zndsk_security', 'zndskSecurity' );
		update_option( 'zendesk_suggestions_later', true );
		return true;
	}

	/**
	 * Function for fetch ticket in zendesk
	 *
	 * @since    1.0.0
	 */
	public function mwb_zndsk_ticket() {
		$url   = get_site_url();
		$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		$check = wp_verify_nonce( $nonce, 'zndsk_ticket' );
		if ( $check ) {
			if ( isset( $_GET['id'] ) ) {
				$all_user_ticket_id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
				$ticket             = MWB_ZENDESK_Manager::mwb_fetch_useremail( $all_user_ticket_id );
				$ticket             = wp_json_encode( $ticket );
				require_once MWB_ZENDESK_DIR_PATH . 'admin-templates/zndsk-all-ticket.php';
			}
		}
	}
	/**
	 * Show all email ticket functiomn function
	 *
	 * @return void
	 */
	public function mwb_zndsk_tickt_email() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$check = wp_verify_nonce( $nonce, 'zndsk_ticket_email' );
		if ( $check ) {
			$all_user_ticket_email = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
			require_once MWB_ZENDESK_DIR . '/Library/class-mwb-zendesk-manager.php';
			$object = new MWB_ZENDESK_Manager();
			$object->mwb_my_account_endpoint_content( $all_user_ticket_email );
			wp_die();
		}
	}
	/**
	 * Check status of mail sent and save suggestion in DB
	 *
	 * @since    1.0.0
	 */
	public function mwb_zndsk_suggest_accept() {
		check_ajax_referer( 'zndsk_security', 'zndskSecurity' );
		$status = $this->mwb_zendeskconnect_manager->send_clients_details();
		if ( $status && 'already-sent' !== $status ) {
			update_option( 'zendesk_suggestions_sent', true );
			echo wp_json_encode( 'success' );
		} elseif ( 'already-sent' === $status ) {
			echo wp_json_encode( 'alreadySent' );
		} else {
			update_option( 'zendesk_suggestions_later', true );
			echo wp_json_encode( 'failure' );
		}
		wp_die();
	}

	/**
	 * Function to fetch chat history using ajax function.
	 *
	 * @return void
	 */
	public function mwb_zndsk_chat_history_callback() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$check = wp_verify_nonce( $nonce, 'zndsk_ticket_email' );
		if ( $check ) {

			$ticketid = isset( $_POST['ticketid'] ) ? sanitize_text_field( wp_unslash( $_POST['ticketid'] ) ) : '';

			$zndsk_acc_details = get_option( 'mwb_zndsk_account_details' );

			$zendeskemail = $zndsk_acc_details['acc_email'];
			$zendeskapitoken = $zndsk_acc_details['acc_api_token'];

			$url = $zndsk_acc_details['acc_url'] . '/api/v2/tickets/' . $ticketid . '/comments.json';

			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_USERPWD, "$zendeskemail/token:$zendeskapitoken" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			$response = curl_exec( $ch );
			curl_close( $ch );

			$html = '';

			$messages = json_decode( $response, true )['comments'];
			foreach ( $messages as $message ) {
				$properties_array[] = array( $message['author_id'], $message['body'], $message['created_at'] );

			}
			$author_id = '';
			$body = '';

			$html .= '<div class="wps-osz-chat-wrapper"><div class="content-inner">';

			$first_author_id = $properties_array[0][0];
			foreach ( $properties_array as $message ) {

				$author_id = $message[0];

				$body = $message[1];

				$create_at = $message[2];
				$datetime = new DateTime( $create_at );
				$time = $datetime->format( 'h:m' );
				$date = $datetime->format( 'D M y' );

				if ( $author_id === $first_author_id ) {

					$html .= '<div class="media flex-row-reverse">';
					$html .= '<div class="img-user online"><img src="' . MWB_ZENDESK_DIR_URL . 'assets/images/user.svg" alt=""></div>';
					$html .= '<div class="media-body"><div class="msg-wrapper">' . $body . ' <sub>' . $time . '</sub></div></div>';
					$html .= '</div><div class="time"><span>' . $date . '</span></div>';
				} else {

					$html .= '<div class="media">';
					$html .= '<div class="img-user online"><img src="' . MWB_ZENDESK_DIR_URL . 'assets/images/customer-support.svg" alt=""></div>';
					$html .= '<div class="media-body"><div class="msg-wrapper">' . $body . ' <sub>' . $time . '</sub></div></div>';
					$html .= '</div><div class="time"><span>' . $date . '</span></div>';
				}
			}
			$html .= '</div></div>';
			echo wp_kses_post( $html );
			wp_die();

		}
	}
}
