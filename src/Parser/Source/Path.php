<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Source;

final class Path implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new \Exception('@TODO: Invalid path');
        }

        $this->value = $value;
    }

    /**
     * @return self
     */
    public static function createMemory(): self
    {
        return new self(':memory:');
    }

    /**
     * @param string $data
     * @return self
     */
    public static function fromString(string $data): self
    {
        return new self($data);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isMemory(): bool
    {
        return $this->value === ':memory:';
    }

    /**
     * @return bool
     */
    public function isRelative(): bool
    {
        return $this->value[0] === '.';
    }

    /**
     * @return bool
     */
    public function isAbsolute(): bool
    {
        return $this->value[0] === '/';
    }

    /**
     * @param Path $target
     * @return self
     */
    public function getRelativePathTo(Path $target): self
    {
        if ($this->isMemory()) {
            throw new \Exception('@TODO: Cannot create relative path for :memory:');
        }
        elseif ($this->isRelative() && $target->isAbsolute()) {
            throw new \Exception('@TODO: Cannot create relative path from realtive source to asbolute target.');
        }
        elseif ($this->isAbsolute() && $target->isAbsolute()) {
            $dirname = dirname($this->value);
            if (substr($target->getValue(), 0, strlen($dirname)) === $dirname) {
                return new self('.' . substr($target->getValue(), strlen($dirname)));
            }
            else {
                throw new \Exception('@TODO: Cannot create relative path due to incompatible absolute paths.');
            }
        }
        else {
            $dirname = dirname($this->value);
            $resultSegments = explode('/', $dirname);
            $overflowSegments = [];
            $targetSegments = explode('/', $target->getValue());

            foreach ($targetSegments as $segment) {
                if ($segment === '.' || $segment === '') {
                    // ignore
                }
                elseif ($segment === '..') {
                    if (count($resultSegments)) {
                        array_pop($resultSegments);
                    }
                    else {
                        $overflowSegments[] = $segment;
                    }
                }
                else {
                    $resultSegments[] = $segment;
                }
            }

            return new self(implode('/', [...$overflowSegments, ...$resultSegments]));
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}