<?php include($tpl_common_dir.'action_confirm.tpl'); ?>

<?php echo $summary_form; ?>

<?php echo $product_tabs ?>
<div id="content" class="panel panel-default">

	<div class="panel-heading col-xs-12">
		<div class="pull-left col-sm-12">
			<div class="btn-group mr10  col-sm-12">
                <?php
                if ($search_form) {
                    echo $search_form['form_open']; ?>
					<div class="form-group pull-left">
						<label class="form-group pull-left mr5">
							<?php echo $this->language->get('licensing_upload_file_label')?></label>
						<div class="input-group input-group-sm pull-left">
							<select name="product_option_value_id" class="form-control aselect ">
                                <?php
                                foreach ($option_list as $opt) {
                                    echo '<optgroup label="'.$opt['name'].'">';
                                    foreach ($opt['values'] as $value) {
                                        echo '<option value="'.$value['product_option_value_id'].'">'.$value['name']
                                            .'</option>';
                                    }
                                    echo '</optgroup>';
                                }
                                ?>
							</select>
						</div>
						<div class="input-group input-group-sm pull-left">
							<?php $f = $search_form['fields']['file']; ?>
							<input name="<?php echo $f->name; ?>"
								   id="license_file_upload"
								   type="file"
								   style="height: 26px !important; margin: 0 5px;"
								   multiple="false">
						</div>
					</div>
					<div class="form-group pull-left ml10">
						<button type="submit" class="btn btn-xs btn-primary tooltips">
							<?php echo $search_form['submit']->text; ?>
						</button>
					</div>
				</form>
                <?php } else { ?>
					<div class="alert alert-warning"><?php echo "Note: To upload file with licenses you should to create product option for assignment first."; ?></div>
                <?php } ?>
			</div>
		</div>

        <?php include($tpl_common_dir.'content_buttons.tpl'); ?>
	</div>

	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
        <?php echo $listing_grid; ?>
	</div>
</div>

<script type="application/javascript">
	$("#product_grid_go").on('click', function () {
		//get all selected rows based on multiselect
		var ids = $('#product_grid').jqGrid('getGridParam', 'selarrrow');
		//get single selected row
		ids.push($('#product_grid').jqGrid('getGridParam', 'selrow'));
		if (!ids.length) {
			return;
		}

		if ($('#product_grid_selected_action').val() == 'relate') {
			var form_data = $('#product_grid_form').serializeArray();
			form_data.push({name: 'id', value: ids});
			form_data.push({name: 'oper', value: 'relate'});
			$.ajax({
				url: '<?php echo $relate_selected_url; ?>',
				type: 'POST',
				data: form_data,
				success: function (msg) {
					if (msg == '') {
						jQuery('#product_grid').trigger("reloadGrid");
						success_alert('<?php \H::js_echo($text_success_relation_set);?>', true);
					} else {
						alert(msg);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert(textStatus + ": " + errorThrown);
				}
			});
		}
	});

</script>
