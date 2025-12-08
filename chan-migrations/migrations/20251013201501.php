<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20251013201501 extends AbstractMigration
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
        if (!$this->hasTable('passports')) {
            $this->table('passports', ['id' => false, 'primary_key' => ['name', 'hash']])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('hash', 'string', ['limit' => 255, 'null' => false])
                ->addIndex(['hash'], ['unique' => true])
                ->addIndex(['name'], ['unique' => true])
                ->create();
        }
    }
}
