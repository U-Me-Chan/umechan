<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20251013201356 extends AbstractMigration
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
        if (!$this->hasTable('posts')) {
            $this->table('posts')
                ->addColumn('poster', 'string', ['limit' => 100])
                ->addColumn('subject', 'string', ['limit' => 100])
                ->addColumn('message', 'string', ['limit' => 10000])
                ->addColumn('timestamp', 'integer', ['signed' => false, 'limit' => 10])
                ->addColumn('board_id', 'integer')
                ->addColumn('parent_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['signed' => false, 'limit' => 10])
                ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('is_verify', 'enum', ['values' => ['no', 'yes']])
                ->addColumn('is_sticky', 'enum', ['values' => ['yes', 'no'], 'default' => 'no'])
                ->addForeignKey('board_id', 'boards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addIndex(['parent_id'], ['name' => 'parent_id_idx'])
                ->create();
        }
    }
}
