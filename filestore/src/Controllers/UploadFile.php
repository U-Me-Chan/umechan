<?php

namespace IH\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Rweb\IController;
use IH\Http\Response;
use IH\DTO\Error as DTOError;
use IH\DTO\UploadedFile as DTOUploadedFile;
use IH\Exceptions\FileUnsupportedMimetype;
use IH\Exceptions\FileNotUploaded;
use IH\Services\FileUploader;
use IH\Services\TelegramSender;
use IH\Services\ThumbnailCreator;

class UploadFile implements IController
{
    public function __construct(
        private string $static_url,
        private ThumbnailCreator $thumbnail_creator,
        private TelegramSender $telegram_sender
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        if ($req->files->all() == null) {
            return new Response(new DTOError('no file'), Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile */
        $file = current($req->files->all());

        try {
            $uploaded_file = new FileUploader($file);
        } catch (FileUnsupportedMimetype) {
            return new Response(new DTOError('unsupported mimetype'), Response::HTTP_BAD_REQUEST);
        } catch (FileNotUploaded) {
            return new Response(new DTOError('file not uploaded'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        list($filename, $thumbname) = $this->thumbnail_creator->execute($uploaded_file);

        $data = new DTOUploadedFile("{$this->static_url}/{$filename}", "{$this->static_url}/{$thumbname}");

        $this->telegram_sender->send("Загружен новый файл: {$this->static_url}/{$filename}");

        return new Response($data);
    }
}
