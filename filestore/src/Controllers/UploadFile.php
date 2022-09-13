<?php

namespace IH\Controllers;

use \Imagick;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Rweb\IController;

class UploadFile implements IController
{
    private const UPLOAD_DIR   = __DIR__ . '/../../files/';
    private const WEBROOT_PATH = 'http://filestore.scheoble.xyz/files/';

    public function __invoke(Request $req, array $vars = []): Response
    {
        if ($req->files->all() == null) {
            return new Response(
                json_encode(['error' => 'no file']),
                Response::HTTP_BAD_REQUEST,
                $this->getResponseHeaders()
            );
        }

        /** @var UploadedFile */
        $file = current($req->files->all());

        if (!$file->isValid()) {
            return new Response(
                json_encode(['error' => 'upload error', 'code' => $file->getError()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getResponseHeaders()
            );
        }

        if (!$this->checkMimetype($file->getClientMimeType())) {
            return new Response(
                json_encode(['error' => 'unsupported mimetype']),
                Response::HTTP_BAD_REQUEST,
                $this->getResponseHeaders()
            );
        }

        $filename = sprintf('%s.%s', uniqid(), $file->getClientOriginalExtension());
        $filepath = sprintf('%s%s', self::UPLOAD_DIR, $filename);

        try {
            $file->move(self::UPLOAD_DIR, $filename);
        } catch (FileException $e) {
            return new Response(
                json_encode(['error' => 'save error']),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getResponseHeaders()
            );
        }

        $data['original_file'] = sprintf('%s%s', self::WEBROOT_PATH, $filename);

        $thumb = new Imagick();

        $fhandle = fopen($filepath, 'r');

        $thumb->readImageFile($fhandle);
        $thumb->scaleImage(240, 320, true);

        $thumbname = sprintf('%s.%s', 'thumb', $filename);
        $thumbpath = sprintf('%s%s', self::UPLOAD_DIR, $thumbname);

        $thumb->writeImage($thumbpath);

        fclose($fhandle);

        $data['thumbnail_file'] = sprintf('%s%s', self::WEBROOT_PATH, $thumbname);

        return new Response(
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS),
            Response::HTTP_OK,
            $this->getResponseHeaders()
        );
    }

    private function getResponseHeaders(): array
    {
        return [
                'Content-type'                 => 'application/json',
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => '*',
                'Access-Control-Allow-Headers' => '*'
        ];
    }

    private function checkMimetype(string $mimetype):bool
    {
        return in_array(
            $mimetype,
            [
                'image/jpeg',
                'image/gif',
                'image/png',
                'image/webp',
            ]
        );
    }
}
