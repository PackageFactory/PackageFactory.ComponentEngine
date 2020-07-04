<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Term;

final class ChainSegment implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $isOptional;

    /**
     * @var Key
     */
    private $key;

    /**
     * @var null|Call
     */
    private $call;

    /**
     * @param boolean $isOptional
     * @param Key $key
     * @param null|Call $call
     */
    private function __construct(
        bool $isOptional,
        Key $key,
        ?Call $call
    ) {
        $this->isOptional = $isOptional;
        $this->key = $key;
        $this->call = $call;
    }

    /**
     * @param boolean $isOptional
     * @param Key $key
     * @return self
     */
    public static function createFromKey(
        bool $isOptional,
        Key $key
    ): self {
        return new self($isOptional, $key, null);
    }

    /**
     * @return bool
     */
    public function getIsOptional(): bool
    {
        return $this->isOptional;
    }

    /**
     * @return bool
     */
    public function getIsCallable(): bool
    {
        return $this->call !== null;
    }

    /**
     * @return Term
     */
    public function getKey(): Term
    {
        /** @var Term $key */
        $key = $this->key;
        return $key;
    }

    /**
     * @return null|Call
     */
    public function getCall(): ?Call
    {
        return $this->call;
    }

    /**
     * @param Call $call
     * @return self
     */
    public function withCall(Call $call): self
    {
        return new self($this->isOptional, $this->key, $call);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ChainSegment',
            'isOptional' => $this->isOptional,
            'key' => $this->key,
            'call' => $this->call
        ];
    }
} 