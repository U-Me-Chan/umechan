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
        if (!$this->hasTable('artists')) {
            $this->table('artists', ['id' => false, 'primary_key' => ['id']])
                 ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                 ->addColumn('artist', 'string')
                 ->addColumn('first_playing', 'integer')
                 ->addColumn('last_playing', 'integer')
                 ->addColumn('play_count', 'integer')
                 ->addColumn('estimate', 'integer')
                 ->create();
        }

        if (!$this->hasTable('tracks')) {
            $this->table('tracks', ['id' => false, 'primary_key' => ['id']])
                 ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('track', 'string')
                ->addColumn('first_playing', 'integer')
                ->addColumn('last_playing', 'integer')
                ->addColumn('play_count', 'integer')
                ->addColumn('estimate', 'integer')
                ->create();
        }

        if (!$this->hasTable('playlists')) {
            $this->table('playlists', ['id' => false, 'primary_key' => ['id']])
                 ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                 ->addColumn('playlist', 'string')
                 ->addColumn('first_playing', 'integer')
                 ->addColumn('last_playing', 'integer')
                 ->addColumn('play_count', 'integer')
                 ->addColumn('estimate', 'integer')
                 ->create();
        }

        if (!$this->hasTable('records')) {
            $this->table('records')
                 ->addColumn('artist_id', 'integer', ['null' => false, 'signed' => false])
                 ->addForeignKey('artist_id', 'artists', 'id')
                 ->addColumn('track_id', 'integer', ['null' => false, 'signed' => false])
                 ->addForeignKey('track_id', 'tracks', 'id')
                 ->addColumn('playlist_id', 'integer', ['null' => false, 'signed' => false])
                 ->addForeignKey('playlist_id', 'playlists', 'id')
                 ->addColumn('listeners', 'integer')
                 ->addColumn('timestamp', 'integer')
                 ->create();
        }
    }
}
