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

final class NullLiteral implements Value, Literal, Term, Statement, Child, \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @param Token $token
     */
    private function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        Util::ensureValid($stream);

        $value = $stream->current();
        if ($value->getType() === TokenType::KEYWORD_NULL()) {
            $stream->next();
            return new self($value);
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::KEYWORD_NULL()]
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
            'type' => 'NullLiteral',
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ]
        ];
    }
}