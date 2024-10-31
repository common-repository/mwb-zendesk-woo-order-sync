jQuery(document).ready(function(e) {
  var t = 0, n = 0;
  function i() {
      e("#select_box_email").hide(), e(".mwb-error-messege").hide(), e(".mwb-zendesk-hitbutton").hide(), 
      e(".mwb-zndsk-ticket-table .mwb-zndsk-ticket-table-child").hide(), e(".mwb-zendesk-ticket-form").show();
  }
  function c() {
      e(".mwb-error-messege").show(), e(".mwb-zendesk-ticket-form").hide(), e(".mwb-zndsk-ticket-table .mwb-zndsk-ticket-table-child").show(), 
      e(".mwb-zendesk-hitbutton").show(), e("#select_box_email").show();
  }
  function o() {
      2 <= n + t && e("#mwb-create-submit-ticket").attr("disabled", !1);
  }
  e(".mwb-zendesk-ticket-form").hide(), e("#mwb-create-submit-ticket").attr("disabled", !0), 
  e(".mwb-zendesk-hitbutton").click(function() {
      i();
  }), e(".mwb-zndsk-return-back").click(function() {
      c();
  }), 
  e(document).on("focusout", ".mwb-create-subject", function() {
      "" == e(this).val() ? (e(document).find(".mwb-subject-error").html("*Select Subject"), 
      e(document).find(".mwb-subject-error").show(), e(this).css("border", "solid 2px red"), 
      e(document).find("#mwb-create-submit-ticket").attr("disabled", !0), t = 0) : (t = 1, 
      e(document).find(".mwb-subject-error").hide(), e(this).css("border", "solid 2px green"), 
      o());
  }), e(document).on("focusout", ".mwb-create-comment", function() {
      "" == e(this).val() ? (e(document).find(".mwb-error-comment").html("*Select Comment"), 
      e(document).find(".mwb-error-comment").show(), e(this).css("border", "solid 2px red"), 
      e(document).find("#mwb-create-submit-ticket").attr("disabled", !0), n = 0) : (n = 1, 
      e(document).find(".mwb-error-comment").hide(), e(this).css("border", "solid 2px green"), 
      o());
  }), e(document).on("focusout", ".mwb-create-phone", function() {
      "" == e(this).val() ? (e(document).find(".mwb-error-phone").html("*Enter Number"), 
      e(document).find(".mwb-error-phone").show(), e(this).css("border", "solid 2px red"), 
      e(document).find("#mwb-create-submit-ticket").attr("disabled", !0), n = 0) : (n = 1, 
      e(document).find(".mwb-error-phone").hide(), e(this).css("border", "solid 2px green"), 
      o());
  }),
  t = zndsk_ajax_ticket_object.zndskSecurity, n = zndsk_ajax_ticket_object.ajax_url;
  jQuery.post(n, {
      action: "mwb_zndsk_tickt_email",
      email: e('#mwb-zendsk-email').first().val(),
      nonce: t
  }, function(t) {
    
      e(".mwb-zndsk-ticket-table").html(t);
  });
  e("#mwb-zendsk-email").on("change", function() {
      email = this.value, e(".mwb-loader-zndsk").show();
      var t = zndsk_ajax_ticket_object.zndskSecurity, n = zndsk_ajax_ticket_object.ajax_url;
      jQuery.post(n, {
          action: "mwb_zndsk_tickt_email",
          email: email,
          nonce: t
      }, function(t) {
          e(".mwb-zndsk-ticket-table").html(t);
      });
  }), e(document).ajaxComplete(function() {
      e(".mwb-loader-zndsk").hide(), e(".mwb-zendesk-ticket-form").hide(), e(".mwb-zendesk-hitbutton").click(function() {
          i();
      }), e(".mwb-zndsk-return-back").click(function() {
          c();
      });
  });

  e(document).on('click','.wps_zndsk_chat_btn',function(){  

    e(".mwb_chat_history").html('');
        var id = e(this).data("id");
        e("#wps_chat_modal").show();
               
        e( '.wps_wrma_exchange_loader_'+id ).show();
       
        var n = zndsk_ajax_ticket_object.ajax_url;
        var ajaxnonce = zndsk_ajax_ticket_object.zndskSecurity;
        jQuery.post(n,{
            action: "mwb_zndsk_chat_history",
            ticketid: id,
            nonce : ajaxnonce
        }, function(response){
            
            
           e('.wps_wrma_exchange_loader_'+id).hide();
          
            e(".mwb_chat_history").html(response);
        });      
  });
  e(document).on('click','.wps_zndsk_chat_close',function(){  
        e("#wps_chat_modal").hide();
  });

});