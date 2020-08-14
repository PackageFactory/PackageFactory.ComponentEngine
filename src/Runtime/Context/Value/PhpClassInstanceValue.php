<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ProtectedContextAwareInterface;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends PhpValue<object>
 */
final class PhpClassInstanceValue extends PhpValue
{
    /**
     * @var object
     */
    private $object;

    /**
     * @param object $object
     */
    private function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * @param object $object
     * @return self
     */
    public static function fromObject(object $object): self
    {
        return new self($object);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if (property_exists($this->object, (string) $key->getValue())) {
            return PhpValue::fromAny($this->object->{ $key->getValue() });
        } elseif (is_callable([$this->object, (string) $key])) {
            if ($this->object instanceof ProtectedContextAwareInterface && $this->object->allowsCallOfMethod((string) $key->getValue())) {
                return PhpClassMethodValue::fromObjectAndMethodName($this->object, (string) $key->getValue());
            } else {
                throw new \RuntimeException('@TODO: Call to ' . get_class($this->object) . '->' . $key->getValue() . '() is not allowed.');
            }
        } else {
            $getter = $key->asGetter();

            if (is_callable([$this->object, $getter])) {
                try {
                    return PhpValue::fromAny($this->object->{ $getter }());
                } catch (\Throwable $err) {
                    throw new \RuntimeException('@TODO: An error occured during PHP execution: ' . $err->getMessage());
                }
            }
        }

        return NullValue::create();
    }

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->object;
    }
}