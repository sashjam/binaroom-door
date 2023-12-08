<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;

class Track
{
    /** @var StructureItem */
    private $item = null;

    /** @var  DoorAcc[] */
    private $accessories = [];

    /** @var float|null */
    private $length = null;

    /** @var string|null */
    private $topMat = null;

    /** @var string|null */
    private $bottomMat = null;

    public const TOP_TRACK = "top";
    public const BOTTOM_TRACK = "bottom";

    public function __construct(StructureItem $item, array $accessories = [])
    {
        $this->item = $item;
        $this->accessories = $accessories;
        $this->length = $item->getBox() ? $item->getBox()->getLength() : null;
        $this->topMat = $this->getTrackMat(self::TOP_TRACK);
        $this->bottomMat = $this->getTrackMat(self::BOTTOM_TRACK);

    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function getTopTrackMat(): ?string
    {
        return $this->topMat;
    }

    public function getBottomTrackMat(): ?string
    {
        return $this->bottomMat;
    }

    /**
     * В аксессуарах, в смете найти аксессуар с названием "Напрвл. * Верхн." и подставить ее название и sku из сметы
     *
     * @param string $position
     *
     * @return string|null
     */
    private function getTrackMat($position = self::TOP_TRACK): ?string
    {
        foreach ($this->accessories as $accessory) {
            $result = $this->_getTrackMat($accessory->getItem(), $position);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private function _getTrackMat(EstimateItem $item, $position): ?string
    {
        $regExpr = '/^Направл.*верхн.*/';
        if ($position !== self::TOP_TRACK) {
            $regExpr = '/^Направл.*нижн.*/';
        }

        if (preg_match($regExpr, $item->getName()) === 1) {
            return $item->getSku();
        }

        return null;
    }
}
