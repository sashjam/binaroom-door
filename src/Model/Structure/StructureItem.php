<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

use AfSoftlab\Binaroom\Door\Service\EstimateService;

class StructureItem
{
    /** @var string */
    private $uid;

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var string */
    private $sku;

    /** @var StructureItem[] */
    private $items = [];

    /** @var StructureItem|null */
    private $parent;

    /** @var int */
    private $level = 0;

    /** @var float[] */
    private $matrix = [];

    /** @var Box|null */
    private $box = null;

    /** @var integer|null */
    private $wall = null;

    /**
     * @var string[]|null
     */
    private $allUids = null;

    /** @var EstimateService|null */
    private $estimateService;

    /**
     * @param string $uid
     * @param string $name
     * @param string $type
     * @param string $sku
     * @param StructureItem[] $items
     * @param int $level
     * @param float[] $matrix
     * @param array $box
     * @param null|integer $wall
     */
    public function __construct(
        string $uid,
        string $name,
        string $type,
        ?string $sku,
        array $items,
        $level = 0,
        $matrix = [],
        $box = null,
        $wall = null,
        ?EstimateService $estimateService = null
    ) {
        $this->uid = $uid;
        $this->name = $name;
        $this->type = $type;
        $this->sku = $sku;
        $this->items = $items;
        $this->level = $level;
        $this->wall = $wall;
        $this->estimateService = $estimateService;

        foreach ($matrix as $v) {
            $this->matrix[] = round($v, 3);
        }

        if ($box) {
            $this->box = new Box($box['min'], $box['max']);
        }
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return StructureItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(StructureItem $item): self
    {
        $item->addParent($this);
        $this->items[] = $item;

        return $this;
    }

    private function addParent(StructureItem $item): self
    {
        $this->parent = $item;

        return $this;
    }

    public function getParent(): ?StructureItem
    {
        return $this->parent;
    }

    /**
     * Возвращает путь из названий элементов структуры
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        $res = [];

        $item = $this;
        while ($item = $item->getParent()) {
            $res[] = $item->getName() ?: "{noname}";
        }

        $res = array_reverse($res);

        return implode("//", $res);
    }

    /**
     * Возвращает путь из названий элементов структуры
     *
     * @return string|null
     */
    public function getPathUid(): ?string
    {
        $res = [];

        $item = $this;
        while ($item = $item->getParent()) {
            $res[] =  $item->getUid() ?: "{noname}";
        }

        $res = array_reverse($res);

        return implode(" / ", $res);
    }

    /**
     * @return string
     */
    public function getIndexPath(): string
    {
        $res = [];
        $res[] = $this->_getIndex();

        $item = $this;
        while ($item = $item->getParent()) {
            $res[] = $item->_getIndex();
        }

        $res = array_reverse($res);

        return implode(".", $res);
    }

    /**
     * Находит порядковый номер элемента среди братьев
     *
     * @return int
     */
    public function _getIndex(): int
    {
        $parent = $this->getParent();
        if ($parent === null) {
            return 1;
        }

        foreach ($parent->getItems() as $i => $_item) {
            if ($_item === $this) {
                return $i + 1;
            }
        }

        return 1;
    }


    public function getEstimateService(): EstimateService
    {
        if ($this->estimateService !== null) {
            return $this->estimateService;
        }

        if ($this->getParent() !== null) {
            return $this->getParent()->getEstimateService();
        }

        throw new \LogicException("EstimateService not found");
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param StructureItem[] $items
     *
     * @return self
     */
    public function setItems(array $items): self
    {
        $this->items = [];
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * @return DoorKit[] Находит все комплекты дверей
     */
    public function getDoorKits(): array
    {
        return $this->_getDoorKits($this);
    }

    private function _getDoorKits(StructureItem $item): array
    {
        $doorKits = [];

        $names = [
            '/^Sliding doors STD3\.C.*/',
            '/^Двери-купе .*/',
            '/^Дверь-купе .*/',
            '/^Двери распашные .*/',
            '/^Двери поворотные .*/',
            '/^SLIM .*/',
        ];
        if ($item->getItems() && $this->matchAny($names, $item->getName())) {
            $doorKits[] = new DoorKit($item);

            return $doorKits;
        }

        foreach ($item->getItems() as $_item) {
            $_doorKits = $this->_getDoorKits($_item);
            foreach ($_doorKits as $doorKit) {
                $doorKits[] = $doorKit;
            }
        }

        return $doorKits;
    }

    /**
     * @param []string $patterns
     *
     * @return bool
     */
    private function matchAny(array $patterns, $subject): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return float[]
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * @param float[] $matrix
     *
     * @return self
     */
    public function setMatrix($matrix): self
    {
        $this->matrix = $matrix;

        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function isWall(): bool
    {
        return $this->wall !== null;
    }

    public function getWidth(): float
    {
        return $this->getSizes()[0] ?? 0;
    }

    public function getHeight(): float
    {
        return $this->getSizes()[1] ?? 0;
    }

    public function getThick(): float
    {
        return $this->getSizes()[2] ?? 0;
    }

    public function getPosition(): Position
    {
        return new Position($this->matrix);
    }

    /**
     * @return float[]
     */
    protected function getSizes(): array
    {
        $box = $this->box;
        if ($box == null) {
            return [];
        }

        $x = 0;
        $y = 1;
        $z = 2;

        $with = $box->getMax()[$x] - $box->getMin()[$x];
        $height = $box->getMax()[$y] - $box->getMin()[$y];
        $thick = $box->getMax()[$z] - $box->getMin()[$z];

        return [$with, $height, $thick];
    }

    /**
     * @return Position
     */
    public function getRelativePosition($to): Position
    {
        $position = $this->getPosition();
        if ($this->getParent() === null || $this->getParent()->getUid() === $to->getUid()) {
            return $position;
        }

        $parentPosition = $this->getParent()->getRelativePosition($to);

        return $parentPosition->sum($position);
    }

    public function setBox(?Box $box): self
    {
        $this->box = $box;

        return $this;
    }

    public function getAllUids(): array
    {
        if ($this->allUids !== null) {
            return $this->allUids;
        }
        $this->allUids = array_unique(
            $this->_getAllUids($this)
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

    public function __toString(): string
    {
        $parent = $this->getParent() ? $this->getParent()->getName() : "null";

        return "{$this->getName()} - {$this->getUid()} - {$parent} <br />";
    }

    public function print($level = 0): string
    {
        $items = [];

        $_level = $level + 1;
        foreach ($this->getItems() as $item) {
            $items[] = $item->print($_level);
        }

        $name = $this->getName();
        $name = strtr($name, [
            //			'💣' => '',
            //			'(костыль min эластичности по ширине)' => '',
        ]);

        $name = empty($name) ? $this->type : $name;

        $m = [
            $this->getMatrix()[12],
            $this->getMatrix()[13],
            $this->getMatrix()[14],
        ];

        $matrix = implode(",", $m);
        $boxMin = implode(", ", $this->getBox()->getMin());
        $boxMax = implode(", ", $this->getBox()->getMax());
        //		$matrix = "";
        //		$boxMin = "";
        //		$boxMax = "";

        return str_repeat(" – ",
                $level) . "{$name} {$matrix} / {$boxMin} / {$boxMax}<br />" . implode(" ", $items);
    }
}
