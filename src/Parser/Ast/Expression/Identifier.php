<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Identifier implements \JsonSerializable
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
    public static function createFromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ($value->getType() === TokenType::IDENTIFIER()) {
            $stream->next();
            return new self($value);
        } else {
            throw new \Exception('@TODO: Unexpected Token: ' . $value);
        }
    }

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
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
            'type' => 'Identifier',
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ],
            'value' => $this->token->getValue()
        ];
    }
}