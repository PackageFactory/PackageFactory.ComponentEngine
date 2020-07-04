<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

use ArrayIterator;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Line implements \IteratorAggregate
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var array|Token[]
     */
    private $tokens;

    /**
     * @param int $number
     * @param array|Token[] $tokens
     */
    private function __construct(int $number, array $tokens)
    {
        $this->number = $number;
        $this->tokens = $tokens;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return array<Token>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return \Traversable<Token>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->tokens);
    }
}