<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20230225002357 extends AbstractMigration
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
        if ($this->hasTable('tracks')) {
            $this->execute('UPDATE tracks SET estimate = 0');
        }

        if ($this->hasTable('artists')) {
            $this->execute('UPDATE artists SET estimate = 0');
        }

        if ($this->hasTable('playlists')) {
            $this->execute('UPDATE playlists SET estimate = 0');
        }
    }
}
