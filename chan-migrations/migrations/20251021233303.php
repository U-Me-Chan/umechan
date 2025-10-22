<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20251021233303 extends AbstractMigration
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
        if ($this->hasTable('posts') && $this->table('posts')->hasColumn('id')) {
            $this->table('posts')
                ->changeColumn('id', 'biginteger', ['identity' => false, 'null' => false, 'signed' => false])
                ->changeColumn('parent_id', 'biginteger', ['signed' => false, 'null' => true])
                ->update();
        }
    }
}
