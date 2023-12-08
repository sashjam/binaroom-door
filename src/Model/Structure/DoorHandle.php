<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

class DoorHandle
{
    public const CODE_LEFT = 'left';

    public const CODE_RIGHT = 'right';

    /** @var StructureItem */
    private $item;

    /** @var */
    private $position;

    /** @var Door */
    private $door;

    private function __construct(StructureItem $item, Door $door, string $position)
    {
        $this->item = $item;
        $this->door = $door;
        $this->position = $position;
    }

    public static function build(StructureItem $item, Door $door): ?self
    {
        $lastChar = mb_substr($item->getName(), -1);
        switch ($lastChar) {
            case "Λ":
            case "Л":
                return new DoorHandle($item, $door, self::CODE_RIGHT); // не опечатка
            case "П":
                return new DoorHandle($item, $door, self::CODE_LEFT);
            default:
                throw new \LogicException("unknown handler position symbol:" . $lastChar);
        }
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getPositionIndent()
    {
        $handle = $this->getHandle($this->item);
        $relativePosition = $handle->getRelativePosition($this->item);

        return $relativePosition->getY();
    }

    private function getHandle(StructureItem $item)
    {
        if ($item->getSku() && !$item->getItems()) {
            return $item;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->getHandle($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
