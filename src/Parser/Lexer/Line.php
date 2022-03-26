<?php

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Lexer;

/**
 * @implements \IteratorAggregate<mixed, Token>
 */
final class Line implements \IteratorAggregate
{
    /**
     * @var int
     */
    private readonly int $number;

    /**
     * @var array|Token[]
     */
    private array $tokens;

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
     * @param integer $number
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(int $number, TokenStream $stream): self
    {
        $tokens = [];
        while ($stream->valid()) {
            $token = $stream->current();
            $stream->next();

            if ($token->getType() === TokenType::END_OF_LINE()) {
                break;
            } else {
                $tokens[] = $token;
            }
        }

        return new self($number, $tokens);
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return array|Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return \Traversable<Token>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->tokens);
    }
}
