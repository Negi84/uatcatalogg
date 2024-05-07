<?php

use Phinx\Migration\AbstractMigration;

class ProductsFKFixes extends AbstractMigration
{

    public function up()
    {
        try {
            $this->execute(
                'ALTER TABLE `tims_products` DROP FOREIGN KEY `tims_products_fk1`;'
            );
            $this->execute(
                'ALTER TABLE `tims_products` DROP FOREIGN KEY `tims_products_fk2`;'
            );
            $this->execute(
                'ALTER TABLE `tims_products` DROP FOREIGN KEY `tims_products_fk3`;'
            );
            $this->execute(
                'ALTER TABLE `tims_products` DROP FOREIGN KEY `tims_products_fk4`;'
            );
        } catch (Exception $e) {
        }

        $this->execute('alter table tims_products modify tax_class_id int null;');
        $this->execute(
            'update tims_products 
                set tax_class_id = NULL
                where tax_class_id NOT IN (SELECT tax_class_id FROM tims_tax_classes);'
        );

        $this->execute('alter table tims_products modify length_class_id int null;');
        $this->execute(
            'update tims_products 
                set length_class_id = NULL 
                where length_class_id NOT IN (SELECT length_class_id FROM tims_length_classes);'
        );

        $this->execute('alter table tims_products modify weight_class_id int null;');
        $this->execute(
            'update tims_products 
                set weight_class_id = NULL
                where weight_class_id NOT IN (SELECT weight_class_id FROM tims_weight_classes);'
        );

        $this->execute('alter table tims_products modify manufacturer_id int null;');
        $this->execute(
            'update tims_products 
            set manufacturer_id = NULL
            where manufacturer_id NOT IN (SELECT manufacturer_id FROM tims_manufacturers);'
        );


        $sql = "ALTER TABLE `tims_products`
                ADD CONSTRAINT `tims_products_fk1`
                  FOREIGN KEY (`manufacturer_id`)
                  REFERENCES tims_manufacturers (manufacturer_id)
            on update cascade on delete set null;";
        $this->execute($sql);

        $sql = "ALTER TABLE `tims_products`
                ADD CONSTRAINT `tims_products_fk2`
                  FOREIGN KEY (`tax_class_id`)
                  REFERENCES tims_tax_classes (tax_class_id)
            on update cascade on delete set null;";
        $this->execute($sql);

        $sql = "ALTER TABLE `tims_products`
                ADD CONSTRAINT `tims_products_fk3`
                  FOREIGN KEY (`weight_class_id`)
                  REFERENCES tims_weight_classes (weight_class_id)
            on update cascade on delete set null;";
        $this->execute($sql);

        $sql = "ALTER TABLE `tims_products`
                ADD CONSTRAINT `tims_products_fk4`
                  FOREIGN KEY (`length_class_id`)
                  REFERENCES tims_length_classes (length_class_id)
            on update cascade on delete set null;";
        $this->execute($sql);

    }

    public function down()
    {

    }
}