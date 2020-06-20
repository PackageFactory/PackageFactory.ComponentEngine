<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Negation implements \JsonSerializable
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
        if ($value->getType() === TokenType::OPERATOR_LOGICAL_NOT()) {
            $stream->next();
            return new self(
                $value,
                Expression::createAtomFromTokenStream($stream)
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
     * @return boolean
     */
    public function evaluate(Context $context = null): bool
    {
        return !$this->subject->evaluate($context);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Negation',
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ],
            'subject' => $this->subject
        ];
    }
}