<?php

namespace IH\Enums;

enum Mimetype: string
{
    case image = 'image';
    case video = 'video';
    case unsupported = 'unsupported';
}
