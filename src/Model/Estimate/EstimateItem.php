<?php

namespace AfSoftlab\Binaroom\Door\Model\Estimate;

class EstimateItem
{
    /** @var string */
    private $name = '';

    /** @var string */
    private $sku = '';

    /** @var string */
    private $description = '';

    /** @var float */
    private $price = 0.00;

    /** @var float */
    private $count = 0;

    /** @var int */
    private $unit = 0;

    /** @var EstimateItem[] */
    private $elements = [];

    /** @var string[] */
    private $entities = [];

    /** @var */
    private $attributes;

    /** * @var float */
    private $cost = 0.00;

    /** * @var EstimateItem|null */
    private $parent;

    /** * @var mixed */
    private $value;

    /** @var int|null */
    private $linkedItemPosition;

    /** @var int[] */
    private $linkedItemPositions = [];

    public const UNIT_RUNNING_METER = 2;
    public const UNIT_SQUARE_METER = 3;

    /**
     * @param string $name
     * @param string $sku
     * @param string $description
     * @param float $price
     * @param float $count
     * @param int $unit
     * @param EstimateItem[] $elements
     * @param string[] $entities
     * @param $attributes
     * @param float $cost
     */
    public function __construct(
        string $name,
        string $sku,
        string $description,
        float $price,
        float $count,
        int $unit,
        array $elements = [],
        array $entities = [],
        $attributes = null,
        float $cost = 0.00
    ) {
        $this->name = $name;
        $this->sku = $sku;
        $this->description = $description;
        $this->price = $price;
        $this->count = $count;
        $this->unit = $unit;
        $this->setElements($elements);
        $this->entities = $entities;
        $this->attributes = $attributes;
        $this->cost = $cost;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return self
     */
    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return self
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getCount(): float
    {
        return $this->count;
    }

    /**
     * @param float $count
     *
     * @return self
     */
    public function setCount(float $count): self
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return UnitType
     */
    public function getUnit(): UnitType
    {
        return UnitType::build($this->unit);
    }

    /**
     * @param int $unit
     *
     * @return self
     */
    public function setUnit(int $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return EstimateItem[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param EstimateItem[] $elements
     *
     * @return self
     */
    public function setElements(array $elements): self
    {
        foreach ($elements as $element) {
            $element->setParent($this);
        }

        $this->elements = $elements;

        return $this;
    }

    /**
     * @return EstimateItem|null
     */
    public function getParent(): ?EstimateItem
    {
        return $this->parent;
    }

    /**
     * @param EstimateItem|null $parent
     *
     * @return self
     */
    public function setParent(?EstimateItem $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param string[] $entities
     *
     * @return self
     */
    public function setEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     *
     * @return self
     */
    public function setAttributes($attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     *
     * @return self
     */
    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getLastSkuPart()
    {
        $sku = explode(".", $this->sku);

        return end($sku);
    }

    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLinkedItemPosition(): ?int
    {
        return $this->linkedItemPosition;
    }

    /**
     * @param int|null $linkedItemPosition
     *
     * @return self
     */
    public function setLinkedItemPosition(?int $linkedItemPosition): self
    {
        $this->linkedItemPosition = $linkedItemPosition;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getLinkedItemPositions(): array
    {
        return $this->linkedItemPositions;
    }

    /**
     * @param int $linkedItemPosition
     *
     * @return self
     */
    public function addLinkedItemPosition($linkedItemPosition): self
    {
        $this->linkedItemPositions[] = $linkedItemPosition;

        return $this;
    }

    /**
     * @return EstimateItem[]
     */
    public function getMaterials(): array
    {
        $res = [];
        $units = [
            UnitType::RUNNING_METER(),
            UnitType::SQUARE_METER()
        ];

        foreach ($this->getElements() as $subItem) {
            if (in_array($subItem->getUnit(), $units)) {
                $res[] = $subItem;
            }
        }

        return $res;
    }
}
