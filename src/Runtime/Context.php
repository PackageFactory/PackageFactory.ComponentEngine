<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

final class Context
{
    /**
     * @var array<string, mixed>
     */
    protected $properties;

    /**
     * @param array<string, mixed> $properties
     */
    private function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return self
     */
    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @param string $propertyName
     * @return mixed
     */
    public function getProperty(string $propertyName)
    {
        if (!isset($this->properties[$propertyName])) {
            throw new \Exception('@TODO: unknown context property ' . $propertyName);
        }

        return $this->properties[$propertyName];
    }

    /**
     * @param string $propertyName
     * @return boolean
     */
    public function hasProperty(string $propertyName)
    {
        return isset($this->properties[$propertyName]);
    }

    /**
     * @param array<string, mixed> $newProperties
     * @return self
     */
    public function withMergedProperties(array $newProperties): self
    {
        $nextProperties = $this->properties;

        foreach ($newProperties as $key => $value) {
            $nextProperties[$key] = $value;
        }

        return new self($nextProperties);
    }
}