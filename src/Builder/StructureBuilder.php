<?php

namespace AfSoftlab\Binaroom\Door\Builder;

use AfSoftlab\Binaroom\Door\Model\Structure\StructureItem;
use AfSoftlab\Binaroom\Door\Service\EstimateService;

class StructureBuilder
{
    public function build($data, ?EstimateService $estimateService = null, $level = 0): StructureItem
    {
        $box = isset($data->box) ? (array)$data->box : [];
        $wall = isset($data->wall) ? $data->wall->id : null;

        $structureItem = new StructureItem(
            $data->uid,
            $data->name,
            $data->type,
            isset($data->sku) ? $data->sku : null,
            [],
            $level,
            $data->matrix,
            $box,
            $wall,
            $estimateService
        );

        $level++;

        if (!isset($data->children)) {
            return $structureItem;
        }

        foreach ($data->children as $child) {
            $structureItem->addItem($this->build($child, null, $level));
        }

        return $structureItem;
    }
}
