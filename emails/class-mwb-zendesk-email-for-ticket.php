<?php
/**
 * Exit if accessed directly
 *
 * @package  woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.

}
/**
 * A custom Expedited Order WooCommerce Email class
 *
 * @since 0.1
 * @extends \WC_Email
 */
class Mwb_Zendesk_Email_For_Ticket extends WC_Email {
	/**
	 * $order as order id.
	 *
	 * @var string
	 */
	public $order_id = '';
	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {
		// set ID, this simply needs to be a unique name.
		$this->id = 'wps_zendesk_email_for_ticket';

		// this is the title in WooCommerce Email settings.
		$this->title = 'Zendesk Email For Ticket';

		// this is the description in WooCommerce email settings.
		$this->description = 'Admin to customer Ticket Notification emails and viceversa';

		// these are the default heading and subject lines that can be overridden using the settings.
		$this->heading = 'Zendesk Ticket';
		$this->subject = 'New ticket has been received';

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
		$this->template_html  = 'mwb-zendesk-ticket-email-template.php';
		$this->template_plain = 'plain/mwb-zendesk-ticket-email-template.php';
		$this->template_base  = MWB_ZENDESK_DIR_PATH . 'emails/templates/';
		$this->placeholders   = array(
			'{site_title}'   => $this->get_blogname(),
			'{message_date}' => '',

		);
		$this->order_id       = '';
		// Call parent constructor to load any other defaults not explicity defined here.
		parent::__construct();
	}
	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @param string $msg is message.
	 * @param array  $attachment is media attachment.
	 * @param string $to send to mail.
	 */
	public function trigger( $msg, $attachment, $to ) {
		if ( $to ) {
			$this->setup_locale();
			$this->receicer                       = $to;
			$this->msg                            = $msg;
			$this->order_id                       = $order_id;
			$this->placeholders['{message_date}'] = date_i18n( wc_date_format() );
			$this->send( $this->receicer, $this->get_subject(), $this->get_content(), $this->get_headers(), $attachment );
		}
		$this->restore_locale();
	}

	/**
	 * Get_content_html function.
	 *
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'msg'                => $this->msg,
				'order_id'           => $this->order_id,
				'email_heading'      => $this->get_heading(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
				'additional_content' => $this->get_additional_content(),
			),
			'',
			$this->template_base
		);

		return ob_get_clean();
	}

	/**
	 * Get email subject.
	 */
	public function get_default_subject() {
		return esc_html__( 'On Your {site_title} Zendesk Ticket is Raise message from {message_date}', 'zndskwoo' );
	}

	/**
	 * Get email heading.
	 */
	public function get_default_heading() {
		return esc_html__( 'Zendesk Ticket Details', 'zndskwoo' );
	}

	/**
	 * Get_content_plain function.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'msg'            => $this->msg,
				'order_id'       => $this->order_id,
				'email_heading'  => $this->get_heading(),
				'sent_to_admin'  => false,
				'plain_text'     => true,
				'email'          => $this,
				'additional_content' => $this->get_additional_content(),
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}
}
