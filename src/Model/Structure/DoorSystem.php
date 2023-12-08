<?php

namespace AfSoftlab\Binaroom\Door\Model\Structure;

class DoorSystem
{
    /** @var string|null */
    private $type = null;

    /** @var string|null */
    private $style = null;

    public function __construct(?string $type = null, ?string $style = null)
    {
        $this->type = $type;
        $this->style = $style;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getStyle(): ?string
    {
        return $this->style;
    }
}
