<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class StringLiteral implements \JsonSerializable
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

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);
        
        $start = $stream->current();
        Util::expect($stream, TokenType::STRING_START());

        $value = '';
        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::STRING_VALUE():
                    $value .= $stream->current()->getValue();
                    $stream->next();
                break;

                case TokenType::STRING_ESCAPE():
                    $stream->next();
                break;

                case TokenType::STRING_ESCAPED_CHARACTER():
                    $value .= $stream->current()->getValue();
                    $stream->next();
                break;

                case TokenType::STRING_END():
                    break 2;

                default:
                    throw new \Exception('@TODO: Unexpected Token');
            }
        }

        $end = $stream->current();
        Util::expect($stream, TokenType::STRING_END());

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
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'value' => $this->value
            ]
        ];
    }
}