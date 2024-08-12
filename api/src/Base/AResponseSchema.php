<?php

namespace PK\Base;

abstract class AResponseSchema implements \JsonSerializable
{
    #[\ReturnTypeWillChange]
    abstract public function jsonSerialize();
}
