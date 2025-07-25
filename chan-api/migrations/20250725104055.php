<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20250725104055 extends AbstractMigration
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
        $this->table('boards')
            ->addColumn('new_posts_count', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('threads_count', 'integer', ['signed' => false, 'default' => 0])
            ->update();
    }
}
