<?php
/**
 * AbanteCart auto-generated migrationfile
 */

use Phinx\Migration\AbstractMigration;

class ProductOptionsFksFixes extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {

        $this->execute("SET SQL_MODE = '';");
        $prefix = 'cba_';
        try {
            $update = " ALTER TABLE `{$prefix}order_options`
                    DROP FOREIGN KEY `{$prefix}order_options_ibfk_1`;
                    ALTER TABLE `{$prefix}order_options`
                    CHANGE COLUMN `product_option_value_id` `product_option_value_id` INT(11) NULL DEFAULT '0' ;
                    ALTER TABLE `{$prefix}order_options`
                    ADD CONSTRAINT `{$prefix}order_options_ibfk_1`
                      FOREIGN KEY (`product_option_value_id`)
                      REFERENCES `{$prefix}product_option_values` (`product_option_value_id`)
                      ON DELETE SET NULL
                      ON UPDATE CASCADE; ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_option_values`
                    DROP FOREIGN KEY `{$prefix}product_option_values_ibfk_2`;
                    ALTER TABLE `{$prefix}product_option_values`
                    ADD CONSTRAINT `{$prefix}product_option_values_ibfk_2`
                      FOREIGN KEY (`product_id`)
                      REFERENCES `{$prefix}products` (`product_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE; ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_option_values`
                    DROP FOREIGN KEY `{$prefix}product_option_values_ibfk_1`;
                    ALTER TABLE `{$prefix}product_option_values`
                    ADD CONSTRAINT `{$prefix}product_option_values_ibfk_1`
                      FOREIGN KEY (`product_option_id`)
                      REFERENCES `{$prefix}product_options` (`product_option_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE; ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_option_value_descriptions`
                    DROP FOREIGN KEY `{$prefix}product_option_value_descriptions_ibfk_1`;
                    ALTER TABLE `{$prefix}product_option_value_descriptions`
                    ADD CONSTRAINT `{$prefix}product_option_value_descriptions_ibfk_1`
                      FOREIGN KEY (`product_id`)
                      REFERENCES `{$prefix}products` (`product_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE; ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_option_descriptions`
                    DROP FOREIGN KEY `{$prefix}product_option_descriptions_ibfk_3`;
                    ALTER TABLE `{$prefix}product_option_descriptions`
                    ADD CONSTRAINT `{$prefix}product_option_descriptions_ibfk_3`
                      FOREIGN KEY (`product_option_id`)
                      REFERENCES `{$prefix}product_options` (`product_option_id`)
                      ON DELETE CASCADE; ";
            $this->execute($update);

            $update = " DELETE FROM {$prefix}product_option_value_descriptions
                     WHERE product_option_value_id NOT IN
                            ( SELECT product_option_value_id
                              FROM {$prefix}product_option_values); ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_option_value_descriptions`
         ADD CONSTRAINT `{$prefix}product_option_value_descriptions_ibfk_3`
           FOREIGN KEY (`product_option_value_id`)
           REFERENCES `{$prefix}product_option_values` (`product_option_value_id`)
           ON DELETE CASCADE
           ON UPDATE CASCADE; ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_descriptions` 
                    DROP FOREIGN KEY `{$prefix}product_descriptions_ibfk_1`;
                    ALTER TABLE `{$prefix}product_descriptions` 
                    ADD CONSTRAINT `{$prefix}product_descriptions_ibfk_1`
                      FOREIGN KEY (`product_id`)
                      REFERENCES `{$prefix}products` (`product_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE; ";
            $this->execute($update);

            $update = " ALTER TABLE `{$prefix}product_option_descriptions` 
                    DROP FOREIGN KEY `{$prefix}product_option_descriptions_ibfk_1`;
                    ALTER TABLE `{$prefix}product_option_descriptions` 
                    ADD CONSTRAINT `{$prefix}product_option_descriptions_ibfk_1`
                      FOREIGN KEY (`product_id`)
                      REFERENCES `{$prefix}products` (`product_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE; ";
            $this->execute($update);
            $update = " ALTER TABLE `{$prefix}product_option_descriptions` 
                    DROP FOREIGN KEY `{$prefix}product_option_descriptions_ibfk_3`;
                    ALTER TABLE `{$prefix}product_option_descriptions` 
                    ADD CONSTRAINT `{$prefix}product_option_descriptions_ibfk_3`
                      FOREIGN KEY (`product_option_id`)
                      REFERENCES `{$prefix}product_options` (`product_option_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE; ";
            $this->execute($update);
            $update = " ALTER TABLE `{$prefix}product_options` 
                    DROP FOREIGN KEY `{$prefix}product_options_ibfk_1`;
                    ALTER TABLE `{$prefix}product_options` 
                    ADD CONSTRAINT `{$prefix}product_options_ibfk_1`
                      FOREIGN KEY (`product_id`)
                      REFERENCES `{$prefix}products` (`product_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE;
         ";
            $this->execute($update);

        } catch (\PDOException $e) {
            echo $e->getMessage()."\n".$update;
        }

    }

    public function down()
    {

    }
}