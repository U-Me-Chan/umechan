<?php

namespace IH;

use IH\Utils\DirectoryIterator;
use InvalidArgumentException;
use Medoo\Medoo;
use SplFileInfo;

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

        $files = array_map(function (SplFileInfo $spl_file) {
            $file_name = $spl_file->getBasename();

            if (substr($file_name, -3) == 'mp4' || substr($file_name, -4) == 'webm' || substr($file_name, -3) == 'mov') {
                $thumbnail = $this->static_url . '/thumb.' . $file_name . '.' . 'jpeg';
            } else {
                $thumbnail = $this->static_url . '/thumb.' . $file_name;
            }

            $original = $this->static_url . '/' . $file_name;

            $post_ids = $this->db->select('posts', 'id', [ // @phpstan-ignore arguments.count,argument.type,argument.type
                'message[~]' => '%' . $file_name . '%'
            ]);

            if (!$post_ids) { // @phpstan-ignore booleanNot.alwaysTrue
                $post_ids = [];
            }

            return new File(
                $file_name,
                $original,
                $thumbnail,
                $post_ids

            );
        }, $files);

        return new FileCollection($files, $count, $filters);
    }
}
