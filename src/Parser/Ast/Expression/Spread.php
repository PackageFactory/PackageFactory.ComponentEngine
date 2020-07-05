<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Spread implements Statement, ParameterAssignment, \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Spreadable
     */
    private $subject;

    /**
     * @param Token $token
     * @param Spreadable $subject
     */
    private function __construct(
        Token $token,
        Spreadable $subject
    ) {
        $this->token = $token;
        $this->subject = $subject;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ($value->getType() === TokenType::OPERATOR_SPREAD()) {
            $stream->next();

            $token = $stream->current();
            $subject = ExpressionParser::parseTerm($stream);
            if ($subject instanceof Spreadable) {
                return new self($value, $subject);
            } else {
                throw ParserFailed::becauseOfUnexpectedTerm(
                    $token,
                    $subject,
                    [
                        ArrayLiteral::class,
                        ObjectLiteral::class,
                        Chain::class,
                        Conjunction::class,
                        Disjunction::class,
                        Identifier::class,
                        Ternary::class
                    ]
                );
            }
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::OPERATOR_SPREAD()]
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
        /** @var Term $subject  */
        $subject = $this->subject;
        return $subject;
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