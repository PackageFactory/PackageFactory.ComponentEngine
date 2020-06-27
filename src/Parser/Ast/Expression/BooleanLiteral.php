<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class BooleanLiteral implements \JsonSerializable
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

    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);
        
        $value = $stream->current();
        if ((
            $value->getType() === TokenType::KEYWORD_TRUE() || 
            $value->getType() === TokenType::KEYWORD_FALSE()
        )) {
            $stream->next();
            return new self($value);
        } else {
            throw new \Exception('@TODO: Unexpected Token: ' . $value);
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