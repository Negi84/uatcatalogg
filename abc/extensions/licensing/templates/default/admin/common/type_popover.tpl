<script type="application/javascript">
	$('#product_type').on('change', function(){
		var enable = $(this).val() !== 'E';
		var fields = ['<?php echo implode("', '", $exclude_fields)?>'];
		for(var i in fields){
			var name = fields[i];
			var fld = $('#productFrm_'+name);
			if(fld.length){
				if(enable){
					fld.removeAttr('disabled');
				}else{
					fld.val('').attr('disabled', 'disabled');
				}
			}
		}
 	});

	$('#catalog_only_layer button').on('click', function () {
		var name ='external_url';
		if (name == 'external_url' && $('#catalog_only_layer button').is('.btn-off')) {
			$('#'+name).removeAttr('disabled');
		} else {
			$('#'+name).attr('disabled', 'disabled');
		}
	});

	$(document).ready( function(){
	    if($('#product_type').attr('readonly') !== 'readonly') {
            $('#product_type').change();
        }
		$('#catalog_only_layer button').click();
	});
</script>
