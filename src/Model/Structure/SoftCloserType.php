<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Common\ObjectValueEnumInterface;
use AfSoftlab\Binaroom\Door\Model\Common\ObjectValueEnumTrait;

class SoftCloserType implements ObjectValueEnumInterface
{
    use ObjectValueEnumTrait;

    public const CODE_LEFT = 'left';

    public const CODE_RIGHT = 'right';

    public const CODE_DOUBLE_SIDED = 'double_sided';

    public static function buildFromSymbol(string $symbol): ?self
    {
        switch ($symbol) {
            case "←":
                return self::LEFT();
            case "→":
                return self::RIGHT();
            case "↔":
                return self::DOUBLE_SIDED();
            case "—":
                return null;
            default:
                throw new \LogicException("unknown sof closers symbol:" . $symbol);
        }
    }

    /**
     * @return string[]
     */
    protected static function getAvailable(): array
    {
        return [
            self::CODE_LEFT => 'left',
            self::CODE_RIGHT => 'right',
            self::CODE_DOUBLE_SIDED => 'double_sided',
        ];
    }

    public static function LEFT(): self
    {
        return new self(self::CODE_LEFT);
    }

    public static function RIGHT(): self
    {
        return new self(self::CODE_RIGHT);
    }

    public static function DOUBLE_SIDED(): self
    {
        return new self(self::CODE_DOUBLE_SIDED);
    }
}
