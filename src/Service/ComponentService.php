<?php

namespace AfSoftlab\Binaroom\Door\Service;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;
use AfSoftlab\Binaroom\Door\Model\Estimate\Component;

class ComponentService
{
    /**
     * @var EstimateItem[]
     */
    private $items = [];

    /**
     * @var StructureService
     */
    private $structureService;

    /**
     * @param EstimateItem[] $items
     */
    public function __construct(StructureService $structureService, array $items)
    {
        $this->structureService = $structureService;
        $this->items = $items;
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
        $components = [];
        foreach ($this->items as $item) {
            $componentStructureItems = $this->structureService->findAllByUids($item->getEntities());
            $uids = [];
            foreach ($componentStructureItems as $componentStructureItem) {
                $uids = array_merge($uids, $componentStructureItem->getAllUids());
            }

            // пройдемся по всем компонентам и найдем вложеннные компоненты (когда один компонент находится внутри другого)
            $childUids = [];
            foreach ($this->items as $_item) {
                if ($item === $_item) {
                    continue;
                }

                $_componentStructureItems = $this->structureService->findAllByUids($_item->getEntities());
                $_uids = [];
                foreach ($_componentStructureItems as $_componentStructureItem) {
                    $_uids = array_merge($_uids, $_componentStructureItem->getAllUids());
                }

                // если у компонента детей больше, то он не может быть вложен в текущий компонент
                if (count($uids) < count($_uids)) {
                    continue;
                }

                $intersect = array_intersect($uids, $_uids);
                // если есть пересечение, значит один компонент внутри другого
                if ($intersect) {
                    $childUids = array_merge($childUids, $intersect);
                }
            }

            // если есть вложенные компоненты
            if ($childUids) {
                $uids = array_diff($uids, $childUids); // удаляем uid-ы вложенного компонента
                $uids = array_values($uids);
            }
            
            $components[] = new Component($item, $uids);
        }

        return $components;
    }
}
