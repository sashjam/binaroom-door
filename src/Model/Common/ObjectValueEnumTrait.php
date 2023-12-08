<?php

namespace AfSoftlab\Binaroom\Door\Model\Common;

trait ObjectValueEnumTrait
{
    protected $code;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        if (!array_key_exists($code, $this->getAvailable())) {
            throw new \InvalidArgumentException("Incorrect type code: {$code}");
        }

        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->getAvailable()[$this->code];
    }

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        $result = [];
        foreach (self::getAvailable() as $code => $_) {
            $result[] = new self($code);
        }

        return $result;
    }

    /**
     * @return string[]
     */
    abstract protected static function getAvailable(): array;
}
