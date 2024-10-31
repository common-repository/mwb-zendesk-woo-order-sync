jQuery( document ).ready(function($){

	let href = '';

 	// Deactivate Modal Open.
	jQuery('#deactivate-mwb-zendesk-woo-order-sync').on('click', function(evt) {
		href = jQuery(this).attr( 'href' );
		evt.preventDefault();
		jQuery('.mwb-g-modal__cover').addClass('show-g_modal_cover');
		jQuery('.mwb-g-modal__message').addClass('show-g_modal_message');
	});
	
	// Deactivate Modal close.
	jQuery('.mwb-w-modal__cover, .mwb-g-modal__close').on('click', function() {
		jQuery('.mwb-g-modal__cover').removeClass('show-g_modal_cover');
		jQuery('.mwb-g-modal__message').removeClass('show-g_modal_message');
		if ( href.length > 0 ) {
			window.location.replace( href );
		}
	});
	jQuery("#zendesk_skip_deactive").on('click',function(){
		if ( href.length > 0 ) {
			window.location.replace( href );
		}
	});	  
});	