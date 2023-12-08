<?php

namespace AfSoftlab\Binaroom\Door\Model\Estimate;

class EstimateItemDetail
{
    private $uid;

    private $value;

    private $quantity = 0;

    /**
     * @param $uid
     * @param $value
     * @param $quantity
     */
    public function __construct($uid, $value, $quantity = 0)
    {
        $this->uid = $uid;
        $this->value = $value;
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int|mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int|mixed $quantity
     *
     * @return self
     */
    public function setQuantity($quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}
