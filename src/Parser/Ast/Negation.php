<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Negation implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Chain
     */
    private $subject;

    /**
     * @param Token $token
     * @param Chain $subject
     */
    public function __construct(
        Token $token,
        Chain $subject
    ) {
        $this->token = $token;
        $this->subject = $subject;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        Util::expect($stream, TokenType::EXCLAMATION());

        $subject = Chain::createFromTokenStream($stream);

        return new self($start, $subject);
    }

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @return Chain
     */
    public function getSubject(): Chain
    {
        return $this->subject;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Negation',
            'properties' => [
                'token' => $this->token,
                'subhect' => $this->subject
            ]
        ];
    }
}