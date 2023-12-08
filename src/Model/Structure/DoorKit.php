<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Model\Estimate\EstimateItem;

class DoorKit
{
    /** @var StructureItem */
    private $item;

    /** @var Track[]|null */
    private $tracksTop;

    /** @var Track[]|null */
    private $tracksBottom;

    /**
     * @var Door[]|null
     */
    private $doors = null;

    /**
     * @var string[]|null
     */
    private $allUids = null;

    public const DOOR_POSITION_FRONT = "front";
    public const DOOR_POSITION_BACK = "back";

    public function __construct(StructureItem $item)
    {
        $this->item = $item;
    }

    public function getUid(): string
    {
        return $this->item->getUid();
    }

    public function getName(): string
    {
        return $this->item->getName();
    }

    public function getIndexPath(): string
    {
        return $this->item->getIndexPath();
    }

    public function getMountType(): DoorMountType
    {
        return DoorMountType::buildFromName($this->item->getName());
    }

    public function getWidth(): float
    {
        return $this->item->getWidth();
    }

    public function getHeight(): float
    {
        return $this->item->getHeight();
    }

    public function print(): string
    {
        return $this->item->print();
    }

    public function getPath(): ?string
    {
        return $this->item->getPath();
    }

    /**
     * Находит все дверные вставки двери
     *
     * @return Door[]
     */
    public function getDoors(): array
    {
        if ($this->doors !== null) {
            return $this->doors;
        }

        $doors = $this->_getDoors($this->item);

        // порядок дверей слева - направо
        $parent = $this;
        usort($doors, function (Door $a, Door $b) use ($parent) {
            return $a->getRelativePosition($parent)->getX() > $b->getRelativePosition($parent)->getX() ? 1 : -1;
        });

        $this->doors = $doors;

        return $this->doors;
    }

    /**
     * @return Door[]
     */
    private function _getDoors(StructureItem $item): array
    {
        $doors = [];
        if (preg_match('/^СТД Фасад .*/', $item->getName()) === 1) {
            $doors[] = new Door($item);
        }

        foreach ($item->getItems() as $_item) {
            $_doors = $this->_getDoors($_item);
            foreach ($_doors as $liner) {
                $doors[] = $liner;
            }
        }

        return $doors;
    }

    public function getAllUids(): array
    {
        if ($this->allUids !== null) {
            return $this->allUids;
        }
        $this->allUids = array_unique(
            $this->_getAllUids($this->item)
        );

        return $this->allUids;
    }

    private function _getAllUids(StructureItem $item): array
    {
        $result = [];
        if ($item->getUid() !== null) {
            $result[] = $item->getUid();
        }

        foreach ($item->getItems() as $_item) {
            $subItems = $this->_getAllUids($_item);
            foreach ($subItems as $subItem) {
                $result[] = $subItem;
            }
        }

        return $result;
    }

    /**
     * Положение двери спереди-сзади, можно найти по координате Z
     *
     * @param Door $door
     *
     * @return string
     */
    public function getDoorPosition(Door $door): string
    {
        $doorContainer = $door->getDoorContainer();
        if ($doorContainer === null) {
            return self::DOOR_POSITION_FRONT;
        }

        $offsets = [];
        foreach ($this->getDoors() as $_door) {
            $_doorContainer = $_door->getDoorContainer();
            if ($_doorContainer === null) {
                continue;
            }

            $offsets[] = $_doorContainer->getPosition()->getZ();
        }

        $meanOffsets = array_sum($offsets) / count($offsets);
        if ($doorContainer->getPosition()->getZ() < $meanOffsets) {
            return self::DOOR_POSITION_BACK;
        }

        return self::DOOR_POSITION_FRONT;
    }

    /**
     * @return DoorAcc[]
     */
    public function getAccessories(): array
    {
        $item = $this->getAccessoriesItem();
        if ($item === null) {
            return [];
        }

        $accessories = [];
        foreach ($item->getElements() as $element) {
            $accessories[] = new DoorAcc($element);
        }

        return $accessories;
    }

    /**
     * Находит расположение дверей в виде символов
     *
     * Искать по имени "Расположение дверей" в чайлдах, брать название (может быть глубоко)
     *
     * @return string[]
     */
    public function getCollocation(): array
    {
        $collocations = $this->_getCollocation($this->item);

        // порядок дверей слева - направо
        $parent = $this;
        usort($collocations, function (StructureItem $a, StructureItem $b) use ($parent) {
            return $a->getRelativePosition($parent)->getX() > $b->getRelativePosition($parent)->getX() ? 1 : -1;
        });

        $result = [];
        foreach ($collocations as $collocation) {
            $result[] = $collocation->getName();
        }

        return $result;
    }

    /**
     * @param StructureItem $item
     *
     * @return StructureItem[]
     */
    private function _getCollocation(StructureItem $item): array
    {
        $result = [];
        if (preg_match('/^СТД Расположение дверей.*/', $item->getType()) === 1) {
            $result[] = $item;
        }

        foreach ($item->getItems() as $_item) {
            $res = $this->_getCollocation($_item);
            foreach ($res as $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    public function getTopFrameUid(): ?string
    {
        $topFrame = $this->getTopFrame($this->item);
        $frame = $this->getFrame($topFrame);

        return $frame ? $frame->getUid() : null;
    }

    private function getTopFrame(StructureItem $item): ?StructureItem
    {
        if (preg_match('/^Рамка верхняя/', $item->getName()) === 1) {
            return $item;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->getTopFrame($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    public function getBottomFrameUid(): ?string
    {
        $frame = $this->getBottomFrame($this->item);
        $frame = $this->getFrame($frame);

        return $frame ? $frame->getUid() : null;
    }

    private function getBottomFrame(StructureItem $item): ?StructureItem
    {
        if (preg_match('/^Рамка нижняя/', $item->getName()) === 1) {
            return $item;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->getBottomFrame($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private function getFrame(StructureItem $item): ?StructureItem
    {
        if (preg_match('/^Рамка/', $item->getName()) === 1 && count($item->getItems()) === 0) {
            return $item;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->getFrame($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    public function getStyleMatUid(): ?string
    {
        $facadeFrame = $this->getFacadeFrame($this->item);
        $verticalProfile = $this->getVerticalProfile($facadeFrame);

        return $verticalProfile ? $verticalProfile->getUid() : null;
    }

    private function getFacadeFrame(StructureItem $item): ?StructureItem
    {
        if (preg_match('/^СТД Рамка фасада.*/', $item->getName()) === 1) {
            return $item;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->getFacadeFrame($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private function getVerticalProfile(StructureItem $item): ?StructureItem
    {
        if (preg_match('/^Профиль.*/', $item->getName()) === 1 && count($item->getItems()) === 0) {
            return $item;
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->getVerticalProfile($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Находит корневой элемент, кроме комнаты и стен
     *
     * Самый верхний родитель, кроме комнаты и стены (у компнаты есть чилдрены с wall), сохранять uid
     *
     * @return StructureItem
     */
    public function getRootParentItem(): StructureItem
    {
        return $this->_getRootParentItem($this->item);
    }

    private function _getRootParentItem(StructureItem $item): StructureItem
    {
        if ($item->getParent() === null) {
            return $item;
        }
        if ($item->getParent()->isWall()) {
            return $item;
        }

        return $this->_getRootParentItem($item->getParent());
    }

    /**
     * В дверях купе есть направляющие, искать по типу "Направл. " (первый ребенок), брать первого,
     * внутри взять самого глубокого ребенка с названием "Направл." и без детей. Брать длинну из бокса
     *
     * @return Track|null
     */
    public function getTopTrack(): ?Track
    {
        $tracks = $this->getTopTracks();

        return $tracks[0] ?? null;
    }

    public function getBottomTrack(): ?Track
    {
        $tracks = $this->getBottomTracks();

        return $tracks[0] ?? null;
    }

    /**
     * @return Track[]
     */
    public function getTopTracks(): array
    {
        if ($this->tracksTop !== null) {
            return $this->tracksTop;
        }

        $tracks = $this->_getTracks($this->item, Track::TOP_TRACK);
        $this->tracksTop = [];
        foreach ($tracks as $track) {
            $this->tracksTop[] = $this->_getTrackBox($track);
        }

        return $this->tracksTop;
    }

    public function getBottomTracks(): ?array
    {
        if ($this->tracksBottom !== null) {
            return $this->tracksBottom;
        }

        $tracks = $this->_getTracks($this->item, Track::BOTTOM_TRACK);
        $this->tracksBottom = [];
        foreach ($tracks as $track) {
            $this->tracksBottom[] = $this->_getTrackBox($track);
        }

        return $this->tracksBottom;
    }


    /**
     * @param StructureItem $item
     *
     * @return StructureItem[]
     */
    private function _getTracks(StructureItem $item, $position = Track::TOP_TRACK): array
    {
        $result = [];

        $regExpr = '/^Направл.*верхн.*/';
        if ($position !== Track::TOP_TRACK) {
            $regExpr = '/^Направл.*нижн.*/';
        }

        $hasDeeper = false;
        foreach ($item->getItems() as $_item) {
            $res = $this->_getTracks($_item, $position);
            foreach ($res as $v) {
                $hasDeeper = true;
                break;
            }
        }

        if (preg_match($regExpr, $item->getName()) === 1 && !$hasDeeper) {
            $result[] = $item;
            return $result;
        }

        foreach ($item->getItems() as $_item) {
            $res = $this->_getTracks($_item, $position);
            foreach ($res as $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    private function _getTrackBox(StructureItem $item): ?Track
    {
        $isTrack = preg_match('/^Направл.*/', $item->getName()) === 1;
        $hasItems = count($item->getItems()) > 0;
        $hasBox = $item->getBox() !== null;
        if ($isTrack && $hasBox && !$hasItems) {
            return new Track($item, $this->getAccessories());
        }

        foreach ($item->getItems() as $_item) {
            $result = $this->_getTrackBox($_item);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Брать из аксессуара, искать блок: К бокам (<- ->)
     * Примеры строк:
     * Декор профиля Fusion: Серебро матовое, Доводчики на 2 двери: К бокам (← →)
     * Доводчики на 2 двери: На все (↔ ↔), Декор профиля O: Золото матовое
     *
     * @return string|null
     */
    public function getSoftClosers(): ?string
    {
        $item = $this->getAccessoriesItem();
        if ($item === null) {
            return null;
        }

        $matches = [];
        if (preg_match('/^.*Доводчики.*\((.*)\).*/', $item->getDescription(), $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function getSoftCloser(Door $door): ?SoftCloserType
    {
        $softClosers = $this->getSoftClosers();
        if ($softClosers === null) {
            return null;
        }

        $softClosers = explode(" ", $softClosers);
        $doorIndex = null;
        foreach ($this->getDoors() as $k => $_door) {
            if ($_door === $door) {
                $doorIndex = $k;
            }
        }

        if ($doorIndex === null) {
            return null;
        }

        $symbol = $softClosers[$doorIndex] ?? null;
        if ($symbol === null) {
            return null;
        }

        return SoftCloserType::buildFromSymbol($symbol);
    }

    /**
     * Берем из названия двери купе, должно быть типа "СТД.2"
     *
     * @return DoorSystem
     */
    public function getDoorSystem(): DoorSystem
    {
        if(preg_match('/^SLIM .*/', $this->getName()) === 1){
            return new DoorSystem("SLIM", "Slim");
        }

        // "Двери-купе СТД2.Flat" -> "СТД2", "Flat"
        $matches = [];
        if (preg_match('/.* (.*)\.(.*)/', $this->getName(), $matches) === 1 && count($matches) === 3) {
            return new DoorSystem($matches[1], $matches[2]);
        }

        return new DoorSystem();
    }

    /**
     * Возвращает блок, в котором содержатся аксессуары
     *
     * @return EstimateItem|null
     */
    private function getAccessoriesItem(): ?EstimateItem
    {
        $estimateService = $this->item->getEstimateService();

        return $estimateService->findByEntityNameSku($this->getName(), "[DoorsAcc]");
    }
}
