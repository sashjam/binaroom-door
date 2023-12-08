<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

class Box
{
    /** @var []float */
    private $min = [];

    /** @var []float */
    private $max = [];

    public function __construct(array $min, array $max)
    {
        foreach ($min as $v) {
            $this->min[] = round($v, 3);
        }
        foreach ($max as $v) {
            $this->max[] = round($v, 3);
        }
    }

    /**
     * @return float[]
     */
    public function getMin(): array
    {
        return $this->min;
    }

    /**
     * @return float[]
     */
    public function getMax(): array
    {
        return $this->max;
    }

    public function getLength(): float
    {
        return $this->max[0];
    }

    public function getWidth(): float
    {
        return $this->max[0];
    }

    public function getHeight(): float
    {
        return $this->max[1];
    }

    public function getThick(): float
    {
        return $this->max[2];
    }
}
