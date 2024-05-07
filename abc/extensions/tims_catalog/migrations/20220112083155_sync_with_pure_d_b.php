<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class SyncWithPureDB extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('customer_notes');
        if ($table->exists()) {
            if (!$table->hasColumn('date_deleted')) {
                $table->addColumn(
                    'date_deleted',
                    'timestamp',
                    ['null' => true]
                )
                      ->save();
            }
            if (!$table->hasColumn('stage_id')) {
                $table->addColumn(
                    'stage_id',
                    'integer'
                )->save();
            }
        }

        $table = $this->table('contents');
        if ($table->exists() && !$table->hasColumn('hide_title')) {
            $table->addColumn('hide_title', 'integer', [
                'null'    => true,
                'limit'   => 1,
                'default' => 0,
                'after'   => 'status',
            ])
                ->save();
        }

        $this->execute("SET SQL_MODE = '';");
        $table = $this->table('content_descriptions');
        if($table->exists() && !$table->hasColumn('meta_keywords')) {
            $table->addColumn( 'meta_keywords', 'string', ['default' => '', 'after' => 'description'] )
                ->addColumn( 'meta_description', 'string',  ['default' => '', 'after' => 'meta_keywords'] )
                ->save();
        }
        $table2 = $this->table('content_descriptions_chnglog');
        if($table2->exists() && !$table2->hasColumn('meta_keywords')) {
            $table2->addColumn( 'meta_keywords', 'string', ['default' => '', 'after' => 'description'] )
                ->addColumn( 'meta_description', 'string',  ['default' => '', 'after' => 'meta_keywords'] )
                ->save();
        }





        // create the table
        $table = $this->table('customer_notes', ['id' => 'note_id']);
        if(!$table->exists()) {
            $table->addColumn( 'customer_id', 'integer' )
                ->addColumn( 'user_id', 'integer' )
                ->addColumn( 'note', 'text' )
                ->addColumn( 'date_added', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update'  => 'CURRENT_TIMESTAMP'])
                ->create();
        }

        $table_changelog = $this->table('customer_notes_chnglog', ['id' => false, 'primary_key' => ['revision', 'note_id']]);
        if(!$table_changelog->exists()) {
           $table_changelog->addColumn('note_id', 'integer', ['identity' => false])
                ->addColumn( 'customer_id', 'integer' )
                ->addColumn( 'user_id', 'integer' )
                ->addColumn( 'note', 'text' )
                ->addColumn( 'date_added', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update'  => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'action', 'string', ['limit' => 1, 'default' => 'I'])
                ->addColumn( 'revision', 'integer', ['limit' => 6, 'identity' => true, 'after' => 'action'])
                ->addColumn( 'revision_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'after' => 'revision'])
                ->addColumn( 'actor_type', 'integer', ['comment' => '0 - System user\n1 - Admin user\n2 - Customer', 'after' => 'revision_date'])
                ->addColumn( 'actor_id', 'integer', ['after' => 'actor_type'])
                ->addColumn( 'actor_name', 'string', ['limit' => 128, 'after' => 'actor_id'])
                ->create();

        }

        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('customer_notes');
        $key = "note_id";

        //create triggers
        $trigger = "
            DROP TRIGGER IF EXISTS tr_{$full_table_name}_insert;
            CREATE TRIGGER tr_{$full_table_name}_insert AFTER INSERT ON {$full_table_name} FOR EACH ROW
            INSERT INTO {$full_table_name}_chnglog SELECT d.*, 'I', NULL, NOW(), @GLOBAL.abc_user_type, @GLOBAL.abc_user_id, @GLOBAL.abc_user_name 
            FROM {$full_table_name} AS d WHERE d.{$key} = NEW.{$key};";
        $this->execute($trigger);

        $trigger = "
            DROP TRIGGER IF EXISTS tr_{$full_table_name}_update;
            CREATE TRIGGER tr_{$full_table_name}_update BEFORE UPDATE ON {$full_table_name} FOR EACH ROW
            INSERT INTO {$full_table_name}_chnglog SELECT d.*, 'U', NULL, NOW(), @GLOBAL.abc_user_type, @GLOBAL.abc_user_id, @GLOBAL.abc_user_name 
            FROM {$full_table_name} AS d WHERE d.{$key} = NEW.{$key};";
        $this->execute($trigger);

        $trigger = "
            DROP TRIGGER IF EXISTS tr_{$full_table_name}_delete;
            CREATE TRIGGER tr_{$full_table_name}_delete BEFORE DELETE ON {$full_table_name} FOR EACH ROW
            INSERT INTO {$full_table_name}_chnglog SELECT d.*, 'D', NULL, NOW(), @GLOBAL.abc_user_type, @GLOBAL.abc_user_id, @GLOBAL.abc_user_name 
            FROM {$full_table_name} AS d WHERE d.{$key} = OLD.{$key};";
        $this->execute($trigger);
    }

    public function down()
    {

    }
}