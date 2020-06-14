<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Chain implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $start;

    /**
     * @var Token
     */
    private $end;

    /**
     * @var array|string[]
     */
    private $elements;

    /**
     * @param Token $start
     * @param Token $end
     * @param array|string[] $elements
     */
    public function __construct(
        Token $start,
        Token $end,
        array $elements
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->elements = $elements;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        $elements = [];
        while ($stream->valid()) {
            if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                $elements[] = $stream->current()->getValue();
                $stream->next();
            }
            else {
                throw new \Exception('@TODO: Unexpected Token');
            }

            if ($stream->current()->getType() === TokenType::PERIOD()) {
                $stream->next();
            }
            else {
                break;
            }
        }

        return new self($start, $stream->current(), $elements);
    }

    /**
     * @return Token
     */
    public function getStart(): Token
    {
        return $this->start;
    }

    /**
     * @return Token
     */
    public function getEnd(): Token
    {
        return $this->end;
    }

    /**
     * @return array|string[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Chain',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'elements' => $this->elements
            ]
        ];
    }
}