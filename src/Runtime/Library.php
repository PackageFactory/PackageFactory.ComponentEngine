<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Runtime\Context\Value\CallableValue;

final class Library
{
    /**
     * @var array<string, array<string, callable>>
     */
    private $methods;

    /**
     * @param array<string, array<string, callable>> $methods
     */
    private function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self([]);
    }

    /**
     * @param string $typeName
     * @param string $methodName
     * @return boolean
     */
    public function hasMethod(string $typeName, string $methodName): bool
    {
        return isset($this->methods[$typeName][$methodName]);
    }

    /**
     * @param string $typeName
     * @param string $methodName
     * @return CallableValue
     */
    public function getMethod(string $typeName, string $methodName): CallableValue
    {
        if (!$this->hasMethod($typeName, $methodName)) {
            throw new \Exception('@TODO: Cannot retrieve undefined method!');
        }

        return CallableValue::fromCallable($this->methods[$typeName][$methodName]);
    }

    /**
     * @param string $typeName
     * @param string $methodName
     * @param callable $method
     * @return self
     */
    public function withAddedMethod(string $typeName, string $methodName, callable $method): self
    {
        if ($this->hasMethod($typeName, $methodName)) {
            throw new \Exception('@TODO: Method already exists!');
        }

        $methods = $this->methods;
        $methods[$typeName][$methodName] = $method;

        return new self($methods);
    }

    /**
     * @param string $typeName
     * @param string $methodName
     * @param callable $method
     * @return self
     */
    public function withOverridenMethod(string $typeName, string $methodName, callable $method): self
    {
        if (!$this->hasMethod($typeName, $methodName)) {
            throw new \Exception('@TODO: Method does not exist!');
        }

        $methods = $this->methods;
        $methods[$typeName][$methodName] = $method;

        return new self($methods);
    }

    /**
     * @param string $typeName
     * @param string $methodName
     * @return self
     */
    public function withoutMethod(string $typeName, string $methodName): self
    {
        if ($this->hasMethod($typeName, $methodName)) {
            $methods = $this->methods;
            unset($methods[$typeName][$methodName]);
    
            return new self($methods);
        } else {
            return $this;
        }
    }
}