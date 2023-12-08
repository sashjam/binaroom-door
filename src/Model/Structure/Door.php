<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;
use AfSoftlab\Binaroom\Door\Service\AbcHelper;

class Door
{
    /** @var StructureItem */
    private $item;

    /** @var DoorLiner[] */
    private $liners;

    public function __construct(StructureItem $item)
    {
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->item->getUid();
    }

    public function getName(): string
    {
        return $this->item->getName();
    }

    public function getType(): string
    {
        $type = $this->item->getType();

        // СТД Фасад -> СТД
        $type = explode(" ", $type);
        $type = $type[0];

        return $type;
    }

    public function getPath(): ?string
    {
        return $this->item->getPath();
    }

    public function getIndexPath(): string
    {
        return $this->item->getIndexPath();
    }

    public function getSku(): string
    {
        return $this->item->getSku();
    }

    public function getSkuPretty(DoorKit $doorKit, EstimateItem $styleMat): string
    {
        // СТД Фасад -> ST
        $a = $this->getType();
        $a = strtr($a, ["СТД" => "ST"]);

        // SS
        $mountType = $doorKit->getMountType()->getCode();

        $b = "";
        switch ($mountType) {
            case DoorMountType::CODE_COMPARTMENT:
                $b = "SS";
                break;
            case DoorMountType::CODE_SLIDING:
                $b = "PS";
                break;
            case DoorMountType::CODE_HINGED:
                $b = "PS";
                break;
        }

        $c = $this->getModelName();

        // Fusion -> F
        $d = $doorKit->getDoorSystem()->getStyle();
        $d = strtr($d, ["Fusion" => "F"]);

        // IVMPP
        $e = $styleMat->getLastSkuPart();

        return "{$a}.{$b}.{$c}.{$d}-{$e}";
    }

    public function getWidth(): float
    {
        return $this->item->getWidth();
    }

    public function getHeight(): float
    {
        return $this->item->getHeight();
    }

    public function getPosition()
    {
        return $this->item->getPosition();
    }

    public function getDoorContainer()
    {
        return $this->_getDoorContainer($this->item);
    }

    private function _getDoorContainer(?StructureItem $item)
    {
        if ($item === null) {
            return null;
        }

        if (preg_match('/^СТД Дверь-купе .*/', $item->getName()) === 1) {
            return $item;
        }

        return $this->_getDoorContainer($item->getParent());
    }

    public function getRelativePosition($to): Position
    {
        return $this->item->getRelativePosition($to);
    }

    public function print()
    {
        return $this->item->print();
    }

    /**
     * @return DoorLiner[] Находит все дверные вставки двери
     */
    public function getLiners(): array
    {
        if ($this->liners !== null) {
            return $this->liners;
        }

        $liners = $this->_getLiners($this->item);
        $door = $this;

        // Сортировка, слева - направо, сверху - вниз, спереди фасада - сзади фасада
        usort($liners, function (DoorLiner $a, DoorLiner $b) use ($door) {
            $aPosition = $a->getRelativePosition($door);
            $bPosition = $b->getRelativePosition($door);

            switch (true) {
                case $aPosition->getY() < $bPosition->getY():
                    return 1;
                case $aPosition->getY() > $bPosition->getY():
                    return -1;
            }

            switch (true) {
                case $aPosition->getX() > $bPosition->getX():
                    return 1;
                case $aPosition->getX() < $bPosition->getX():
                    return -1;
            }

            switch (true) {
                case $aPosition->getZ() < $bPosition->getZ():
                    return 1;
                case $aPosition->getZ() > $bPosition->getZ():
                    return -1;
            }

            return 0;
        });

        $i = 0;
        foreach ($liners as $liner) {
            if (!$liner->isFrontPosition($this, $liners)) {
                $downsideLiner = $liner->getDownsideLiner($this, $liners);
                if ($downsideLiner) {
                    $symbolicPosition = $downsideLiner->getSymbolicPosition() . "'"; // A -> A'
                    $liner->setSymbolicPosition($symbolicPosition);
                    continue;
                }
            }

            $liner->setSymbolicPosition($this->getSymbolicPosition($i));
            $i++;
        }

        $this->liners = $liners;

        return $this->liners;
    }

    /**
     * @return DoorLiner[]
     */
    private function _getLiners(StructureItem $item): array
    {
        $liners = [];
        if (!count($item->getItems())) {
            if (preg_match('/^Вставка.*/', $item->getName()) === 1 && count($item->getItems()) === 0) {
                $liners[] = new DoorLiner($item);
            }

            return $liners;
        }

        foreach ($item->getItems() as $_item) {
            $_liners = $this->_getLiners($_item);
            foreach ($_liners as $liner) {
                $liners[] = $liner;
            }
        }

        return $liners;
    }

    /**
     * @return DoorProfile[]
     */
    public function findProfiles(): array
    {
        $item = $this->item->getEstimateService()->findByEntityId($this->getUid());
        $profiles = [];
        foreach ($item->getElements() as $element) {
            if (preg_match('/^Профиль .*/', $element->getName()) === 1) {
                $profiles[] = new DoorProfile($element);
            }
            if (preg_match('/^Рамка .*/', $element->getName()) === 1) {
                $profiles[] = new DoorProfile($element);
            }
        }

        return $profiles;
    }

    public function getAllUids(): array
    {
       return $this->item->getAllUids();
    }

    public function getModelName(): ?string
    {
        $name = $this->_getModelName($this->item);

        return intval(trim(strtr($name, ["Модель" => ""]))); // 	// Модель 01 -> 1
    }

    private function _getModelName(StructureItem $item): ?string
    {
        if (preg_match('/^Контейнер Модели.*/', $item->getName()) === 1) {
            $items = $item->getItems();
            $firstItem = $items[0] ?? null;
            if ($firstItem) {
                return $firstItem->getName();
            }

            return null;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->_getModelName($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return DoorHandle[]
     */
    public function getHandles(): array
    {
        return $this->_getHandles($this->item);
    }

    private function _getHandles(StructureItem $item): array
    {
        $handles = [];
        if (preg_match('/^Контейнер для ручки врезной.*/', $item->getName()) === 1) {
            if ($item->getItems()) {
                $handles[] = DoorHandle::build($item, $this);
            }
        }

        foreach ($item->getItems() as $_item) {
            $_handles = $this->_getHandles($_item);
            foreach ($_handles as $handle) {
                $handles[] = $handle;
            }
        }

        return $handles;
    }

    private function getSymbolicPosition(int $index)
    {
        $abc = AbcHelper::getAbc();
        if ($index > count($abc)) {
            return "Z{$index}";
        }

        return $abc[$index];
    }
}
