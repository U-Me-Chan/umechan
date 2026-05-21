<?php

namespace IH\Enums;

enum Filetype: string
{
    case image = 'image';
    case video = 'video';
    case unsupported = 'unsupported';
}
