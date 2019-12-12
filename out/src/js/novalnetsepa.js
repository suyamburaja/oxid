/*
 * Novalnet SEPA script
 *
 * @author    Novalnet AG
 * @copyright Copyright by Novalnet
 * @license   https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */

$( document ).ready(function() {
    var maskReference =  $("select#novalnet_sepa_ref_acc option").filter(":selected").val();
      $('#reference_tid').val(maskReference);
       var sepa_new = $('#novalnet_sepa_new_details').val();
       if (sepa_new ==0) {
            $('#novalnet_sepa_save_card').css('display', 'none');
       }
});

 if($('#novalnet_sepa_new_details').length && $('#novalnet_sepa_new_details').val() == 1) {
        $('.novalnet_sepa_new_acc').show();
        $('.novalnet_sepa_ref_acc').hide();
    }
$( "div.sepamobile ul.novalnet_sepa_ref_acc_mobile li.novalnet_sepa_ref_acc_mobileli a" ).on( "click", function() {
  maskReference = $(this).attr("nn-data-value");
    if(maskReference)
        $('#reference_tid').val($(this).attr("nn-data-value"));
});
$("#mandate_information").click(function() {	
	if ($("#mandate_confirm_value").val() == '1' ) {
		$('#mandate_information_message').css('display', 'none');
		$("#mandate_confirm_value").val('0');		
	} else {
		$("#mandate_confirm_value").val('1');
		$('#mandate_information_message').css('display', 'block');		
	}			
});
/**
 * Toggles account type while onclick shopping enabled for sepa
 *
 */
function changeSepaAccountType(event, accType)
{
    var currentAccType = event.target.id;
    $('.' + currentAccType).hide();
    $('.' + accType).show();
    if (accType == 'novalnet_sepa_new_acc') {
        $('#novalnet_sepa_save_card').css('display', 'block');
        $('#novalnet_sepa_new_details').val(1);
    } else {
        $('#novalnet_sepa_new_details').val(0);
         $('#novalnet_sepa_save_card').css('display', 'none');
    }

    $('#novalnet_sepa_mandate_confirm').attr('checked',false);
}

  $('#novalnet_sepa_acc_no').keyup(function (event) {
                           this.value = this.value.toUpperCase();
                           var field = this.value;
                           var value = "";
                           for(var i = 0; i < field.length;i++){
                                   if(i <= 1){
                                           if(field.charAt(i).match(/^[A-Za-z]/)){
                                                   value += field.charAt(i);
                                           }
                                   }
                                   if(i > 1){
                                           if(field.charAt(i).match(/^[0-9]/)){
                                                   value += field.charAt(i);
                                           }
                                   }
                           }
                           field = this.value = value;
          });
