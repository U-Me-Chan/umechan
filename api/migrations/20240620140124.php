<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20240620140124 extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('events')) {
            $this->table('events')
                ->addColumn('event_type', 'string', ['limit' => 100])
                ->addColumn('timestamp', 'integer')
                ->addColumn('post_id', 'integer', ['null' => true])
                ->addColumn('board_id', 'integer', ['null' => true])
                ->save();
        }
    }

    public function down()
    {
        if ($this->hasTable('events')) {
            $this->table('events')->drop()->save();
        }
    }
}
