<?php

namespace AfSoftlab\Binaroom\Door\Model\Estimate;

class Edge
{
    /** @var EstimateItem */
    private $estimateItem;

    /**
     * @var int
     */
    private $sn = 0;

    /**
     * @var EstimateItemDetail[]
     */
    private $details = [];

    /**
     * @param EstimateItem $estimateItem
     */
    public function __construct(EstimateItem $estimateItem)
    {
        $this->estimateItem = $estimateItem;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->estimateItem->getSku();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->estimateItem->getName();
    }

    /**
     * @return UnitType
     */
    public function getUnit(): UnitType
    {
        return $this->estimateItem->getUnit();
    }

    /**
     * @return int
     */
    public function getSn(): int
    {
        return $this->sn;
    }

    /**
     * @param int $sn
     *
     * @return $this
     */
    public function setSn(int $sn): self
    {
        $this->sn = $sn;

        return $this;
    }

    /**
     * @return EstimateItemDetail[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param EstimateItemDetail[] $details
     *
     * @return self
     */
    public function setDetails(array $details): self
    {
        $this->details = [];
        foreach ($details as $detail) {
            $this->addDetail($detail);
        }

        return $this;
    }

    /**
     * @param EstimateItemDetail $detail
     *
     * @return self
     */
    public function addDetail(EstimateItemDetail $detail): self
    {
        $this->details[] = $detail;

        return $this;
    }
}
