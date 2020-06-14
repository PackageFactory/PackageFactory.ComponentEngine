<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ObjectLiteral implements \JsonSerializable
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
     * @var array|Property[]
     */
    private $properties;

    /**
     * @param Token $start
     * @param Token $end
     * @param array|Property[] $properties
     */
    public function __construct(
        Token $start,
        Token $end,
        array $properties
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->properties = $properties;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        Util::expect($stream, TokenType::OBJECT_START());

        $properties = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);

            if ($stream->current()->getType() === TokenType::OBJECT_END()) {
                break;
            }

            $properties[] = Property::createFromTokenStream($stream);

            Util::skipWhiteSpaceAndComments($stream);
            if ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
            }
        }

        Util::skipWhiteSpaceAndComments($stream);

        $end = $stream->current();
        Util::expect($stream, TokenType::OBJECT_END());

        return new self($start, $end, $properties);
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
     * @return array|Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ObjectLiteral',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'properties' => $this->properties
            ]
        ];
    }
}