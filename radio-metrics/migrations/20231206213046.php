<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20231206213046 extends AbstractMigration
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
        $this->table('tracks')
            ->addColumn('duration', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('path', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('mpd_track_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('artist', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => true])
            ->update();
    }
}
