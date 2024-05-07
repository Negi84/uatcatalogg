<?php
/**
 * AbanteCart auto-generated migration file
 */

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\JobManager;
use abc\extensions\tims_catalog\modules\workers\ProductExport;
use Phinx\Migration\AbstractMigration;

class ProductsPointsReSyncAfterChangeOfRates extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
       /* $rows = $this->fetchAll(
            "SELECT product_id, sites, product_type, uplift_id 
            FROM tims_products 
            WHERE status = '1'"
        );
        $txt = '';
        foreach ($rows as $row) {
            $sites = unserialize($row['sites']);
            if (!$sites) {
                continue;
            }
            $types = unserialize($row['product_type']);
            $uplifts = unserialize($row['uplift_id']);
            foreach ($sites as $site) {
                $txt .= $row['product_id'].";".$site.";".$types[$site].";".$uplifts[$site]."\n";
            }
        }
        if (file_put_contents(ABC::env('DIR_SYSTEM').'export'.DS.'auto_export_products_MigrationSync', $txt)) {
            try {
                $jm = ABC::getObjectByAlias('JobManager', [Registry::getInstance()]);
                $job_id = $jm->addJob(
                    [
                        'name'          => 'MigrationProductsSync',
                        'actor_type'    => 1, //admin
                        'actor_id'      => 0,
                        'actor_name'    => 'migration',
                        'status'        => $jm::STATUS_READY,
                        'configuration' => [
                            'worker'           => [
                                'file'       =>
                                    ABC::env('DIR_APP_EXTENSIONS')
                                    .'tims_catalog'.DS
                                    .'modules'.DS
                                    .'workers'.DS
                                    .'ProductExport.php',
                                'class'      => ProductExport::class,
                                'method'     => 'export',
                                'parameters' => ['batch-id' => 'MigrationSync'],
                            ],
                            'export_list_file' => 'auto_export_products_MigrationSync',
                        ],
                    ]
                );
            } catch (\Exception $e) {
                Registry::log()->write(__CLASS__.': '.$e->getTraceAsString());
            }
        }*/
    }

    public function down()
    {
    }
}