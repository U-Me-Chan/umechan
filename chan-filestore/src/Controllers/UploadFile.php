<?php

namespace IH\Controllers;

use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Rweb\IController;
use IH\Http\Response;
use IH\DTO\Error as DTOError;
use IH\DTO\UploadedFile as DTOUploadedFile;
use IH\Exceptions\FileUnsupportedMimetype;
use IH\Exceptions\FileNotUploaded;
use IH\Services\Files;

class UploadFile implements IController
{
    public function __construct(
        private Files $files
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        if ($req->files->all() == null) {
            return new Response(new DTOError('no file'), Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile */
        $temp_file = current($req->files->all());

        try {
            $file = $this->files->uploadFile($temp_file);
        } catch (FileUnsupportedMimetype) {
            return new Response(new DTOError('unsupported mimetype'), Response::HTTP_BAD_REQUEST);
        } catch (FileNotUploaded) {
            return new Response(new DTOError('file not uploaded'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Throwable $e) {
            return new Response(new DTOError('internal server error: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = new DTOUploadedFile(
            $file->original,
            $file->thumbnail
        );

        return new Response($data);
    }
}
