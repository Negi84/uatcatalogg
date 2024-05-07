<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class CreateNewAuditLogTables extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());

        $full_table_name = $tableAdapter->getAdapterTableName('audit_event_descriptions');
        $this->execute("DROP TABLE IF EXISTS {$full_table_name};
                            CREATE TABLE {$full_table_name} (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `audit_event_id` int(11) NOT NULL,
                              `auditable_model_id` int(11) NOT NULL,
                              `auditable_id` int(11) NOT NULL,
                              `field_name` varchar(128) NOT NULL,
                              `old_value` text,
                              `new_value` text,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $full_table_name = $tableAdapter->getAdapterTableName('audit_events');
        $this->execute("DROP TABLE IF EXISTS {$full_table_name};
                            CREATE TABLE {$full_table_name} (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `request_id` varchar(128) NOT NULL,
                              `audit_session_id` int(11) NOT NULL,
                              `audit_user_id` int(11) NOT NULL,
                              `audit_alias_id` int(11) DEFAULT NULL,
                              `event_type_id` int(11) NOT NULL,
                              `main_auditable_model_id` int(11) NOT NULL,
                              `main_auditable_id` int(11) DEFAULT NULL,
                              `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `request_id_UNIQUE` (`request_id`,`audit_user_id`,`event_type_id`,`main_auditable_model_id`,`main_auditable_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $full_table_name = $tableAdapter->getAdapterTableName('audit_models');
        $this->execute("DROP TABLE IF EXISTS {$full_table_name};
                            CREATE TABLE {$full_table_name} (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `name` varchar(128) NOT NULL,
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `name_UNIQUE` (`name`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $full_table_name = $tableAdapter->getAdapterTableName('audit_sessions');
        $this->execute("DROP TABLE IF EXISTS {$full_table_name};
                            CREATE TABLE {$full_table_name} (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `session_id` varchar(255) NOT NULL,
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `uniq` (`session_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $full_table_name = $tableAdapter->getAdapterTableName('audit_users');
        $this->execute("DROP TABLE IF EXISTS {$full_table_name};
                           CREATE TABLE {$full_table_name} (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `user_type_id` int(11) NOT NULL,
                              `name` varchar(255) NOT NULL,
                              `user_id` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `name_userid_indx` (`id`,`name`,`user_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    }

    public function down()
    {
        $tables = ['audit_event_descriptions', 'audit_events', 'audit_models', 'audit_sessions', 'audit_users'];

        foreach ($tables as $tableName) {
            $table = $this->table($tableName);
            if ($table->exists()) {
                $table->drop();
            }
        }


    }
}
