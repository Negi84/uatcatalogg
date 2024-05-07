<div class="btn-group mr10">
	<button class="btn btn-info tooltips task_run <?php echo $disable_sync_button ? 'disabled' : ''; ?>"
			type="button"
			data-run-task-url="<?php
            echo $this->html->getSecureUrl('r/extension/tims_catalog/buildTask', '&products[]='.$product_id); ?>"
			data-complete-task-url="<?php echo $this->html->getSecureUrl('r/extension/tims_catalog/completeTask'); ?>"
			data-original-title="<?php
            echo($disable_sync_button ? 'No export stores selected.' : 'Export changes to remote sites'); ?>">
		<i class="fa fa-share-square"></i> Export </span>
	</button>
</div>