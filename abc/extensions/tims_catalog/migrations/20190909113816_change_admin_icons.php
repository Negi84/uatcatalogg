<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class ChangeAdminIcons extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {

        $builder = $this->getQueryBuilder();
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('resource_descriptions');
        $table = $this->table('resource_descriptions');
        if ($table->exists()) {
            //Icon Sale
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-flag"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-flag-o"></i>&nbsp;'])
                ->execute();
            //Icon Design
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-file-alt"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-file-text"></i>&nbsp;'])
                ->execute();
            //Icon Reports
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-chart-bar"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-bar-chart-o"></i>&nbsp;'])
                ->execute();
            //Icon Manufacturers
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-bookmark"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-bookmark-o"></i>&nbsp;'])
                ->execute();
            //Icon Review
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-comment"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-comment-o"></i>&nbsp;'])
                ->execute();
            //Icon Attributes
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-ticket-alt"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-ticket"></i>&nbsp;'])
                ->execute();
            //Icon Send Mail
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-envelope"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-envelope-o"></i>&nbsp;'])
                ->execute();
            //Icon Templates
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-copy"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-files-o"></i>&nbsp;'])
                ->execute();
            //Icon Banner Manager
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-image"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-picture-o"></i>&nbsp;'])
                ->execute();
            //Icon Total
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sign-in-alt"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sign-in"></i>&nbsp;'])
                ->execute();
            //Icon Get Extensions
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-cloud-download-alt"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-cloud-download"></i>&nbsp;'])
                ->execute();
            //Icon Updater
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sync"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-refresh"></i>&nbsp;'])
                ->execute();
            //Icon Messages
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-comments"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-weixin"></i>&nbsp;'])
                ->execute();
            //Icon Logs
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-save"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-floppy-o"></i>&nbsp;'])
                ->execute();
            //Icon All Settings
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sliders-h"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sliders"></i>&nbsp;'])
                ->execute();
            //Icon General
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-folder"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-folder-o"></i>&nbsp;'])
                ->execute();
            //Icon Settings appearance
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-edit"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-pencil-square-o"></i>&nbsp;'])
                ->execute();
            //Icon Users groups
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-code-branch"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-code-fork"></i>&nbsp;'])
                ->execute();
            //Icon Languages
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sort-alpha-up"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sort-alpha-asc"></i>&nbsp;'])
                ->execute();
            //Icon Currencies
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-money-bill-alt"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-money"></i>&nbsp;'])
                ->execute();
            //Icon Order statuses
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sort-amount-up"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sort-amount-asc"></i>&nbsp;'])
                ->execute();
            //Icon Zones
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-thumbtack"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-thumb-tack"></i>&nbsp;'])
                ->execute();
            //Icon Length classes
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-arrows-alt-h"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-arrows-h"></i>&nbsp;'])
                ->execute();
            //Icon Backup
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-server"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-jsfiddle"></i>&nbsp;'])
                ->execute();
            //Icon Tims ICustom Import
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-file-excel"></i>&nbsp;')
                ->where([
                    'resource_code' => '<i class="fa fa-file-excel-o"></i>&nbsp;',
                    'name'          => 'Menu Icon Tims Import Upload',
                ])
                ->execute();
            //Icon Viewed
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sort-amount-down"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sort-amount-desc"></i>&nbsp;'])
                ->execute();
            //Icon Purchased
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-file"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-file-text-o"></i>&nbsp;'])
                ->execute();
            //Icon Homepage
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-external-link-alt"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-external-link"></i>&nbsp;'])
                ->execute();
        }
    }

    public function down()
    {
        $builder = $this->getQueryBuilder();
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('resource_descriptions');
        $table = $this->table('resource_descriptions');
        if ($table->exists()) {
            //Icon Sale
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-flag-o"></i>&nbsp;')
                ->where([
                    'resource_code' => '<i class="fa fa-file-excel-o"></i>&nbsp;',
                ])
                ->execute();
            //Icon Design
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-file-text"></i>&nbsp;')
                ->where([
                    'resource_code' => '<i class="fa fa-file-alt"></i>&nbsp;',
                ])
                ->execute();
            //Icon Reports
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-bar-chart-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-chart-bar"></i>&nbsp;'])
                ->execute();
            //Icon Manufacturers
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-bookmark-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-bookmark"></i>&nbsp;'])
                ->execute();
            //Icon Review
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-comment-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-comment"></i>&nbsp;'])
                ->execute();
            //Icon Attributes
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-ticket"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-ticket-alt"></i>&nbsp;'])
                ->execute();
            //Icon Send Mail
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-envelope-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-envelope"></i>&nbsp;'])
                ->execute();
            //Icon Templates
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-files-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-copy"></i>&nbsp;'])
                ->execute();
            //Icon Banner Manager
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-picture-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-image"></i>&nbsp;'])
                ->execute();
            //Icon Total
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sign-in"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sign-in-alt"></i>&nbsp;'])
                ->execute();
            //Icon Get Extensions
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-cloud-download"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-cloud-download-alt"></i>&nbsp;'])
                ->execute();
            //Icon Updater
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-refresh"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sync"></i>&nbsp;'])
                ->execute();
            //Icon Messages
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-weixin"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-comments"></i>&nbsp;'])
                ->execute();
            //Icon Logs
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-floppy-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-save"></i>&nbsp;'])
                ->execute();
            //Icon All Settings
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sliders"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sliders-h"></i>&nbsp;'])
                ->execute();
            //Icon General
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-folder-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-folder"></i>&nbsp;'])
                ->execute();
            //Icon Settings appearance
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-pencil-square-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-edit"></i>&nbsp;'])
                ->execute();
            //Icon Users groups
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-code-fork"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-code-branch"></i>&nbsp;'])
                ->execute();
            //Icon Languages
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sort-alpha-asc"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sort-alpha-up"></i>&nbsp;'])
                ->execute();
            //Icon Currencies
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-money"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-money-bill-alt"></i>&nbsp;'])
                ->execute();
            //Icon Order statuses
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sort-amount-asc"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sort-amount-up"></i>&nbsp;'])
                ->execute();
            //Icon Zones
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-thumb-tack"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-thumbtack"></i>&nbsp;'])
                ->execute();
            //Icon Length classes
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-arrows-h"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-arrows-alt-h"></i>&nbsp;'])
                ->execute();
            //Icon Backup
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-jsfiddle"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-server"></i>&nbsp;'])
                ->execute();
            //Icon Backup
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-sort-amount-desc"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-sort-amount-down"></i>&nbsp;'])
                ->execute();
            //Icon Tims ICustom Import
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-file-excel-o"></i>&nbsp;')
                ->where([
                    'resource_code' => '<i class="fa fa-file-excel"></i>&nbsp;',
                    'name'          => 'Menu Icon Tims Import Upload',
                ])
                ->execute();
            //Icon Purchased
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-file-text-o"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-file"></i>&nbsp;'])
                ->execute();
            //Icon Homepage
            $builder = $this->getQueryBuilder();
            $builder->update($full_table_name)
                ->set('resource_code', '<i class="fa fa-external-link"></i>&nbsp;')
                ->where(['resource_code' => '<i class="fa fa-external-link-alt"></i>&nbsp;'])
                ->execute();
        }

    }
}