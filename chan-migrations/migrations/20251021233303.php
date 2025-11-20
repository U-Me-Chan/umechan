<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20251021233303 extends AbstractMigration
{
    public function up(): void
    {
        $this->table('posts')
            ->changeColumn('parent_id', 'biginteger', ['signed' => false, 'null' => true])
            ->changeColumn('id', 'biginteger', ['identity' => false, 'null' => false, 'signed' => false])
            ->update();
    }

    public function down(): void
    {
        $this->table('posts')
            ->changeColumn('parent_id', 'integer', ['signed' => false, 'null' => true])
            ->changeColumn('id', 'integer', ['signed' => false, 'null' => true])
            ->update();
    }
}
