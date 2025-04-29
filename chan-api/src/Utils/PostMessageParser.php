<?php

namespace PK\Utils;

abstract class PostMessageParser
{
    protected const SKIP_CODE_BLOCK_REGEXP = '((`{1,})[\s\S]+?(`{1,}))(*SKIP)(*F)';
    protected const BASE_URL = BASE_URL;
}
