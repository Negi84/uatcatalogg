<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * https://www.abantecart.com
 *
 * Copyright (c) 2011-2023  Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <https://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to https://www.abantecart.com for more information.
 */

if ($type == 'number' && in_array($value, ['', 0, null]) && !str_contains($attr, 'min=')) {
    $attr .= ' min="0" ';
}
if ($type == 'password' && $has_value == 'Y' && $required) { ?>
    <div class="input-group-addon confirm_default" id="<?php echo $id ?>_confirm_default">***********</div>
<?php } ?>
    <input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
           class="form-control atext <?php echo $style; ?>" value="<?php echo $value ?>"
           data-orgvalue="<?php echo $value ?>" <?php echo $attr; ?> placeholder="<?php echo $placeholder ?>"/>

<?php if ($required || $multilingual || !empty ($help_url)) { ?>
    <span class="input-group-addon">
	<?php if ($required == 'Y') { ?>
        <span class="required">*</span>
    <?php } ?>

	<?php if ( $multilingual ) { ?>
        <span class="multilingual"><i class="fa fa-flag"></i></span>
    <?php } ?>

	<?php if ( !empty ($help_url) ) { ?>
        <span class="help_element"><a href="<?php echo $help_url; ?>" target="new"><i
                        class="fa fa-question-circle fa-lg"></i></a></span>
	<?php } ?>	

	</span>

<?php } ?>