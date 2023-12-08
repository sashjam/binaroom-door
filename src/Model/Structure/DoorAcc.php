<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;

class DoorAcc
{
    /** @var EstimateItem */
    private $item;

    public function __construct(EstimateItem $item)
    {
        $this->item = $item;
    }

    public function getUid(): string
    {
        return implode("|", $this->item->getEntities());
    }

    public function getName(): string
    {
        return $this->item->getName();
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

