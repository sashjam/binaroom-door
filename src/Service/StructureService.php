<?php

namespace AfSoftlab\Binaroom\Door\Service;

use AfSoftlab\Binaroom\Door\Model\Structure\StructureItem;

class StructureService
{

    /**
     * @var StructureItem[]
     */
    private $itemsByUid = [];

    /**
     * @param StructureItem $structureItem
     */
    public function __construct(StructureItem $structureItem)
    {
        $this->indexItem($structureItem);
    }

    private function indexItem(StructureItem $structureItem)
    {
        $uid = $structureItem->getUid();
        $this->itemsByUid[$uid] = $structureItem;
        foreach ($structureItem->getItems() as $item) {
            $this->indexItem($item);
        }
    }

    /**
     * @param string[] $uids
     *
     * @return bool
     */
    public function isAnyProduct(array $uids): bool
    {
        foreach ($uids as $uid) {
            $item = $this->itemsByUid[$uid] ?? null;
            if (!$item) {
                continue;
            }

            if ($item->getLevel() === 1 && count($item->getItems())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $uids
     *
     * @return bool
     */
    public function findProduct(array $uids): StructureItem
    {
        foreach ($uids as $uid) {
            $item = $this->itemsByUid[$uid] ?? null;
            if (!$item) {
                continue;
            }

            return $this->getRootParentProduct($item);
        }

        return false;
    }

    public function findProductItem(string $uid): ?StructureItem
    {
        $item = $this->itemsByUid[$uid] ?? null;
        if (!$item) {
            return null;
        }

        return $item;
    }

    private function getRootParentProduct(StructureItem $item): StructureItem
    {
        $parent = $item->getParent();
        if ($parent->getLevel() === 1) {
            return $parent;
        }

        return $this->getRootParentProduct($parent);
    }

    /**
     * @param string[] $uids
     *
     * @return StructureItem|null
     */
    public function findByUids(array $uids): ?StructureItem
    {
        foreach ($uids as $uid) {
            if (isset($this->itemsByUid[$uid])) {
                return $this->itemsByUid[$uid];
            }
        }

        return null;
    }

    /**
     * @param string[] $uids
     *
     * @return []StructureItem
     */
    public function findAllByUids(array $uids): array
    {
        $res = [];
        foreach ($uids as $uid) {
            if (isset($this->itemsByUid[$uid])) {
                $res[] = $this->itemsByUid[$uid];
            }
        }

        return $res;
    }
}
