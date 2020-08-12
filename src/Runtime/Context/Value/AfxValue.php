<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

final class AfxValue implements ValueInterface
{
    /**
     * @var ComponentInterface
     */
    private $component;

    /**
     * @param ComponentInterface $component
     */
    private function __construct(ComponentInterface $component)
    {
        $this->component = $component;
    }

    /**
     * @param ComponentInterface $component
     * @return self
     */
    public static function fromComponent(ComponentInterface $component): self
    {
        return new self($component);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value has no children');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be merged with ' . get_class($other));
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue
    {
        return BooleanValue::fromBoolean($this->component === $other->getValue());
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Afx Value does not allow modulo operation');
    }

    /**
     * @return bool
     */
    public function isTrueish(): bool
    {
        return true;
    }

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->component;
    }
}