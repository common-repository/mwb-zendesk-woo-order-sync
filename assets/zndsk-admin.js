jQuery(document).ready(function($) {

	// zndsk_ajax_object variables.
	var ajaxUrl              = zndsk_ajax_object.ajax_url;
	var zndskSecurity        = zndsk_ajax_object.zndskSecurity;
	var zndskMailSuccess     = zndsk_ajax_object.zndskMailSuccess;
	var zndskMailFailure     = zndsk_ajax_object.zndskMailFailure;
	var zndskMailAlreadySent = zndsk_ajax_object.zndskMailAlreadySent;

	jQuery( '.mwb-reject-button' ).on(
		'click',function(){
			jQuery( '#zndsk_loader' ).show();
			jQuery.post(
				ajaxUrl , {'action' : 'mwb_zndsk_suggest_later', 'zndskSecurity' : zndskSecurity }, function(response){
					location.reload();
				}
			);
		}
	);
	jQuery( '.mwb-accept-button' ).on(
		'click', function() {
			jQuery( '#zndsk_loader' ).show();
			jQuery.post(
				ajaxUrl , { 'action' : 'mwb_zndsk_suggest_accept', 'zndskSecurity' : zndskSecurity}, function( response ) {

					if ( response == '"success"' ) {
						alert( zndskMailSuccess );
						location.reload();
					} else if ( response == '"alreadySent"' ) {
						alert( zndskMailAlreadySent );
						location.reload();
					} else {
						alert( zndskMailFailure );
						location.reload();
					}
				}
			);
		}
	);
	
	// Drag and Drop initialization.
	$('.mwb-zndsk-field-drag.kpi-fields').draggable({
		cursor: "move",
		revert: true,
		distance: 50,
		scope: 'kpi-fields-scope',
		containment: "parent",
		connectToSortable: "#mwb-zndsk-kpi-fields-dvdest",
		drop: function(event, ui) {
			$(this).find('ul').append(ui.draggable);
		}
	});
	
	$('.mwb-zndsk-field-drop.kpi-fields').droppable({

		hoverClass: "hoverDrop",
		tolerance: "pointer",
		scope: 'kpi-fields-scope',
		drop: function(event, ui) {
			$(this).append(ui.draggable);
		}
	});

	$('.mwb-zndsk-field-drag.order-fields').draggable({
		cursor: "move",
		revert: true,
		distance: 50,
		scope: 'order-fields-scope',
		containment: "parent",
		connectToSortable: "#mwb-zndsk-order-fields-dvdest",
		drop: function(event, ui) {
			$(this).find('ul').append(ui.draggable);
		}
	});
	
	$('.mwb-zndsk-field-drop.order-fields').droppable({

		hoverClass: "hoverDrop",
		tolerance: "pointer",
		scope: 'order-fields-scope',
		drop: function(event, ui) {
			$(this).append(ui.draggable);
		}
	});

	// Order config options ajax handling.
	var latest_orders_count = '';
	var source_kpi_fields = [];
	var selected_kpi_fields = [];
	var source_order_fields = [];
	var selected_order_fields = [];

	$(document).on( 'submit', '#mwb-zndsk-order-config-form', function(e){

		e.preventDefault();

		// Reinitialize array on every click.
		source_kpi_fields = [];
		selected_kpi_fields = [];
		source_order_fields = [];
		selected_order_fields = [];

		latest_orders_count = $('#mwb-zndsk-latest-orders-count').val();

		enable_create_ticket_on_order_status = $('#enable_create_ticket_on_order_status').is(':checked') ? 1 : 0;
		order_status_for_ticket = $('#order_status_for_ticket').val();
		mwb_create_subject_automatic = $('#mwb_create_subject_automatic').val();
		mwb_create_comment_automatic = $('#mwb_create_comment_automatic').val();
		mwb_create_tag_automatic = $('#mwb_create_tag_automatic').val();
	


		$('#mwb-zndsk-kpi-fields-dvsource li').each(function(i, li) {
			source_kpi_fields.push( $(this).data('name') );
		});
		
		$('#mwb-zndsk-kpi-fields-dvdest li').each(function(i, li) {
			selected_kpi_fields.push( $(this).data('name') );
		});

		$('#mwb-zndsk-order-fields-dvsource li').each(function(i, li) {
			source_order_fields.push( $(this).data('name') );
		});
		
		$('#mwb-zndsk-order-fields-dvdest li').each(function(i, li) {
			selected_order_fields.push( $(this).data('name') );
		});

		jQuery.post(ajaxUrl,{
			action:'mwb_zndsk_save_order_config_options', 
			latest_orders_count:latest_orders_count,
			source_kpi_fields:source_kpi_fields,
			selected_kpi_fields:selected_kpi_fields,
			source_order_fields:source_order_fields,
			selected_order_fields:selected_order_fields,
			enable_create_ticket_on_order_status: enable_create_ticket_on_order_status,
			order_status_for_ticket : order_status_for_ticket, 
			mwb_create_subject_automatic : mwb_create_subject_automatic,
			mwb_create_comment_automatic : mwb_create_comment_automatic,
			mwb_create_tag_automatic : mwb_create_tag_automatic,
			zndskSecurity:zndskSecurity
		},
		function(data){

			if( 'true' == data ) {

				$('.mwb-zndsk-order-config-notice.settings-saved').show();
				$('.mwb-zndsk-order-config-notice.settings-not-saved').hide();
			}

			else {

				$('.mwb-zndsk-order-config-notice.settings-not-saved').show();
				$('.mwb-zndsk-order-config-notice.settings-saved').hide();
			}

			$('html, body').animate({
				scrollTop: 0
			}, 1000);
			
		});
	});

	var acc = document.getElementsByClassName("zndsk_accordion");
	var i;
	$(document).on("click","button.data.zndsk_accordion", function(e) {
		e.preventDefault();
		this.classList.toggle("active");
		var panel = this.nextElementSibling;
		if (panel.style.maxHeight){
			panel.style.maxHeight = null;
		} else {
			panel.style.maxHeight = panel.scrollHeight + "px";
		} 
	});


	$(document).ready(function(){
		setTimeout(function(){ 
			$('.sucessful-msg').hide(); 
		 }, 3000);
	});

});
