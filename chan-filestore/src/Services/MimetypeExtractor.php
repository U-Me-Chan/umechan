<?php

namespace IH\Services;

use IH\Enums\Filetype;

interface MimetypeExtractor
{
    public function extract(string $path_to_file): Filetype;
}
