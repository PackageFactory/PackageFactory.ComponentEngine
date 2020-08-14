<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @extends PhpValue<array{object, string}>
 */
final class PhpClassMethodValue extends PhpValue
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @param object $object
     * @param string $methodName
     */
    private function __construct(object $object, string $methodName)
    {
        $this->object = $object;
        $this->methodName = $methodName;
    }

    /**
     * @param object $object
     * @param string $methodName
     * @return self
     */
    public static function fromObjectAndMethodName(object $object, string $methodName): self
    {
        if (is_callable([$object, $methodName])) {
            return new self($object, $methodName);
        } else {
            throw new \RuntimeException('@TODO: ' . get_class($object) . '->' . $methodName . '() is not callable.');
        }
    }

    /**
     * @return array{object, string}
     */
    public function getValue()
    {
        return [$this->object, $this->methodName];
    }
}