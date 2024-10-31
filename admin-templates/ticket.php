<?php
/**
 * This file includes Zendesk Order configuration settings.
 *
 * @link       https://wpswings.com/
 * @since      2.0.2
 *
 * @package    mwb-zendesk-woo-order-sync
 * @subpackage mwb-zendesk-woo-order-sync/admin-templates
 */

use Automattic\WooCommerce\Utilities\OrderUtil;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $tickets ) ) {
	?>
	<img src="<?php echo esc_html( MWB_ZENDESK_DIR_URL . 'assets/images/loader.gif' ); ?>" class="mwb-loader-zndsk" style="display:none">
	<div class="woocommerce-info"><?php esc_html_e( 'Presently no ticket available', 'zndskwoo' ); ?></div>
	<?php

}
if ( ! empty( $tickets ) && is_array( $tickets ) ) {
	$user_data = wp_get_current_user();
	$user_mail = $user_data->data->user_email;

	$zndsk_acc_details = get_option( 'mwb_zndsk_account_details' );
	?>

	<div class="mwb-zndsk-ticket-table">
		<img src="<?php echo esc_html( MWB_ZENDESK_DIR_URL . 'assets/images/loader.gif' ); ?>" class="mwb-loader-zndsk" style="display:none">
		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table mwb-zndsk-ticket-table-child">
			<thead>
				<tr>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-subject"><span class="nobr"><?php esc_html_e( 'Ticket-id', 'zndskwoo' ); ?></span></th>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e( 'Subject', 'zndskwoo' ); ?></span></th>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-description"><span class="nobr"><?php esc_html_e( 'First Comment', 'zndskwoo' ); ?></span></th>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e( 'Status', 'zndskwoo' ); ?></span></th>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr"><?php esc_html_e( 'Action', 'zndskwoo' ); ?></span></th>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-check_history"><span class="nobr"><?php esc_html_e( 'Chat History', 'zndskwoo' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tickets as $single_data ) { ?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
							<?php echo esc_html( $single_data['id'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woovaluecommerce-orders-table__cell-order-subject" data-title="subject">
							<?php echo esc_html( $single_data['subject'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-description" data-title="description">
							<?php echo esc_html( $single_data['description'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="Status">
							<?php echo esc_html( $single_data['status'] ); ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="Actions">
							<a href="#TB_inline?&width=600&height=500&inlineId=<?php echo esc_html( $single_data['id'] ); ?>" class="thickbox mwb-zndsk-update-ticket"><?php esc_html_e( 'Add New Comment', 'zndskwoo' ); ?></a>
							<div id="<?php echo esc_html( $single_data['id'] ); ?>" style="display:none;">

								<form action="" method="post">
									<input type="hidden" name="nonce-for-update" value="<?php echo esc_html( wp_create_nonce( 'zndsk_ticket_updates' ) ); ?>">
									<div class="mwb-ticket-id">
										<p><b><?php esc_html_e( 'Ticket-id-', 'zndskwoo' ); ?></b><label for="ticket-id"><?php echo esc_html( $single_data['id'] ); ?></label></p>
										<input type="hidden" name="mwb-ticket-no" value="<?php echo esc_html( $single_data['id'] ); ?>">
									</div>
									<div id="mwb-updated-subject">
										<label for="ticket-subject"><?php esc_html_e( 'Subject-', 'zndskwoo' ); ?></label>
										<p><?php echo esc_html( $single_data['subject'] ); ?></p>
									</div>
									<div><input type="hidden" name="mwb-update-subject" value="<?php echo esc_html( $single_data['subject'] ); ?>"></div>

									<?php
									$select_array_email = array();


									if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
										// HPOS Enabled.
										$customer_orders    = wc_get_orders(
											array(
												'limit' => -1,
												'customer_user'    => get_current_user_id(), //phpcs:ignore
												'type'   => wc_get_order_types(),
												'status' => array_keys( wc_get_order_statuses() ),
											)
										);
									} else {

											$customer_orders    = get_posts(
												array(
													'numberposts' => -1,
													'meta_key'    => '_customer_user',
													'meta_value'  => get_current_user_id(),
													'post_type'   => wc_get_order_types(),
													'post_status' => array_keys( wc_get_order_statuses() ),
												)
											);
									}
									if ( ! empty( $customer_orders ) && is_array( $customer_orders ) ) {
										foreach ( $customer_orders as $key => $value ) {
											$orders      = wc_get_order( $value->ID );
											$order_data  = $orders->get_data();
											$user_emaill = $order_data['billing']['email'];
											if ( ! in_array( $user_emaill, $select_array_email, true ) ) {
												array_push( $select_array_email, $order_data['billing']['email'] );
											}
										}
										?>
										<div id="select_box_email_ticket_create">
											<label for="email"><?php esc_html_e( 'Choose your Email', 'zndskwoo' ); ?></label>
											<div class="mwb-update-email-wrap">
												<select name="mwb-update-email">
													<?php foreach ( $select_array_email as $key => $value ) { ?>
														<option value="<?php echo esc_html( $value ); ?>"><?php echo esc_html( $value ); ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<?php
									} else {
										?>
										<div>
											<input type="email" name="mwb-create-email" value="<?php echo esc_html( $user_mail ); ?>">
										</div>
										<?php
									}
									?>
									<div>
										<div id="mwb-updated-comment">
											<label for="ticket-comment"><?php esc_html_e( 'New Comment', 'zndskwoo' ); ?></label>
											<div>
												<textarea id="" name="mwb-update-comment" cols="30" rows="5" placeholder="Add Comment here"></textarea>
											</div>
										</div>
										<div>
											<p>
												<input type="submit" name="update_ticket_all" class="update_ticket_all" value="Update-Ticket">
											</p>
										</div>
									</div>	
								</form>
							</div>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-check_history" data-title="History">

							<button class="wps_zndsk_chat_btn" data-id="<?php echo esc_html( $single_data['id'] ); ?>">Open Chat conversation</button>
							<div id="wps_chat_modal" class="wps_zndsk_modal">
								<!-- Modal content -->
								<div class="wps_zndsk_modal-content">
									<span class="wps_zndsk_chat_close">&times;</span>
									<div class="mwb_chat_history"></div>
								</div>
							</div>
						</td>
					</tr>
					<?php
				}
}
?>
			</tbody>
		</table>
		<?php
		$user_data = wp_get_current_user();
		$user_mail = $user_data->data->user_email;
		?>
		<div class="form-button">
			<button class="mwb-zendesk-hitbutton"><b><?php esc_html_e( 'Add a new ticket from here', 'zndskwoo' ); ?></b></button>
		</div>

		<div class="mwb-zendesk-ticket-form">
			<form action="" method="POST" class="mwb-zndsk-return-back-wrappeer">
				<input type="hidden" name="nonce" value="<?php echo esc_html( wp_create_nonce( 'zndsk_ticket_check' ) ); ?>">
				<div>
					<label for="Subject"><?php esc_html_e( 'Subject:', 'zndskwoo' ); ?></label>
					<p><input type="text" class="mwb-create-subject" name="mwb-create-subject" placeholder="Enter subject" required></p>
					<p class="mwb-subject-error"></p>
				</div>

				<div>
					<label for="Comment"><?php esc_html_e( 'Comment:', 'zndskwoo' ); ?></label>
					<p><textarea class="mwb-create-comment" name="mwb-create-comment" placeholder="Enter description" required></textarea></p>
					<p class="mwb-error-comment"></p>
				</div>

				<div>
					<label for="Phone"><?php esc_html_e( 'Phone:', 'zndskwoo' ); ?></label>
					<p><input type="tel" class="mwb-create-phone" name="mwb-create-phone" placeholder="Enter Phone Number" pattern="[0-9]{10}" required></input></p>
					<p class="mwb-error-phone"></p>
				</div>

				<div>
					<label for="tags"><?php esc_html_e( 'Ticket Tags:', 'zndskwoo' ); ?></label>
					<p><input type="tag" class="mwb-create-tag" name="mwb-create-tag" placeholder="Enter Ticket Tags"></input></p>
					<p class="mwb-error-tag"></p>
				</div>

				<?php
				$select_array_email = array();

				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// HPOS Enabled.
					$customer_orders    = wc_get_orders(
						array(
							'limit' => -1,
							'customer_user'    => get_current_user_id(), //phpcs:ignore
							'type'   => wc_get_order_types(),
							'status' => array_keys( wc_get_order_statuses() ),
						)
					);
				} else {
					$customer_orders    = get_posts(
						array(
							'numberposts' => -1,
							'meta_key'    => '_customer_user',
							'meta_value'  => get_current_user_id(),
							'post_type'   => wc_get_order_types(),
							'post_status' => array_keys( wc_get_order_statuses() ),
						)
					);

				}
				if ( ! empty( $customer_orders ) && is_array( $customer_orders ) ) {
					foreach ( $customer_orders as $key => $value ) {
						$orders      = wc_get_order( $value->ID );
						$order_data  = $orders->get_data();
						$user_emaill = $order_data['billing']['email'];
						if ( ! in_array( $user_emaill, $select_array_email, true ) ) {
							array_push( $select_array_email, $order_data['billing']['email'] );
						}
					}
					?>
					<div id="select_box_email_ticket_create">
						<p><label for="email"><?php esc_html_e( 'Choose Your Email', 'zndskwoo' ); ?></label></p>
						<p>
							<select name="mwb-create-email">
								<?php foreach ( $select_array_email as $key => $value ) { ?>
									<option value="<?php echo esc_html( $value ); ?>"> <?php echo esc_html( $value ); ?></option>
								<?php } ?>
							</select>
						</p>
					</div>
					<?php
				} else {
					?>
					<div>
						<input type="email" name="mwb-create-email" value="<?php echo esc_html( $user_mail ); ?>">
					</div>
					<?php
				}
				?>
				<div>
					<p><input type="submit" id="mwb-create-submit-ticket" name="submit_ticket_all" value="Create Ticket"></p>
				</div>
			</form>
			<div>
				<button class="mwb-zndsk-return-back"><?php esc_html_e( 'Back', 'zndskwoo' ); ?></button>
			</div>
	
	</div>
