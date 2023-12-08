<?php

namespace AfSoftlab\Binaroom\Door\Model\Common;

interface ObjectValueEnumInterface
{
    public function getCode(): string;

    public function getLabel(): string;
}
