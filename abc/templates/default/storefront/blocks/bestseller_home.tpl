<?php if($products){?>
<section id="bestseller" class="row mt20">
<h4 class="hidden">&nbsp;</h4>
	<div class="container-fluid">
<?php   if ( $block_framed ) { ?>
		<div class="block_frame block_frame_<?php echo $block_details['block_txt_id'];?>"
			 id="block_frame_<?php echo $block_details['block_txt_id'].'_'.$block_details['instance_id'] ?>">
			<h1 class="heading1">
                <a href="<?php echo $this->html->getSecureUrl('product/bestseller')?>">
                    <span class="maintext"><?php echo $heading_title; ?></span>
                    <span class="subtext"><?php echo $heading_subtitle; ?></span>
                </a>
            </h1>
        <?php }
        include($this->templateResource('blocks/product_list.tpl', 'file'));
if ($block_framed) { ?>
		</div>
<?php } ?>
	</div>
</section>
<?php } ?>