<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<null>
 */
final class NullValue extends Value
{
    /**
     * @var null|self
     */
    private static $instance = null;

    /**
     * Private constructor
     */
    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function create()
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new self();
    }

    /**
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::false();
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if ($optional) {
            return $this;
        } else {
            return parent::get($key, $optional, $runtime);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        return $other;
    }

    /**
     * @param ListValue $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function call(ListValue $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        if ($optional) {
            return $this;
        } else {
            return parent::call($arguments, $optional, $runtime);
        }
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return null;
    }
}