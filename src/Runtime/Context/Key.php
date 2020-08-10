<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

final class Key
{
    /**
     * @var bool
     */
    private $numeric;

    /**
     * @var string|int
     */
    private $value;

    /**
     * @param boolean $numeric
     * @param string|int $value
     */
    private function __construct(bool $numeric, $value)
    {
        $this->numeric = $numeric;
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
    {
        return new self(false, $value);
    }

    /**
     * @param int $value
     * @return self
     */
    public static function fromInteger(int $value): self
    {
        return new self(true, $value);
    }

    /**
     * @return boolean
     */
    public function isNumeric(): bool
    {
        return $this->numeric;
    }

    /**
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function asGetter(): string
    {
        return 'get' . ucfirst((string) $this->value);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}