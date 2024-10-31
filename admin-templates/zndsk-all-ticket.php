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
 * This file includes Zendesk Order configuration settings.
 *
 * @link       https://wpswings.com/
 * @since      2.0.2
 *
 * @package    mwb-zendesk-woo-order-sync
 * @subpackage mwb-zendesk-woo-order-sync/admin-templates
 */
$ticket = json_decode( $ticket );
if ( empty( $ticket ) ) {

	?>
		<div class="error" style="display:block;" >
			<span>
			<?php
				echo esc_html_e( 'Presently no ticket available', 'zndskwoo' );
				wp_die();
			?>
			</span>
		</div>
	<?php

}
$html = '';
if ( ! empty( $ticket ) || is_array( $ticket ) ) {
	$zndsk_acc_details = get_option( 'mwb_zndsk_account_details' );

	$html .= '<div class="ticket-table">
				<table id="myTable2">
					<thead>
						<tr>
									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">' . esc_html__( 'Ticket-id', 'zndskwoo' ) . '</span></th>
									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-subject"><span class="nobr">' . esc_html__( 'Subject', 'zndskwoo' ) . '</span></th>
									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-description"><span class="nobr">' . esc_html__( 'Description', 'zndskwoo' ) . '</span></th>
									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr">' . esc_html__( 'Status', 'zndskwoo' ) . '</span></th>
									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr">' . esc_html__( 'View-Tickets', 'zndskwoo' ) . '</span></th>
						</tr>
					</thead>
					<tbody>';
	foreach ( $ticket as $single_data ) {
		$html .= '<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
					' . $single_data->id . '
					</td>
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
					' . $single_data->subject . '
					</td>	<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
					' . $single_data->description . '
					</td>	<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order">
					' . $single_data->status . '
					</td>
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="Actions">
						<a href= "' . $zndsk_acc_details['acc_url'] . 'agent/tickets/' . $single_data->id . '" target="_blank"> View </a>
					</td>
					</tr>';
	}
		$html .= '</tbody></table></div>';
		echo wp_kses_post( wpautop( wptexturize( $html ) . PHP_EOL ) );
		wp_die();
}

