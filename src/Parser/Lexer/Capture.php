<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Fragment;

final class Capture
{
    /**
     * @var null|Fragment
     */
    private $fragment;

    /**
     * @param null|Fragment $fragment
     */
    private function __construct(?Fragment $fragment = null)
    {
        $this->fragment = $fragment;
    }

    /**
     * @return self
     */
    public static function createEmpty(): self 
    {
        return new self(null);
    }

    /**
     * @param Fragment $fragment
     * @return self
     */
    public static function fromFragment(Fragment $fragment): self 
    {
        return new self($fragment);
    }

    /**
     * @param Fragment $add
     * @return void
     */
    public function append(Fragment $add): void
    {
        if ($this->fragment === null) {
            $this->fragment = $add;
        } else {
            $this->fragment = $this->fragment->append($add);
        }
    }

    /**
     * @param TokenType $tokenType
     * @return \Iterator<Token>
     */
    public function flush(TokenType $tokenType): \Iterator
    {
        if ($this->fragment !== null) {
            yield Token::fromFragment($tokenType, $this->fragment);
            $this->fragment = null;
        }
    }
}