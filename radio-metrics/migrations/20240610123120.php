<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20240610123120 extends AbstractMigration
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
            ->removeIndex('last_playing')
            ->removeIndex('estimate')
            ->removeIndex('hash')
            ->removeIndex('mpd_track_id')
            ->update();

        $this->table('records')
            ->removeIndex('track_id')
            ->removeIndex('listeners')
            ->removeIndex('timestamp')
            ->update();
    }
}
