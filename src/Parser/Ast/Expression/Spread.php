<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Spread implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Operand
     */
    private $subject;

    /**
     * @param Token $token
     * @param Operand $subject
     */
    private function __construct(
        Token $token,
        $subject
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
        
        $value = $stream->current();
        if ($value->getType() === TokenType::OPERATOR_SPREAD()) {
            $stream->next();
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file: ' . $value);
            }

            return new self(
                $value,
                Expression::createFromTokenStream($stream)
            );
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
     * @return Operand
     */
    public function getSubject()
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
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ],
            'subject' => $this->subject
        ];
    }
}