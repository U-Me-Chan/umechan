<?php

namespace PK\Posts;

use OpenApi\Attributes as OA;
use PK\Boards\Board\Board;
use PK\Posts\OpenApi\Schemas\MediaList;
use PK\Posts\Post\ImageParser;
use PK\Posts\Post\PasswordHash;
use PK\Posts\Post\StickyFlag;
use PK\Posts\Post\VerifyFlag;
use PK\Posts\Post\VideoParser;
use PK\Posts\Post\YoutubeParser;

#[OA\Schema]
class Post implements \JsonSerializable
{
    #[OA\Property(description: 'Превышен ли лимит ответов?')]
    public bool $bump_limit_reached {
        get => $this->parent_id == null && $this->replies_count > 500 ? true : false;
    }

    public bool $is_draft {
        get => $this->id == 0 ? true : false;
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
            0,
            $poster,
            $subject,
            $message,
            time(),
            $board,
            $parent_id,
            time(),
            0,
            PasswordHash::generate()
        );
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['poster'],
            $state['subject'],
            $state['message'],
            $state['timestamp'],
            Board::fromArray($state['board_data']),
            $state['parent_id'],
            $state['updated_at'],
            $state['estimate'],
            PasswordHash::fromString($state['password']),
            isset($state['replies']) && !empty($state['replies']) ? $state['replies'] : [],
            isset($state['replies_count']) ? $state['replies_count'] : 0,
            VerifyFlag::from($state['is_verify']) == VerifyFlag::yes ? true : false,
            StickyFlag::from($state['is_sticky']) == StickyFlag::yes ? true: false
        );
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);

        list($media, $truncated_message) = $this->getMediaAndTruncatedMessage();

        $data['media']             = $media;
        $data['truncated_message'] = $truncated_message;

        unset(
            $data['password'],
            $data['is_draft']
        );

        return $data;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);

        $data['board_id']  = $this->board->id;
        $data['is_verify'] = $this->is_verify == true ? 'yes' : 'no';
        $data['password']  = $this->password->toString();

        unset(
            $data['board'],
            $data['replies'],
            $data['replies_count'],
            $data['is_sticky'],
            $data['bump_limit_reached'],
            $data['is_draft'],
            $data['datetime'],
            $data['truncated_message'],
            $data['media']
        );

        return $data;
    }

    private function __construct(
        #[OA\Property(description: 'Идентификатор')]
        public int $id,
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
        #[OA\Property(deprecated: true)]
        public int $estimate,
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
        private ?object $media = null
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
