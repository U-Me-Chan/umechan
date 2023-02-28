<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20230228103855 extends AbstractMigration
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
        $this->table('artists')
             ->addIndex(['artist'])
             ->addIndex(['estimate'])
             ->addIndex(['last_playing'])
             ->save();

        $this->table('tracks')
             ->addIndex(['track'])
             ->addIndex(['estimate'])
             ->addIndex(['last_playing'])
             ->save();

        $this->table('playlists')
             ->addIndex(['playlist'])
             ->addIndex(['estimate'])
             ->addIndex(['last_playing'])
             ->save();

        $this->table('records')
             ->addIndex(['artist_id'])
             ->addIndex(['track_id'])
             ->addIndex(['timestamp'])
             ->addIndex(['listeners'])
             ->save();
    }
}
