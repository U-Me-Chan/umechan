<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20230127005453 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if (!$this->hasTable('tracks')) {
            $this->table('tracks', ['id' => false, 'primary_key' => ['id']])
                 ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                 ->addColumn('track', 'string')
                 ->addIndex(['track'])
                 ->addColumn('first_playing', 'integer')
                 ->addColumn('last_playing', 'integer')
                 ->addIndex(['last_playing'])
                 ->addColumn('play_count', 'integer')
                 ->addColumn('estimate', 'integer')
                 ->addIndex(['estimate'])
                ->create();
        }

        if (!$this->hasTable('records')) {
            $this->table('records')
                 ->addColumn('track_id', 'integer', ['null' => false, 'signed' => false])
                 ->addForeignKey('track_id', 'tracks', 'id')
                 ->addIndex(['track_id'])
                 ->addColumn('listeners', 'integer')
                 ->addIndex(['listeners'])
                 ->addColumn('timestamp', 'integer')
                 ->addIndex(['timestamp'])
                 ->create();
        }
    }
}
