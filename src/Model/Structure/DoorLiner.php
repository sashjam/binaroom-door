<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

class DoorLiner
{
    /** @var StructureItem */
    private $item;

    /** @var string|null */
    private $symbolicPosition;

    /** @var string|null */
    private $materialSymbolicPosition;

    public function __construct(StructureItem $item)
    {
        $this->item = $item;
    }

    public function getUid(): string
    {
        return $this->item->getUid();
    }

    public function getName(): string
    {
        return $this->item->getName();
    }

    public function getIndexPath(): string
    {
        return $this->item->getIndexPath();
    }

    public function print(): string
    {
        return $this->item->print();
    }

    public function getWidth(): float
    {
        return $this->item->getWidth();
    }

    public function getThick(): float
    {
        return $this->item->getThick();
    }

    public function getHeight(): float
    {
        return $this->item->getHeight();
    }

    public function getRelativePosition($to): Position
    {
        return $this->item->getRelativePosition($to);
    }

    /**
     * Проверяет, что вставка находится с фасадной стороны двери
     *
     * @param Door $door
     * @param DoorLiner[] $liners
     *
     * @return bool
     */
    public function isFrontPosition(Door $door, array $liners): bool
    {
        $downsideLiner = $this->getDownsideLiner($door, $liners);
        if ($downsideLiner) {
            return $this->getRelativePosition($door)->getZ() >= $downsideLiner->getRelativePosition($door)->getZ();
        }

        return true;
    }

    /**
     * Возвращает вставку с обратной стороны
     *
     * @param Door $door
     * @param DoorLiner[] $liners
     *
     * @return DoorLiner|null
     */
    public function getDownsideLiner(Door $door, array $liners): ?DoorLiner
    {
        $position = $this->getRelativePosition($door);

        foreach ($liners as $liner) {
            if ($this === $liner) {
                continue;
            }

            // если есть вставка с такими же положением, то смотрим какая вставка спереди
            $linerPosition = $liner->getRelativePosition($door);
            if ($linerPosition->equalByXY($position)) {
                return $liner;
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getSymbolicPosition(): ?string
    {
        return $this->symbolicPosition;
    }

    /**
     * @return string|null
     */
    public function getMaterialSymbolicPosition(): ?string
    {
        return $this->materialSymbolicPosition;
    }

    /**
     * @param string|null $materialSymbolicPosition
     *
     * @return self
     */
    public function setMaterialSymbolicPosition(?string $materialSymbolicPosition): self
    {
        $this->materialSymbolicPosition = $materialSymbolicPosition;

        return $this;
    }

    public function getSlotBox()
    {
        return $this->_getSlotBox($this->item);
    }

    private function _getSlotBox(StructureItem $item)
    {
        if ($item->getParent() === null) {
            return null;
        }

        $parent = $item->getParent();
        if (preg_match('/^Наполнение в паз .*/', $parent->getType()) === 1) {
            return $parent->getBox();
        }

        return $this->_getSlotBox($parent);
    }


    public function getTextureDirection()
    {
        $parts = explode(" ", $this->getName());
        $lastSymbol = end($parts);

        switch ($lastSymbol) {
            case "↕": //
            case "🡙": // ↕
                return "↕";
            case "↔":
                return $lastSymbol;
            case "—":
            default:
                return ".";
        }
    }


    /**
     * @param string|null $symbolicPosition
     *
     * @return self
     */
    public function setSymbolicPosition(?string $symbolicPosition): self
    {
        $this->symbolicPosition = $symbolicPosition;

        return $this;
    }
}
