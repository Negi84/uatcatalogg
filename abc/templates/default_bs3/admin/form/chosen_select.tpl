<?php //NOTE: For multivalue, need to pass attribute multiple="multiple" ?>
    <select id="<?php echo $id ?>"
            name="<?php echo $name ?>"
            data-placeholder="<?php echo $placeholder; ?>"
            class="chosen-select form-control aselect <?php echo $style ?: ''; ?>"
            style="display: none;" <?php echo $attr; ?>>
        <?php
        if (is_array($options)) {
            foreach ($options as $v => $text) {
                $check_id = preg_replace('/[^a-zA-Z0-9_]/', '', $id . $v);
                //special case for chosen
                if (is_array($text)) {
                    $text = $text['name'];
                }
                ?>
                <option id="<?php echo $check_id ?>"
                        value="<?php echo $v ?>" <?php echo(in_array($v, $value) ? ' selected="selected" ' : '') ?>
                        data-orgvalue="<?php echo(in_array($v, $value) ? 'true' : 'false') ?>">
                    <?php echo $text ?>
                </option>
                <?php
            }
    } ?>
</select>

<?php if ($required == 'Y' || !empty ($help_url)) { ?>
	<span class="input-group-addon">
	<?php if ($required == 'Y') { ?>
        <span class="required">*</span>
    <?php }
    if (!empty ($help_url)) { ?>
        <span class="help_element">
                <a href="<?php echo $help_url; ?>" target="new">
                    <i class="fa fa-question-circle fa-lg"></i>
                </a>
            </span>
    <?php } ?>
	</span>
<?php } ?>
<script type="text/javascript">
    <?php
    //for chosen we populate HTML into options
    if (str_contains($style, 'chosen')) { ?>
    $(document).ready(function () {
        let elm = $("#<?php echo $id ?>");
        <?php
        if(is_array($options)){
        foreach ( $options as $v => $text ) {
        if (is_array($text)) {
        $check_id = preg_replace('/[^a-zA-Z0-9_]/', '', $id . $v); ?>
        $('#<?php echo $check_id ?>').html(<?php abc_js_echo($text['image']); ?>);
        $('#<?php echo $check_id ?>').append('<span class="hide_text"> <?php abc_js_echo($text['name']); ?></span>');
        <?php           }
        }
        } ?>
        elm.chosen(
            {
                'width': '100%',
                'white-space': 'nowrap',
                'max_selected_options': <?php echo $extra['max_selected_options'] ?: 'null'; ?>,
                'search_contains': true
            }
        );
        <?php if( $extra['max_selected_options']){ ?>
        elm.chosen().change(
            function () {
                let currVal = $(this).val();
                currVal = currVal === null ? [] : currVal;
                if (currVal.length === <?php echo (int)$extra['max_selected_options']; ?>) {
                    $('#<?php echo $id?>_chosen li.search-field').hide();
                } else {
                        $('#<?php echo $id?>_chosen li.search-field').show();
                    }
                });
        <?php } ?>
    });
    <?php }

    if ($ajax_url) {  //for chosen we populate data from ajax  ?>
    <!-- Ajax Product Sector with Chosen (Multivalue lookup element) -->
    $(document).ready(function () {
        $("#<?php echo $id ?>").ajaxChosen({
            type: 'POST',
            url: '<?php echo $ajax_url; ?>',
            dataType: 'json',
            jsonTermKey: "term",
            data: {
                'exclude': <?php echo (int)$extra['exclude'] ?: '$("#' . $id . '").chosen().val()' ?>,
                'filter': '<?php echo $filter_params; ?>'
            },
            keepTypingMsg: <?php abc_js_echo('<span class="green">' . $text_continue_typing . '</span>'); ?>,
            lookingForMsg: <?php abc_js_echo('<i class="fa fa-spinner fa-spin"></i>&nbsp;' . $text_looking_for); ?>
        }, function (data) {
            var results = [];
            $.each(data, function (i, val) {
                var html = '', css = '';
                if (val.hasOwnProperty('image')) {
                    html += val.image;
                    css = 'hide_text';
                }
                html += '<span class="' + css + '"> ' + val.name;
                if (val.meta) {
						html += '&nbsp;(' + val.meta + ')';
					}
					html += '</span>';
                    <?php // process custom html-attributes for "option"-tag
                    $oa = '';

                    if ($option_attr) {
                        $i = 0;
                        $k = array();
                        foreach ($option_attr as $attr_name) {
                            $k[] = "'".$i."': {name: '".$attr_name."', value: val.".$attr_name." }";
                            $i++;
                        }
                        $oa = implode(', ', $k);
                    }
                    ?>
					results.push({value: val.id, text: html, option_attr: {<?php echo $oa;?>}});
				});
				return results;
			});
		});
<?php } ?>
</script>
