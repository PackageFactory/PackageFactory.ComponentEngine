<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Negation implements Term, Statement, \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Term
     */
    private $subject;

    /**
     * @param Token $token
     * @param Term $subject
     */
    private function __construct(
        Token $token,
        Term $subject
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
            return new self($value, ExpressionParser::parseTerm($stream));
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::OPERATOR_LOGICAL_NOT()]
            );
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
     * @return Term
     */
    public function getSubject(): Term
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
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ],
            'subject' => $this->subject
        ];
    }
}