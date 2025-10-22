<?php

namespace PK\Posts;

use OpenApi\Attributes as OA;
use PK\Boards\Board;
use PK\Posts\Post\ImageParser;
use PK\Posts\Post\PasswordHash;
use PK\Posts\Post\StickyFlag;
use PK\Posts\Post\VerifyFlag;
use PK\Posts\Post\VideoParser;
use PK\Posts\Post\YoutubeParser;
use PK\Posts\Post\Id;
use PK\Posts\OpenApi\Schemas\MediaList;

#[OA\Schema]
class Post implements \JsonSerializable
{
    #[OA\Property(description: 'Превышен ли лимит ответов?')]
    public bool $bump_limit_reached {
        get => $this->is_thread && $this->replies_count > 500 ? true : false;
    }

    public bool $is_thread {
        get => $this->parent_id == null ? true : false;
    }

    #[OA\Property(description: 'Идентификатор доски')]
    public int $board_id {
        get => $this->board_id ?? $this->board->id;
    }

    #[OA\Property(format: 'Y-m-d G:i:s', description: 'Дата и время')]
    public string $datetime {
        get => date('Y-m-d G:i:s', $this->timestamp + 60 * (60 * 4));
    }

    public static function draft(
        Board $board,
        ?int $parent_id,
        string $message,
        string $poster = 'Anonymous',
        string $subject = ''
    ): self {
        return new self(
            Id::generate(),
            $poster,
            $subject,
            $message,
            time(),
            $board,
            $parent_id,
            time(),
            PasswordHash::generate(),
            is_draft: true
        );
    }

    public static function fromArray(array $state): self
    {
        return new self(
            Id::fromInt($state['id']),
            $state['poster'],
            $state['subject'],
            $state['message'],
            $state['timestamp'],
            Board::fromArray($state['board_data']),
            $state['parent_id'],
            $state['updated_at'],
            PasswordHash::fromString($state['password']),
            isset($state['replies']) && !empty($state['replies']) ? $state['replies'] : [],
            isset($state['replies_count']) ? $state['replies_count'] : 0,
            VerifyFlag::from($state['is_verify']) == VerifyFlag::yes ? true : false,
            StickyFlag::from($state['is_sticky']) == StickyFlag::yes ? true: false,
            is_draft: false
        );
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);

        list($media, $truncated_message) = $this->getMediaAndTruncatedMessage();

        $data['media']             = $media;
        $data['truncated_message'] = $truncated_message;
        $data['id']                = $this->id->value;

        unset(
            $data['password'],
            $data['is_draft'],
            $data['is_thread']
        );

        return $data;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);

        $data['board_id']  = $this->board->id;
        $data['is_verify'] = $this->is_verify == true ? 'yes' : 'no';
        $data['is_sticky'] = $this->is_sticky == true ? 'yes' : 'no';
        $data['password']  = $this->password->toString();
        $data['id']        = $this->id->value;

        unset(
            $data['board'],
            $data['replies'],
            $data['replies_count'],
            $data['bump_limit_reached'],
            $data['is_draft'],
            $data['datetime'],
            $data['truncated_message'],
            $data['media'],
            $data['is_thread']
        );

        return $data;
    }

    private function __construct(
        #[OA\Property(description: 'Идентификатор')]
        public Id $id,
        #[OA\Property(description: 'Автор')]
        public string $poster,
        #[OA\Property(description: 'Тема')]
        public string $subject,
        #[OA\Property(description: 'Сообщение')]
        public string $message,
        #[OA\Property(description: 'Метка времени в unixtime')]
        public int $timestamp,
        #[OA\Property(ref: Board::class)]
        public Board $board,
        #[OA\Property(description: 'Идентификатор родительского поста')]
        public ?int $parent_id,
        #[OA\Property(description: 'Метка времени в unixtime последнего обновления поста')]
        public int $updated_at,
        public PasswordHash $password,
        #[OA\Property(items: new OA\Items(ref: Post::class), description: 'Список ответов на нить')]
        public array $replies = [],
        #[OA\Property(description: 'Количество ответов')]
        public int $replies_count = 0,
        #[OA\Property(description: 'Является ли имя автора верифицированным?')]
        public bool $is_verify = false,
        #[OA\Property(description: 'Является ли нить прилипчивой?')]
        public bool $is_sticky = false,
        #[OA\Property(description: 'Очищенное от медиа сообщение')]
        private string $truncated_message = '',
        #[OA\Property(description: 'Список медиа', ref: MediaList::class, nullable: false)]
        private ?object $media = null,
        public bool $is_draft = true
    ) {
    }

    public function getMediaAndTruncatedMessage(): array
    {
        $message = $this->message;
        $media = [];

        list($images, $message)   = ImageParser::parse($message);
        list($youtubes, $message) = YoutubeParser::parse($message);
        list($videos, $message)   = VideoParser::parse($message);

        $media = [
            'videos'   => array_values($videos),
            'images'   => array_values($images),
            'youtubes' => $youtubes,
        ];

        $message = htmlspecialchars($message, ENT_HTML5);

        return [$media, $message];
    }
}
