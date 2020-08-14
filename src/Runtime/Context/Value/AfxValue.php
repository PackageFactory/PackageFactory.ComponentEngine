<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

/**
 * @extends Value<ComponentInterface>
 */
final class AfxValue extends Value
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
     * @return ComponentInterface
     */
    public function getValue()
    {
        return $this->component;
    }
}