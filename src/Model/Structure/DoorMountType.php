<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Common\ObjectValueEnumInterface;
use AfSoftlab\Binaroom\Door\Model\Common\ObjectValueEnumTrait;

class DoorMountType implements ObjectValueEnumInterface
{
    use ObjectValueEnumTrait;

    public const CODE_COMPARTMENT = "compartment";

    public const CODE_SLIDING = "sliding";

    public const CODE_HINGED = "hinged";

    public const CODE_RAIL_OPEN = "rail_open";

    public const CODE_RAIL_HIDDEN_OPENING = "rail_hidden_opening";

    public const CODE_RAIL_HIDDEN_CLOSET = "rail_hidden_closet";

    public static function buildFromName(string $name): ?self
    {
        $match = function ($pattern) use ($name): bool {
            return preg_match($pattern, $name) === 1;
        };

        switch (true) {
            case $match('/^Дверь-купе.*/'):
            case $match('/^Двери-купе.*/'):
                return self::COMPARTMENT();
            case $match('/^Двери распашные.*/'):
                return self::SLIDING();
            case $match('/^Двери поворотные.*/'):
                return self::HINGED();
            case $match('/^SLIM РДО.*/'):
                return self::RAIL_OPEN();
            case $match('/^SLIM РДСП.*/'):
                return self::RAIL_HIDDEN_OPENING();
            case $match('/^SLIM РДСШ.*/'):
                return self::RAIL_HIDDEN_CLOSET();
            default:
                throw new \LogicException("unknown door mount type name:" . $name);
        }
    }

    /**
     * @return string[]
     */
    protected static function getAvailable(): array
    {
        return [
            self::CODE_COMPARTMENT => 'Раздвижные',
            self::CODE_SLIDING => 'Распашные',
            self::CODE_HINGED => 'Поворотные',
            self::CODE_RAIL_OPEN => 'Открытая напр.',
            self::CODE_RAIL_HIDDEN_OPENING => 'Скрытая в проём',
            self::CODE_RAIL_HIDDEN_CLOSET => 'Скрытая в шкаф',
        ];
    }

    public static function COMPARTMENT(): self
    {
        return new self(self::CODE_COMPARTMENT);
    }

    public static function SLIDING(): self
    {
        return new self(self::CODE_SLIDING);
    }

    public static function HINGED(): self
    {
        return new self(self::CODE_HINGED);
    }

    public static function RAIL_OPEN(): self
    {
        return new self(self::CODE_RAIL_OPEN);
    }

    public static function RAIL_HIDDEN_OPENING(): self
    {
        return new self(self::CODE_RAIL_HIDDEN_OPENING);
    }

    public static function RAIL_HIDDEN_CLOSET(): self
    {
        return new self(self::CODE_RAIL_HIDDEN_CLOSET);
    }
}
