<?php

namespace AfSoftlab\Binaroom\Door\Model\Estimate;

class Component
{
    /** @var EstimateItem */
    private $estimateItem;

    /** @var string[] */
    private $uids;

    /**
     * @param EstimateItem $estimateItem
     * @param string[] $uids
     */
    public function __construct(EstimateItem $estimateItem, array $uids)
    {
        $this->estimateItem = $estimateItem;
        $this->uids = $uids;
    }

    /**
     * @return string[]
     */
    public function getUids(): array
    {
        return $this->uids;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->estimateItem->getName();
    }

    /**
     * @return string[]
     */
    public function getEntities(): array
    {
        return $this->estimateItem->getEntities();
    }

    /**
     * @return float
     */
    public function getCount(): float
    {
        return $this->estimateItem->getCount();
    }

    /**
     * @return EstimateItem[]
     */
    public function getMaterials(): array
    {
        return array_filter($this->estimateItem->getMaterials(), function ($material) {
            return preg_match('/^Кромка.*/', $material->getName()) !== 1;
        });
    }

    /**
     * @return Edge[]
     */
    public function getEdges(): array
    {
        $items = $this->estimateItem->getMaterials();
        $items = array_filter($items, function (EstimateItem $items) {
            return preg_match('/^Кромка.*/', $items->getName()) === 1;
        });
        usort($items, function (EstimateItem $a, EstimateItem $b) {
            return $a->getName() > $b->getName() ? 1 : -1;
        });

        $edges = [];
        foreach ($items as $k => $item) {
            $edge = new Edge($item);
            $edges[] = $edge;
        }

        return $edges;
    }
}
