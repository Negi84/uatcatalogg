<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $tabs; ?>

<div id="content" class="panel panel-default">
	<?php if ($customer_id) { ?>
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">

			<?php if (!empty ($list_url)) { ?>
			<div class="btn-group">
				<a class="btn btn-white tooltips" href="<?php echo $list_url; ?>" data-toggle="tooltip" data-original-title="<?php echo $text_back_to_list; ?>">
                    <i class="fa fa-arrow-left fa-lg"></i>
                </a>
            </div>
            <?php } ?>

            <div class="btn-group">
                <button class="btn btn-default dropdown-toggle tooltips"
                        data-original-title="<?php echo $text_edit_address; ?>"
                        title="<?php echo $text_edit_address; ?>" type="button" data-toggle="dropdown">
                    <i class="fa fa-book"></i>
                    <?php echo $current_address; ?><span class="caret"></span>
                </button>
                <?php if ($addresses) { ?>
                    <ul class="dropdown-menu">
                        <?php foreach ($addresses as $address) { ?>
                            <li><a href="<?php echo $address['href'] ?>"
                                   class="<?php echo $address['title'] == $current_address ? 'disabled' : ''; ?>">
                                    <?php if ($address['default']) { ?>
                                        <i class="fa fa-check"></i>
                                    <?php } ?>
                                    <?php echo $address['title'] ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
            <div class="btn-group mr20 toolbar">
                <a class="actionitem btn btn-primary tooltips" href="<?php echo $add_address_url; ?>"
                   title="<?php echo $text_add_address; ?>">
                    <i class="fa fa-plus fa-fw"></i>
                </a>
            </div>
            <div class="btn-group mr10 toolbar">
                <?php echo $this->getHookVar('toolbar_pre'); ?>
                <?php if ($register_date) { ?>
                    <a class="btn btn-white disabled"><?php echo $register_date; ?></a>
				<?php } ?>
				<?php if($last_login){?>
				<a class="btn btn-white disabled"><?php echo $last_login; ?></a>
				<?php } ?>
				<a class="btn btn-white disabled"><?php echo $balance; ?></a>
				<a target="_blank"
				   class="btn btn-white tooltips"
				   href="<?php echo $button_orders_count->href; ?>"
				   data-toggle="tooltip"
				   title="<?php echo $button_orders_count->title; ?>"
				   data-original-title="<?php echo $button_orders_count->title; ?>"><?php echo $button_orders_count->text; ?>
				</a>
				<?php if($reset_password){ ?>
				<a target="_blank"
				   class="btn btn-white tooltips"
				   data-toggle="tooltip"
				   data-confirmation="delete"
				   data-confirmation-text="<?php echo $warning_resend_password;?>"
				   onclick = "sendPasswordReset();return false;"
				   title="<?php echo $reset_password->title; ?>"
				   data-original-title="<?php echo $reset_password->title; ?>"><i class="fa fa-key "></i>
				</a>
				<?php } ?>
				<a target="_blank"
                   class="btn btn-white tooltips"
                   href="<?php echo $message->href; ?>"
                   data-toggle="tooltip"
                   title="<?php echo $message->text; ?>"
                   data-original-title="<?php echo $message->text; ?>"><i class="fa fa-paper-plane"></i>
				</a>
				<a target="_blank"
                   class="btn btn-white tooltips"
                   href="<?php echo $new_order->href; ?>"
                   data-toggle="tooltip"
                   title="<?php echo $new_order->text; ?>"
                   data-original-title="<?php echo $new_order->text; ?>"><i class="fa fa-flag"></i>
				</a>
				<a target="_blank"
				   class="btn btn-white tooltips"
				   href="<?php echo $actas->href; ?>"
				   data-toggle="tooltip"
				   title="<?php echo $actas->text; ?>"
					<?php
					//for additional store show warning about login in that store's admin (because of crossdomain restriction)
					if($warning_actonbehalf){ ?>
						data-confirmation="delete"
						data-confirmation-text="<?php echo $warning_actonbehalf;?>"
					<?php } ?>
				   data-original-title="<?php echo $actas->text; ?>"><i class="fa fa-male"></i>
				</a>
				<?php
				if ($auditLog) {
				?>
				<a data-toggle="modal"
				   class="btn btn-white tooltips"
				   data-target="#viewport_modal"
				   href="<?php echo $auditLog->vhref; ?>"
				   data-fullmode-href="<?php echo $auditLog->href; ?>"
				   rel="audit_log"
				   title="<?php echo $auditLog->text; ?>">
					<i class="fa fa-history "></i></a>
				<?php
				}
				?>
				<?php echo  $this->getHookVar('toolbar_post'); ?>
			</div>
		</div>
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	</div>
	<?php }	?>

	<?php echo $form['form_open'];
	foreach($form['fields'] as $section=>$fields){
	?>

	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<label class="h4 heading"><?php echo ${'tab_customer_' . $section}; ?></label>
		<?php foreach ($fields as $name => $field) { ?>
		<?php
		//Logic to calculate fields width
		$widthcasses = "col-sm-7";
		if (is_int(stripos($field->style, 'large-field'))) {
			$widthcasses = "col-sm-7";
		} else if (is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date'))) {
			$widthcasses = "col-sm-5";
		} else if (is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch'))) {
			$widthcasses = "col-sm-3";
		} else if (is_int(stripos($field->style, 'tiny-field'))) {
			$widthcasses = "col-sm-2";
		}
		$widthcasses .= " col-xs-12";
		?>
		<div class="form-group <?php if (!empty($error[$name])) {
			echo "has-error";
		} ?>">
			<label class="control-label col-sm-3 col-xs-12"
				   for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>

			<div class="input-group afield <?php echo $widthcasses; ?> <?php echo($name == 'description' ? 'ml_ckeditor' : '') ?>">
				<?php if($name == 'email') { ?>
				<span class="input-group-btn">
					<a type="button" title="mailto" class="btn btn-info" href="mailto:<?php echo $field->value; ?>">
					<i class="fa fa-envelope fa-fw"></i>
					</a>
				</span>
				<?php } ?>
				<?php echo $field; ?>
			</div>
			<?php if (!empty($error[$name])) { ?>
				<span class="help-block field_err"><?php echo $error[$name]; ?></span>
			<?php } ?>
		</div>
		<?php } ?><!-- <div class="fieldset"> -->
	</div>
<?php } ?>

	<div class="panel-footer col-xs-12">
		<div class="text-center">
			<button class="btn btn-primary lock-on-click">
			<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<button class="btn btn-default" type="reset">
                <i class="fa fa-sync fa-fw"></i> <?php echo $button_reset; ?>
			</button>
			<?php if($form['delete']){?>
				<a class="btn btn-danger" data-confirmation="delete"
				   href="<?php echo $form['delete']->href; ?>">
					<i class="fa fa-trash"></i> <?php echo $form['delete']->text; ?>
				</a>
			<?php } ?>
		</div>
	</div>
	</form>
</div>

<script type="application/javascript">
	function sendPasswordReset(){
		$.ajax({
			url: '<?php echo $reset_password->href; ?>',
			type:'POST',
            success: function (data) {
                if (data.result === true) {
                    success_alert(data.success);
                }
            }
        });
    }
</script>

<?php
//load quick view port modal
echo $this->html->buildElement(
    [
        'type'        => 'modal',
        'id'          => 'viewport_modal',
        'modal_type'  => 'lg',
        'data_source' => 'ajax',
        'js_onload'   => "
        var url = $(this).data('bs.modal').options.fullmodeHref;
        $('#viewport_modal .modal-header a.btn').attr('href',url);
        ",
        'js_onclose'  => "$('#" . $data['table_id'] . "').trigger('reloadGrid',[{current:true}]);"
    ]
); ?>