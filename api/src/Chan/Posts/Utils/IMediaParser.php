<?php

namespace PK\Utils;

interface IMediaParser
{
    public static function execute(string $message): array;
}
