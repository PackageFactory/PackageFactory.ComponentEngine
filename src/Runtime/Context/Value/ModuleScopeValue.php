<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Module\OnConstant;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Module\OnImport;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<void>
 */
final class ModuleScopeValue extends Value
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var ValueInterface<mixed>
     */
    private $fallback;

    /**
     * @var Runtime
     */
    private $runtime;

    /**
     * @param Module $module
     */
    private function __construct(Module $module)
    {
        $this->module = $module;
        $this->fallback = DictionaryValue::empty();
    }

    /**
     * @param Module $module
     * @return self
     */
    public static function fromModule(Module $module): self
    {
        return new self($module);
    }

    /**
     * @param Runtime $runtime
     * @return Runtime
     */
    public function bindRuntime(Runtime $runtime): Runtime
    {
        return $this->runtime = $runtime;
    }

    /**
     * @return ValueInterface<mixed>
     */
    public function getFallback(): ValueInterface
    {
        return $this->fallback;
    }

    /**
     * @param Key $key
     * @param boolean $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if ($this->module->hasConstant((string) $key)) {
            return OnConstant::evaluate($this->runtime, $this->module->getConstant((string) $key));
        } elseif ($this->module->hasImport((string) $key)) {
            return OnImport::evaluate($this->runtime, $this->module, $this->module->getImport((string) $key));
        } else {
            return $this->fallback->get($key, $optional, $runtime);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        if ($other instanceof DictionaryValue) {
            $this->fallback = $this->fallback->merge($other);
            return $this;
        } elseif ($other instanceof ModuleScopeValue) {
            return $this->merge($other->getFallback());
        } else {
            return parent::merge($other);
        }
    }
}