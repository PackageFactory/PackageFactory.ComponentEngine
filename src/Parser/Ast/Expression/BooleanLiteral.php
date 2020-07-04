<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Value;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class BooleanLiteral implements Value, Literal, Term, Statement, \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var bool
     */
    private $boolean;

    private function __construct(Token $token)
    {
        $this->token = $token;
        $this->boolean = $token->getValue() === 'true' ? true : false;
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ((
            $value->getType() === TokenType::KEYWORD_TRUE() || 
            $value->getType() === TokenType::KEYWORD_FALSE()
        )) {
            $stream->next();
            return new self($value);
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [
                    TokenType::KEYWORD_TRUE(),
                    TokenType::KEYWORD_FALSE()
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->token->getValue();
    }

    /**
     * @return bool
     */
    public function getBoolean(): bool
    {
        return $this->boolean;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->token->getValue();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'BooleanLiteral',
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ],
            'value' => $this->token->getValue()
        ];
    }
}