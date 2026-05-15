<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class V20260515000910 extends AbstractMigration
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
        $datas = $this->fetchAll('select p.id, COUNT(c.id) AS count FROM posts AS p LEFT JOIN posts AS c ON p.id = c.parent_id WHERE p.parent_id IS NULL GROUP BY p.id;');

        foreach ($datas as $data) {
            list($id, $count) = $data;

            $this->execute("UPDATE posts SET replies_count = {$count} WHERE id = {$id}");
        }
    }
}
