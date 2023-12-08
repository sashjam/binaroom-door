<?php

namespace AfSoftlab\Binaroom\Door\Model\Estimate;

use AfSoftlab\Binaroom\Door\Model\Common\ObjectValueEnumInterface;
use AfSoftlab\Binaroom\Door\Model\Common\ObjectValueEnumTrait;

class UnitType implements ObjectValueEnumInterface
{
    use ObjectValueEnumTrait;

    public const CODE_UNKNOWN = 1;

    public const CODE_RUNNING_METER = 2;

    public const CODE_SQUARE_METER = 3;

    public static function build(int $code): ?self
    {
        switch ($code) {
            case self::CODE_UNKNOWN:
                return self::UNKNOWN();
            case self::CODE_RUNNING_METER:
                return self::RUNNING_METER();
            case self::CODE_SQUARE_METER:
                return self::SQUARE_METER();
            default:
                throw new \LogicException("unknown unit code:" . $code);
        }
    }

    /**
     * @return string[]
     */
    protected static function getAvailable(): array
    {
        return [
            self::CODE_UNKNOWN => 'unknown',
            self::CODE_RUNNING_METER => 'rm',
            self::CODE_SQUARE_METER => 'm2',
        ];
    }

    public static function UNKNOWN(): self
    {
        return new self(self::CODE_UNKNOWN);
    }

    public static function RUNNING_METER(): self
    {
        return new self(self::CODE_RUNNING_METER);
    }

    public static function SQUARE_METER(): self
    {
        return new self(self::CODE_SQUARE_METER);
    }
}
