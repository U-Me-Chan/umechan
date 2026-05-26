<?php

namespace IH;

use InvalidArgumentException;
use SplFileInfo;
use Medoo\Medoo;
use IH\Utils\DirectoryIterator;

class FileRepository
{
    public function __construct(
        private Medoo $db,
        private DirectoryIterator $directory_iterator,
        private string $static_url
    ) {
    }

    public function findMany(array $filters = []): FileCollection
    {
        $offset = isset($filters['offset']) ? $filters['offset'] : 0;
        $limit  = isset($filters['limit']) ? $filters['limit'] : 20;

        if ($limit > 20) {
            throw new InvalidArgumentException('Количество файлов в ответе не должно превышать 20');
        }

        $files = $this->directory_iterator->getSlice($offset, $limit);
        $count = $this->directory_iterator->count();

        if (sizeof($files) == 0) {
            return new FileCollection([], $count, $filters);
        }

        /** @var string[] */
        $files_names = array_map(fn(SplFileInfo $file) => $file->getBasename(), $files);

        // TODO: однажды весь этот CTE следует заменить хранением ссылок на посты в хранилище
        $cte_first_query = sprintf("WITH search_names AS( SELECT %s AS name ", $this->db->quote(array_shift($files_names)));

        if (sizeof($files_names) !== 1) {
            foreach ($files_names as $name) {
                $cte_first_query .= sprintf("UNION ALL SELECT %s ", $this->db->quote($name));
            }
        }

        $query = $cte_first_query . "), matched_posts AS (
        SELECT
            search_names.name AS file_name,
            posts.id AS post_id
        FROM search_names
        LEFT JOIN posts ON posts.message LIKE CONCAT('%', search_names.name, '%')
        )
        SELECT
            file_name,
            GROUP_CONCAT(post_id ORDER BY post_id SEPARATOR ',') AS post_ids
        FROM matched_posts
        GROUP BY file_name;";

        $result = $this->db->query($query)->fetchAll($this->db->pdo::FETCH_ASSOC);

        $files = array_map(function (array $file_data) {
            $file_name = $file_data['file_name'];

            // TODO: однажды всё это должно переехать в какое-то хранилище
            if (
                substr($file_name, -3) == 'mp4' ||
                substr($file_name, -4) == 'webm' ||
                substr($file_name, -3) == 'mov' ||
                substr($file_name, -3) == 'gif'
            ) {
                $thumbnail = $this->static_url . '/thumb.' . $file_name . '.' . 'jpeg';
            } else {
                $thumbnail = $this->static_url . '/thumb.' . $file_name;
            }

            $original = $this->static_url . '/' . $file_name;

            $post_ids = $file_data['post_ids'] === null ? [] : explode(',', $file_data['post_ids']);

            return new File(
                $file_name,
                $original,
                $thumbnail,
                $post_ids
            );
        }, $result);

        return new FileCollection($files, $count, $filters);
    }

    public function deleteFile(string $filename): void
    {
        $filepath  = $filename;
        $thumbpath = 'thumb.' . $filename;

        if (is_file($filepath)) {
            unlink($filepath);
        }

        if (is_file($thumbpath)) {
            unlink($thumbpath);
        }
    }
}
