<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

class Position
{
    /** @var float[] */
    private $matrix = [];

    private const X = 12;
    private const Y = 13;
    private const Z = 14;

    /**
     * @param float[] $matrix
     */
    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
    }

    /**
     * @return float[]
     */
    public function getPosition(): array
    {
        return [
            $this->getX(),
            $this->getY(),
            $this->getZ(),
        ];
    }

    public function getX(): float
    {
        return $this->matrix[self::X];
    }

    public function getY(): float
    {
        return $this->matrix[self::Y];
    }

    public function getZ(): float
    {
        return $this->matrix[self::Z];
    }

    public function sum(Position $pos): self
    {
        $this->matrix[self::X] += $pos->getX();
        $this->matrix[self::Y] += $pos->getY();
        $this->matrix[self::Z] += $pos->getZ();

        return $this;
    }

    public function equalByXY(Position $pos): bool
    {
        return
            $this->getX() == $pos->getX() &&
            $this->getY() == $pos->getY();
    }

}
