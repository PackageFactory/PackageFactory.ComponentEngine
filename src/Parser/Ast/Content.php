<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Content implements \JsonSerializable
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
        $start = $stream->current();
        $value = '';
        while (
            $stream->valid() && 
            (
                $stream->current()->getType() === TokenType::WHITESPACE() ||
                $stream->current()->getType() === TokenType::END_OF_LINE()
            )
        ) {
            $stream->next();
            $start = $stream->current();
            $value = ' ';
        }

        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::CONTENT():
                    $value .= $stream->current()->getValue();
                    $stream->next();
                break;

                case TokenType::WHITESPACE():
                case TokenType::END_OF_LINE():
                    Util::skipWhiteSpaceAndComments($stream);
                    $value .= ' ';
                break;

                case TokenType::EXPRESSION_START():
                case TokenType::TAG_START():
                    break 2;

                default:
                    throw new \Exception('@TODO: Unexpected Token');
            }
        }

        return new self($start, $stream->current(), $value);
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
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Content',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'value' => $this->value
            ]
        ];
    }
}