<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ObjectLiteral implements Literal, Spreadable, Term, Statement, \JsonSerializable
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
     * @var array|ObjectLiteralProperty[]
     */
    private $properties;

    /**
     * @param Token $start
     * @param Token $end
     * @param array|ObjectLiteralProperty[] $properties
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

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        Util::ensureValid($stream);

        $start = $stream->current();
        $end = $stream->current();
        Util::expect($stream, TokenType::BRACKETS_CURLY_OPEN());

        $properties = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);

            switch ($stream->current()->getType()) {
                case TokenType::COMMA():
                    $stream->next();
                    break;

                case TokenType::BRACKETS_CURLY_CLOSE():
                    $end = $stream->current();
                    $stream->next();
                    break 2;
                
                default:
                    $properties[] = ObjectLiteralProperty::fromTokenStream($stream);
                    break;
            }
        }

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