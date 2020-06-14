<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;

final class Spread implements \JsonSerializable
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
        throw new \Exception('SPREAD');
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
            'type' => 'Spread',
            'properties' => [
                'token' => $this->token,
                'subject' => $this->subject
            ]
        ];
    }
}