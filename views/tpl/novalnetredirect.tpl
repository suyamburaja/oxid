<div class="container">
	<div class="main_row">
[{include file="layout/header.tpl"}]
    <div id="wrapper" style="height:420px;padding:2em;">
	<label>[{ oxmultilang ident="NOVALNET_REDIRECT_MESSAGE" }]</label>
		 <form action="[{$sNovalnetFormAction}]" id="novalnet_redirect_form" method="post" onsubmit="return checkBeforeSubmit()">
			[{foreach key=sNovalnetKey from=$aNovalnetFormData item=sNovalnetValue}]
			<input type="hidden" name="[{$sNovalnetKey}]" value="[{$sNovalnetValue}]" />
			[{/foreach}]
			<input type="submit" id="novalnet_button_submit" class="btn btn-primary" value="[{oxmultilang ident='NOVALNET_REDIRECT_SUBMIT'}]" />
			</form>
    <script type="text/javascript">
    setTimeout(function(){ document.getElementById('novalnet_button_submit').click(); }, 500);
	var beforeSubmitted = false;    
    function checkBeforeSubmit(){
      if (!beforeSubmitted) {
        beforeSubmitted = true;
        return beforeSubmitted;
      }
      return false;
    }    
	</script>
    </div>
[{include file="layout/footer.tpl"}]    
[{include file="layout/base.tpl"}]
</div>
</div>
