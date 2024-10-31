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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$handled_order_config_options = mwb_zndskwoo_get_order_config_options();

$latest_orders_count = $handled_order_config_options['latest_orders_count'];

// KPI Fields.
$all_kpi_fields      = mwb_zndskwoo_order_config_get_all_kpi_fields();
$source_kpi_fields   = $handled_order_config_options['source_kpi_fields'];
$selected_kpi_fields = $handled_order_config_options['selected_kpi_fields'];

// Order Fields.
$all_order_fields      = mwb_zndskwoo_order_config_get_all_order_fields();
$source_order_fields   = $handled_order_config_options['source_order_fields'];
$selected_order_fields = $handled_order_config_options['selected_order_fields'];

$enable_create_ticket_on_order_status = get_option( 'enable_create_ticket_on_order_status' );
$order_status_for_ticket = get_option( 'order_status_for_ticket', '' );

$mwb_create_subject_automatic = get_option( 'mwb_create_subject_automatic' );
$mwb_create_comment_automatic = get_option( 'mwb_create_comment_automatic' );
$mwb_create_tag_automatic = get_option( 'mwb_create_tag_automatic' );
$order_statuses = wc_get_order_statuses();

?>

<div class="zndsk_setting_ticket_wrapper">
	<div class="zndsk_setting_wrapper mwb-zndsk-order-config-options">
		<h2><?php esc_html_e( 'Order Configuration Settings', 'zndskwoo' ); ?></h2>	
		<!-- Settings saved notice. -->
		<div class="mwb-zndsk-order-config-notice settings-saved notice notice-success is-dismissible" style="display: none;"> 
			<p><?php esc_html_e( 'Selected Options Saved.', 'zndskwoo' ); ?></p>
		</div>
		<!-- Settings not saved notice. -->
		<div class="mwb-zndsk-order-config-notice settings-not-saved notice notice-warning is-dismissible" style="display: none;"> 
			<p><?php esc_html_e( 'No Changes.', 'zndskwoo' ); ?></p>
		</div>

		<form id="mwb-zndsk-order-config-form" method="POST">
		<table class="zndsk_setting_table">
			<tbody>
				<tr>
					<td class="zendesk-column zendesk-col-left  zendesk-url-column"><strong><?php esc_html_e( 'Enable to Create Automatic Ticket based on Order Status', 'zndskwoo' ); ?></strong>
					<td class="zendesk-column zendesk-col-right">

						<input type="checkbox" id="enable_create_ticket_on_order_status" name="enable_create_ticket_on_order_status" <?php checked( 1, $enable_create_ticket_on_order_status ); ?>  >
					</td>
				</tr>
				<tr>
					<td class="zendesk-column zendesk-col-left zendesk-url-column">
						<strong><?php esc_html_e( 'Select Order Status for Zendesk Ticket', 'zndskwoo' ); ?></strong>
					</td>
					<td class="zendesk-column zendesk-col-right">
						<select id="order_status_for_ticket" name="order_status_for_ticket">
							<?php foreach ( $order_statuses as $status_key => $status_name ) : ?>
								<option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $order_status_for_ticket, $status_key ); ?>>
									<?php echo esc_html( $status_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="zendesk-column zendesk-col-left zendesk-url-column">
						<strong><?php esc_html_e( 'Enter Subject For Automatic Ticket', 'zndskwoo' ); ?></strong>
						<div class="mwb-tool-tip">
							<span class="icon">?</span>
							<span class="description"><?php esc_html_e( 'If the subject value is not entered, the automatic ticket will not be created based on the order status.', 'zndskwoo' ); ?></span>
						</div>
					</td>
					<td>
					<input type="text" id="mwb_create_subject_automatic" name="mwb_create_subject_automatic" placeholder="Enter Automatic Subject" value="<?php echo esc_html( ! empty( $mwb_create_subject_automatic ) ? $mwb_create_subject_automatic : '' ); ?>">
					</td>
				</tr>
				<tr>
					<td class="zendesk-column zendesk-col-left zendesk-url-column">
						<strong><?php esc_html_e( 'Enter Comment For Automatic Ticket', 'zndskwoo' ); ?></strong>
						<div class="mwb-tool-tip">
							<span class="icon">?</span>
							<span class="description"><?php esc_html_e( 'If the comment value is not entered, the automatic ticket will not be created based on the order status.', 'zndskwoo' ); ?></span>
						</div>
					</td>
					<td>
					<input type="text" id="mwb_create_comment_automatic" name="mwb_create_comment_automatic" placeholder="Enter Automatic comment" value="<?php echo esc_html( ! empty( $mwb_create_comment_automatic ) ? $mwb_create_comment_automatic : '' ); ?>">
					</td>
				</tr>

				<tr>
					<td class="zendesk-column zendesk-col-left zendesk-url-column">
						<strong><?php esc_html_e( 'Enter Tag For Automatic Ticket', 'zndskwoo' ); ?></strong>
						<div class="mwb-tool-tip">
							<span class="icon">?</span>
							<span class="description"><?php esc_html_e( 'If the tag value is not entered, the automatic ticket will not be created based on the order status.', 'zndskwoo' ); ?></span>
						</div>
					</td>
					<td>
					<input type="text" id="mwb_create_tag_automatic" name="mwb_create_tag_automatic" placeholder="Enter Automatic tag" value="<?php echo esc_html( ! empty( $mwb_create_tag_automatic ) ? $mwb_create_tag_automatic : '' ); ?>" >
					</td>
				</tr>
								
				<tr>
					<td class="zendesk-column zendesk-col-left  zendesk-url-column"><strong><?php esc_html_e( 'Latest Orders Count', 'zndskwoo' ); ?></strong>
						<div class="mwb-tool-tip">
							<span class="icon">?</span>
							<span class="description"><?php esc_html_e( 'Select the number of orders that will list on Zendesk app', 'zndskwoo' ); ?></span>
							</div>
					</td>

					<td class="zendesk-column zendesk-col-right"><input required="required" type="number" min="1" max="100" id="mwb-zndsk-latest-orders-count" value="<?php echo esc_html( $latest_orders_count ); ?>">

					</td>
				</tr>

				<tr>
					<td class="zendesk-column zendesk-col-left  zendesk-url-column"><strong><?php esc_html_e( 'KPI Fields', 'zndskwoo' ); ?></strong>
						<div class="mwb-tool-tip">
							<span class="icon">?</span>
							<span class="description"><?php esc_html_e( 'KPI fields for Zendesk App', 'zndskwoo' ); ?></span>
							</div>
					</td>
					<td class="zendesk-column zendesk-col-right">
						<div class="mwb-zndsk-order-config-fields__drag-drop mwb-zndsk-clearfix">
							<div class="mwb-zndsk-heading-title-wrap">
								<h6 class="heading"><?php esc_html_e( 'List of fields that you can show under KPI fields on Zendesk app', 'zndskwoo' ); ?></h6>
								<div class="mwb-zndsk-order-config-fields__drag mwb-zndsk-clearfix">

									<ul id="mwb-zndsk-kpi-fields-dvsource" class="mwb-zndsk-field-drop kpi-fields">

										<?php

										if ( ! empty( $source_kpi_fields ) && is_array( $source_kpi_fields ) ) {

											foreach ( $all_kpi_fields as $field_key => $field_name ) {

												if ( in_array( $field_key, $source_kpi_fields, true ) ) :
													?>
													<li class="mwb-zndsk-field-drag kpi-fields" data-name='<?php echo esc_html( $field_key ); ?>'><?php echo esc_html( $field_name ); ?></li>

													<?php
												endif;
											}
										}

										?>
									</ul>
								</div>
							</div>	

							<img src="<?php echo esc_html( MWB_ZENDESK_DIR_URL . 'assets/images/switch.png' ); ?>" alt="" class="mwb-zndsk-switch-icon">
							<div class="mwb-zndsk-heading-title-wrap">
								<h6 class="heading"><?php esc_html_e( 'List of fields that will be shown under KPI fields on Zendesk app', 'zndskwoo' ); ?></h6>
								<div class="mwb-zndsk-order-config-fields__drop mwb-zndsk-clearfix">
									<ul id="mwb-zndsk-kpi-fields-dvdest" class="mwb-zndsk-field-drop kpi-fields">

										<?php

										if ( ! empty( $selected_kpi_fields ) && is_array( $selected_kpi_fields ) ) {

											foreach ( $all_kpi_fields as $field_key => $field_name ) {

												if ( in_array( $field_key, $selected_kpi_fields, true ) ) :
													?>

													<li class="mwb-zndsk-field-drag kpi-fields" data-name='<?php echo esc_html( $field_key ); ?>'><?php echo esc_html( $field_name ); ?></li>

													<?php
												endif;
											}
										}

										?>
									</ul>
								</div>
							</div>
						</div> 

					</td>
				</tr>

				<tr>
					<td class="zendesk-column zendesk-col-left  zendesk-url-column"><strong><?php esc_html_e( 'Order Fields', 'zndskwoo' ); ?></strong>
						<div class="mwb-tool-tip">
							<span class="icon">?</span>
							<span class="description"><?php esc_html_e( 'Order fields for Zendesk App', 'zndskwoo' ); ?></span>
						</div>
					</td>
					<td class="zendesk-column zendesk-col-right">

						<div class="mwb-zndsk-order-config-fields__drag-drop mwb-zndsk-clearfix">
							<div class="mwb-zndsk-heading-title-wrap">
								<h6 class="heading"><?php esc_html_e( ' List of order fields that you can show on Zendesk app', 'zndskwoo' ); ?></h6>
								<div class="mwb-zndsk-order-config-fields__drag mwb-zndsk-clearfix">

									<ul id="mwb-zndsk-order-fields-dvsource" class="mwb-zndsk-field-drop order-fields">

										<?php

										if ( ! empty( $source_order_fields ) && is_array( $source_order_fields ) ) {

											foreach ( $all_order_fields as $field_key => $field_name ) {

												if ( in_array( $field_key, $source_order_fields, true ) ) :
													?>
													<li class="mwb-zndsk-field-drag order-fields" data-name='<?php echo esc_html( $field_key ); ?>'><?php echo esc_html( $field_name ); ?></li>

													<?php
												endif;
											}
										}

										?>
									</ul>
								</div>
							</div>	

							<img src="<?php echo esc_html( MWB_ZENDESK_DIR_URL . 'assets/images/switch.png' ); ?>" alt="" class="mwb-zndsk-switch-icon">

							<div class="mwb-zndsk-heading-title-wrap">
							<h6 class="heading"><?php esc_html_e( 'List of order fields that will be shown on Zendesk app', 'zndskwoo' ); ?></h6>
								<div class="mwb-zndsk-order-config-fields__drop mwb-zndsk-clearfix">
									<ul id="mwb-zndsk-order-fields-dvdest" class="mwb-zndsk-field-drop order-fields">

										<?php

										if ( ! empty( $selected_order_fields ) && is_array( $selected_order_fields ) ) {

											foreach ( $all_order_fields as $field_key => $field_name ) {

												if ( in_array( $field_key, $selected_order_fields, true ) ) :
													?>

												<li class="mwb-zndsk-field-drag order-fields" data-name='<?php echo esc_html( $field_key ); ?>'><?php echo esc_html( $field_name ); ?></li>

													<?php
												endif;
											}
										}

										?>
									</ul>
								</div>
							</div>	
						</div> 

					</td>
				</tr>

				<tr>
					<td class="zendesk-column zendesk-col-left  zendesk-url-column"><strong><?php esc_html_e( 'Ticket ShortCode', 'zndskwoo' ); ?></strong>
					<div class="mwb-tool-tip">
					<span class="icon">?</span>
					<span class="description"><?php esc_html_e( 'Use this ShortCode to Create a New Section For Ticket History', 'zndskwoo' ); ?></span>
					</div>
					</td>

					<td class="zendesk-column zendesk-col-right">
						<input type="text" value="[mwb-ticket-history]" readonly>
					</td>
					
				</tr>

				<tr>
					<td colspan="2" class="zendesk-submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Options', 'zndskwoo' ); ?></button>
					</td>
				</tr>
			</tbody>
		</table></form>
	</div>
</div>
