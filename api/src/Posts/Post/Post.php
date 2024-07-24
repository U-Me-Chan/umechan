<?php

namespace PK\Posts\Post;

use PK\Boards\Board\Board;

class Post implements \JsonSerializable
{
    public static function draft(
        Board $board,
        int|null $parent_id,
        string $message,
        Poster $poster,
        string $subject = ''
    ): self {
        return new self(
            0,
            $poster,
            $subject,
            $message,
            time(),
            $board,
            $parent_id,
            time(),
            0,
            hash('sha256', bin2hex(random_bytes(5)))
        );
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            Poster::fromArray($state),
            $state['subject'],
            $state['message'],
            $state['timestamp'],
            Board::fromArray($state['board_data']),
            $state['parent_id'],
            $state['updated_at'],
            $state['estimate'],
            $state['password'],
            !empty($state['replies']) ? $state['replies'] : [],
            isset($state['replies_count']) ? $state['replies_count'] : 0
        );
    }

    public function erase(): void
    {
        $this->subject = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
        $this->poster = Poster::draft('⬛⬛⬛⬛⬛⬛⬛⬛⬛', IsVerifyPoster::NO);
        $message = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
        $message = <<<EOT
{$message}

Данные удалены пользователем
EOT;
        $this->message = $message;
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        $data['board_id'] = $data['board']->id;

        list($media, $truncated_message) = $this->getMediaAndTruncatedMessage();

        $data['truncated_message'] = $truncated_message;
        $data['media']             = $media;
        $data['datetime']          =  date('Y-m-d G:i:s', $data['timestamp']);
        $data['is_verify']         = $this->poster->is_verify->value;
        $data['poster']            = $this->poster->poster;

        unset($data['password']);

        return $data;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);

        $data['board_id']  = $data['board']->id;
        $data['is_verify'] = $this->poster->is_verify->value;
        $data['poster']    = $this->poster->poster;

        unset($data['board'], $data['replies'], $data['replies_count']);

        return $data;
    }

    public function addReply(Post $reply): void
    {
        array_push($this->replies, $reply);
    }

    private function __construct(
        public readonly int $id,
        public Poster $poster,
        public string $subject,
        public string $message,
        public readonly int $timestamp,
        public Board $board,
        public readonly int|null $parent_id,
        public int $updated_at,
        public int $estimate,
        public readonly string $password,
        public array $replies = [],
        public int $replies_count = 0
    ) {
    }


    public function getMediaAndTruncatedMessage(): array
    {
        $message = $this->message;
        $images  = $youtubes = [];

        if (preg_match_all('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|\[\!\[\]\((?<preview>.+)\)\]\((?<link>.+)\)/mi', $message, $matches)) {
            foreach ($matches['link'] as $k => $link) {
                $images[$link] = [
                    'link' => $link,
                    'preview' => $matches['preview'][$k]
                ];
            }
        }

        $message = preg_replace('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|\[\!\[\]\((?<preview>.+)\)\]\((?<link>.+)\)/mi', '', $message);

        if (preg_match_all('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?\:\/\/[a-z0-9_\-.\/]+\.(jpe?g?|gif|png)(\?[a-z0-9=_\/\-&]+)?/mi', $message, $matches)) {
            foreach ($matches[0] as $link) {
                $images[$link] = [
                    'link' => $link,
                    'preview' => $link
                ];
            }
        }

        $message = preg_replace('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?\:\/\/[a-z0-9_\-.\/]+\.(jpe?g?|gif|png)(\?[a-z0-9=_\/\-&]+)?/mi', '',  $message);

        if (preg_match_all('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?\:\/\/pbs\.twimg\.com\/media\/[a-z0-9\?=&]+/mi', $message, $matches)) {
            foreach ($matches[0] as $link) {
                $images[$link] = [
                    'link' => $link,
                    'preview' => $link
                ];
            }
        }

        $message = preg_replace('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?\:\/\/pbs\.twimg\.com\/media\/[a-z0-9\?=&]+/mi', '', $message);

        if (preg_match_all('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?:\/\/www\.youtube\.com\/watch\?v=([0-9a-z_-]+)/mi', $message, $matches)) {
            foreach ($matches[4] as $id) {
                $youtubes[$id] = [
                    'link' => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        if (preg_match_all('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?:\/\/youtu\.be\/([0-9a-z_-]+)(\?si\=([a-z0-9-_]+))?/mi', $message, $matches)) {
            foreach ($matches[4] as $id) {
                $youtubes[$id] = [
                    'link' => "https://youtu.be/{$id}",
                    'preview' => "https://i1.ytimg.com/vi/{$id}/hqdefault.jpg"
                ];
            }
        }

        $message = preg_replace('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?:\/\/www\.youtube\.com\/watch\?v=([0-9a-z_-]+)/mi', '', $message);
        $message = preg_replace('/((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)|https?:\/\/youtu\.be\/([0-9a-z_-]+)(\?si\=([a-z0-9-_]+))?/mi', '', $message);

        $data = [
            'images' => array_values($images),
            'youtubes' => array_values($youtubes)
        ];

        return [$data, $message];
    }
}
