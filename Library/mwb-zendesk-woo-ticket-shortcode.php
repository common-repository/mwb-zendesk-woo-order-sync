<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    mwb-zendesk-woo-order-sync/Library
 * @subpackage mwb-zendesk-woo-order-sync/Library
 */

 $this->mwb_zendeskconnect_manager = MWB_ZENDESK_Manager::get_instance();
 $this->mwb_zendeskconnect_manager->mwb_my_account_endpoint_content_main();
