<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;

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
    private function __construct(
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
        
        $value = $stream->current();
        if ($value->getType() === TokenType::OPERATOR_SPREAD()) {
            $stream->next();
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file: ' . $value);
            } elseif ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                $root = Identifier::createFromTokenStream($stream);
                return new self(
                    $value,
                    Chain::createFromTokenStream($root, $stream)
                );
            } else {
                throw new \Exception('@TODO: Unexpected Token: ' . $value);
            }
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
     * @return mixed
     */
    public function evaluate(Context $context = null)
    {
        return $this->subject->evaluate($context);
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