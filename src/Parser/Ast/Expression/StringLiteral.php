<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Value;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class StringLiteral implements Value, Literal, Term, Statement, Key, Child, \JsonSerializable
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
     * @var string
     */
    private $value;

    /**
     * @param Token $start
     * @param Token $end
     * @param string $value
     */
    private function __construct(
        Token $start,
        Token $end,
        string $value
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->value = $value;
    }

    public static function createFromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        Util::expect($stream, TokenType::STRING_LITERAL_START());
        Util::ensureValid($stream);

        $value = '';
        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::STRING_LITERAL_CONTENT():
                    $value .= $stream->current()->getValue();
                    $stream->next();
                break;

                case TokenType::STRING_LITERAL_ESCAPE():
                    $stream->next();
                break;

                case TokenType::STRING_LITERAL_ESCAPED_CHARACTER():
                    $value .= $stream->current()->getValue();
                    $stream->next();
                break;

                case TokenType::STRING_LITERAL_END():
                    break 2;

                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        $end = $stream->current();
        Util::expect($stream, TokenType::STRING_LITERAL_END());

        return new self($start, $end, $value);
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
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'StringLiteral',
            'offset' => [
                $this->start->getStart()->getIndex(),
                $this->end->getEnd()->getIndex()
            ],
            'value' => $this->value
        ];
    }
}