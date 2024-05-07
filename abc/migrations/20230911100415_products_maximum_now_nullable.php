<?php


use Phinx\Migration\AbstractMigration;

class ProductsMaximumNowNullable extends AbstractMigration
{

    public function up()
    {
        $this->execute('alter table tims_products modify maximum int null;');
    }

    public function down()
    {

    }
}