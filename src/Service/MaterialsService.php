<?php

namespace AfSoftlab\Binaroom\Door\Service;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;
use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItemDetail;
use AfSoftlab\Binaroom\Door\Model\Structure\DoorKit;
use AfSoftlab\Binaroom\Door\Model\Structure\Door;

class MaterialsService
{
    /**
     * @var EstimateItem[][]
     */
    private $materialsByUid = [];

    /**
     * @var EstimateItemDetail[][]
     */
    private $allValues = [];

    /**
     * @var EstimateItem[][]
     */
    private $materialsByNames = [];

    /**
     * @param string[] $materialNames
     * @param EstimateItem[] $materials
     * @param array $materialsValues
     */
    public function __construct($materialNames, $materials, $materialsValues)
    {
        foreach ($materialsValues as $k => $values) {
            $name = $materialNames[$k];

            $sameMaterials = $this->findMaterialsByName($materials, $name);
            foreach ($sameMaterials as $_material) {
                $_material->setValue($values['value']);
            }

            if (!isset($this->detailsByName[$name])) {
                $this->detailsByName[$name] = [];
            }

            foreach ($values['details'] as $v) {
                $this->detailsByName[$name][] = $v;
            }

            foreach ($sameMaterials as $_material) {
                foreach ($values['details'] as $v) {
                    $uid = $v['uid'];

                    if (!isset($this->materialsByUid[$uid])) {
                        $this->materialsByUid[$uid] = [];
                    }
                    if (!isset($this->materialsByNames[$name])) {
                        $this->materialsByNames[$name] = [];
                    }
                    if (!isset($this->allValues[$name])) {
                        $this->allValues[$name] = [];
                    }

                    $this->materialsByNames[$name][] = $_material;
                    $this->materialsByUid[$uid][] = $_material;
                    $this->allValues[spl_object_hash($_material)][] = new EstimateItemDetail(
                        $v['uid'],
                        round($v['value'], 3)
                    );
                }
            }
        }
    }

    /**
     * @param EstimateItem[] $materials
     * @param $name
     *
     * @return EstimateItem[]
     */
    private function findMaterialsByName(array $materials, $name)
    {
        $result = [];
        foreach ($materials as $material) {
            if ($material->getName() === $name) {
                $result[] = $material;
            }
        }

        return $result;
    }

    /**
     * @param EstimateItem $material
     *
     * @return EstimateItemDetail[]
     */
    private function getItemDetails(EstimateItem $material): array
    {
        return $this->allValues[spl_object_hash($material)] ?? [];
    }

    /**
     * @return EstimateItem[]
     */
    private function unique(array $items): array
    {
        $res = [];
        foreach ($items as $item) {
            $key = "{$item->getName()}/{$item->getSku()}";
            $res[$key] = $item;
        }

        return array_values($res);
    }

    /**
     * @return EstimateItem[]
     */
    private function sortByName($items): array
    {
        usort($items, function (EstimateItem $a, EstimateItem $b) {
            return $a->getName() > $b->getName() ? 1 : -1;
        });

        return $items;
    }

    /**
     * @param $uid
     * @param Door $door
     *
     * @return EstimateItem|null
     */
    public function getByUidWithDoor($uid, Door $door)
    {
        $materials = $this->materialsByUid[$uid] ?? [];
        foreach ($materials as $material) {
            $parent = $material->getParent();
            if ($parent == null) {
                continue;
            }
            if (in_array($door->getUid(), $parent->getEntities())) {
                return $material;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param string[] $allUids
     *
     * @return EstimateItemDetail|null
     */
    public function getFirstByNameWithUids($name, array $allUids): ?EstimateItemDetail
    {
        $result = $this->getByNameWithUids($name, $allUids);

        return $result ? $result[0] : null;
    }

    /**
     * @param $name
     * @param Door $door
     *
     * @return EstimateItemDetail[]
     */
    public function getByNameWithUids($name, array $allUids)
    {
        $result = [];
        $materials = $this->materialsByNames[$name] ?? [];
        $allUidsMap = [];
        foreach ($allUids as $uid) {
            $allUidsMap[$uid] = true;
        }
        foreach ($materials as $material) {
            $parent = $material->getParent();
            if ($parent == null) {
                continue;
            }

            if (!array_intersect($allUids, $parent->getEntities())) {
                continue;
            }

            $details = $this->getItemDetails($material);
            foreach ($details as $detail) {
                if (isset($allUidsMap[$detail->getUid()])){
                    $result[] = $detail;
                }
            }
        }

        return $this->uniqueItemDetails($result);
    }

    /**
     * @param $name
     * @param Door $door
     *
     * @return EstimateItemDetail[]
     */
    public function getByName($name)
    {
        $result = [];
        $materials = $this->materialsByNames[$name] ?? [];
        foreach ($materials as $material) {
            $details = $this->getItemDetails($material);
            foreach ($details as $detail) {
                $result[] = $detail;
            }
        }

        return $this->uniqueItemDetails($result);
    }

    /**
     * @param EstimateItemDetail[] $items
     *
     * @return EstimateItemDetail[]
     */
    private function uniqueItemDetails(array $items): array
    {
        $res = [];
        foreach ($items as $item) {
            $key = "{$item->getUid()}/{$item->getValue()}";
            $res[$key] = $item;
        }

        return array_values($res);
    }

    /**
     * @param DoorKit $doorKit
     *
     * @return EstimateItem[]
     */
    public function getInsetMaterials(DoorKit $doorKit): array
    {
        $result = [];
        foreach ($doorKit->getDoors() as $door) {
            foreach ($door->getLiners() as $liner) {
                $material = $this->getByUidWithDoor($liner->getUid(), $door);
                if ($material !== null) {
                    $result[] = $material;
                }
            }
        }

        return $this->sortByName(
            $this->unique($result)
        );
    }

    /**
     * @param DoorKit $doorKit
     *
     * @return EstimateItem[]
     */
    public function getProfileMaterials(DoorKit $doorKit): array
    {
        $materials = [];
        foreach ($doorKit->getDoors() as $door) {
            foreach ($door->findProfiles() as $profile) {
                $materials[] = $profile->getItem();
            }
        }

        return $this->sortByName(
            $this->unique($materials)
        );
    }

    /**
     * @param array $materials
     * @param $name
     * @param $sku
     *
     * @return int|null
     */
    public function getMaterialIndexByParams(array $materials, $name, $sku): ?int
    {
        foreach ($materials as $i => $material) {
            $equal =
                $material->getName() === $name &&
                $material->getSku() === $sku;

            if ($equal) {
                return $i;
            }
        }

        return -1;
    }
}
