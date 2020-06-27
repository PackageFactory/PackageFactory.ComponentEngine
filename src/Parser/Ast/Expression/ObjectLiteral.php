<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

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
     * @var array<int, ObjectLiteralProperty>
     */
    private $properties;

    /**
     * @param Token $start
     * @param Token $end
     * @param array<int, ObjectLiteralProperty> $properties
     */
    private function __construct(
        Token $start,
        Token $end,
        array $properties
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->properties = $properties;
    }

    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        Util::expect($stream, TokenType::BRACKETS_CURLY_OPEN());

        $properties = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);

            switch ($stream->current()->getType()) {
                case TokenType::COMMA():
                    $stream->next();
                    break;

                case TokenType::BRACKETS_CURLY_CLOSE():
                    $end = $stream->current();
                    $stream->next();
                    return new self($start, $end, $properties);
                
                default:
                    $properties[] = ObjectLiteralProperty::createFromTokenStream($stream);
                    break;
            }
        }
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
     * @return array<int, ObjectLiteralProperty>
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
            'offset' => [
                $this->start->getStart()->getIndex(),
                $this->end->getEnd()->getIndex()
            ],
            'properties' => $this->properties
        ];
    }
}