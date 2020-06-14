<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Identifier implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var string
     */
    private $value;

    /**
     * @param Token $token
     * @param string $value
     */
    private function __construct(
        Token $token,
        string $value
    ) {
        $this->token = $token;
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
            $token = $stream->current();
            $stream->next();

            return new self($token, $token->getValue());
        }
        else {
            throw new \Exception('@TODO: Unexpected Token');
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
            'type' => 'Identifier',
            'properties' => [
                'token' => $this->token,
                'value' => $this->value
            ]
        ];
    }
}