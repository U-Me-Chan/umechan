<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitBoardsTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('boards')) {
            $this->table('boards')
                 ->addColumn('name', 'string', [])
                 ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('boards')) {
            $this->table('boards')->drop()->save();
        }
    }
}
