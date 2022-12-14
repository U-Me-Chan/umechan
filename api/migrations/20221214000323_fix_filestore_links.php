<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixFilestoreLinks extends AbstractMigration
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
        $posts = $this->fetchAll("SELECT id, message FROM posts WHERE message LIKE '%filestore.scheoble.xyz%'");

        foreach ($posts as $post) {
            $message = str_replace('filestore.scheoble.xyz', 'scheoble.xyz', $post['message']);
            $message = $this->getAdapter()->getConnection()->quote($message);

            $this->execute("UPDATE `posts` SET `message` = {$message} WHERE `id` = {$post['id']}");
        }
    }
}
