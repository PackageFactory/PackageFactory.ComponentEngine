<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ArrayLiteral implements \JsonSerializable
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
     * @var array<int, Operand>
     */
    private $items;

    /**
     * @param Token $start
     * @param Token $end
     * @param array<int, Operand> $items
     */
    private function __construct(
        Token $start,
        Token $end,
        array $items
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->items = $items;
    }

    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        Util::expect($stream, TokenType::BRACKETS_SQUARE_OPEN());

        $items = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_SQUARE_CLOSE():
                    $end = $stream->current();
                    $stream->next();
                    return new self($start, $end, $items);

                default:
                    $items[] = Expression::createFromTokenStream($stream);
            }

            Util::skipWhiteSpaceAndComments($stream);

            if ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
            } else {
                Util::skipWhiteSpaceAndComments($stream);
                $end = Util::expect($stream, TokenType::BRACKETS_SQUARE_CLOSE());
                return new self($start, $end, $items);
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
     * @return array<int, Operand>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ArrayLiteral',
            'offset' => [
                $this->start->getStart()->getIndex(),
                $this->end->getEnd()->getIndex()
            ],
            'items' => $this->items
        ];
    }
}