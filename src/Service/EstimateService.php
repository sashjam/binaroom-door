<?php

namespace AfSoftlab\Binaroom\Door\Service;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;
use AfSoftlab\Binaroom\Door\Model\Estimate\Component;

class EstimateService
{
    /**
     * @var EstimateItem[]
     */
    private $itemsByEntityId = [];

    /**
     * @var array
     */
    private $itemsByEntityNameSku = [];

    /**
     * @var EstimateItem[]
     */
    private $items = [];

    /**
     * @param EstimateItem[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
        foreach ($items as $item) {
            $this->indexItem($item);
        }
    }

    private function indexItem(EstimateItem $item): void
    {
        $key = $this->buildNameSkuKey($item->getName(), $item->getSku());
        $this->itemsByEntityNameSku[$key] = $item;

        foreach ($item->getEntities() as $entityId) {
            $this->itemsByEntityId[$entityId] = $item;
        }

        foreach ($item->getElements() as $item) {
            $this->indexItem($item);
        }
    }

    public function findByEntityId(string $entityId): ?EstimateItem
    {
        if (isset($this->itemsByEntityId[$entityId])) {
            return $this->itemsByEntityId[$entityId];
        }

        return null;
    }

    public function findByEntityNameSku(string $entityName, string $sku): ?EstimateItem
    {
        $key = $this->buildNameSkuKey($entityName, $sku);
        if (isset($this->itemsByEntityNameSku[$key])) {
            return $this->itemsByEntityNameSku[$key];
        }

        return null;
    }

    private function buildNameSkuKey(string $name, string $sku): string
    {
        return $name . "/" . $sku;
    }

    /**
     * @return string[]
     */
    public function getUniqueMaterialNames(array $materials): array
    {
        $names = [];
        foreach ($materials as $material) {
            $names[] = $material->getName();
        }

        return array_values(array_unique($names));
    }

    /**
     * @return EstimateItem[]
     */
    public function getMaterials(): array
    {
        $res = [];
        foreach ($this->items as $item) {
            foreach ($item->getMaterials() as $material) {
                $res[] = $material;
            }
        }

        return $res;
    }

    /**
     * @return EstimateItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
    
    /**
     * @return Component[]
     */
    public function getComponents(): array
    {
        return $this->items;
    }

    /**
     * @return EstimateItem[]
     */
    private function unique($items): array
    {
        $res = [];
        foreach ($items as $item) {
            $key = "{$item->getName()}/{$item->getSku()}";
            $res[$key] = $item;
        }

        return array_values($res);
    }
}
