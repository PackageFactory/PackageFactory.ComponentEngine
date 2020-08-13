<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\TagName;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NumberValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\StringValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

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
     * @param Identifier $identifier
     * @return self
     */
    public static function fromIdentifier(Identifier $identifier): self
    {
        return new self(false, $identifier->getValue());
    }

    /**
     * @param TagName $tagName
     * @return self
     */
    public static function fromTagName(TagName $tagName): self
    {
        return new self(false, $tagName->getValue());
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
     * @param ValueInterface $value
     * @param Runtime $runtime
     * @return self
     */
    public static function fromValue(ValueInterface $value, Runtime $runtime): self
    {
        if ($value instanceof NumberValue) {
            return new self(true, (int) $value->getValue($runtime));
        } elseif ($value instanceof StringValue) {
            return new self(false, $value->getValue($runtime));
        } else {
            throw new \RuntimeException('@TODO: Illegal value as key');
        }
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