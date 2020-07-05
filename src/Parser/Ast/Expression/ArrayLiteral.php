<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ArrayLiteral implements 
    Literal,
    Spreadable,
    Term,
    Statement,
    Child,
    \JsonSerializable
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
     * @var array|Statement[]
     */
    private $items;

    /**
     * @param Token $start
     * @param Token $end
     * @param array|Statement[] $items
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

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        $end = $stream->current();
        $stream->consume(TokenType::BRACKETS_SQUARE_OPEN());

        $items = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_SQUARE_CLOSE():
                    $end = $stream->current();
                    $stream->next();
                    break 2;

                default:
                    $items[] = ExpressionParser::parseStatement(
                        $stream, 
                        ExpressionParser::PRIORITY_LIST
                    );
                    break;
            }

            $stream->skipWhiteSpaceAndComments();

            if ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
            } else {
                $stream->skipWhiteSpaceAndComments();
                $end = $stream->consume(TokenType::BRACKETS_SQUARE_CLOSE());
                break;
            }
        }

        return new self($start, $end, $items);
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
     * @return array|Statement[]
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