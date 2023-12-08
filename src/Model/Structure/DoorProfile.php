<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;

class DoorProfile
{
    /** @var EstimateItem */
    private $item;

    public function __construct(EstimateItem $item)
    {
        $this->item = $item;
    }

    public function getName(): string
    {
        return $this->item->getName();
    }

    public function getDecorName(): string
    {
        $sku = $this->item->getSku();
        $parts = explode(".", $sku);

        return end($parts);
    }

    public function getCount(): float
    {
        return $this->item->getCount();
    }

    public function getSku(): string
    {
        return $this->item->getSku();
    }

    /**
     * @return EstimateItem
     */
    public function getItem(): EstimateItem
    {
        return $this->item;
    }
}
