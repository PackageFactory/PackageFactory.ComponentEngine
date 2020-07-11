<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Comparison implements Term, Statement, \JsonSerializable
{
    const COMPARATOR_EQ = '===';
    const COMPARATOR_NEQ = '!==';
    const COMPARATOR_GT = '>';
    const COMPARATOR_GTE = '>=';
    const COMPARATOR_LT = '<';
    const COMPARATOR_LTE = '<=';

    /**
     * @var Term
     */
    private $left;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var Term
     */
    private $right;

    /**
     * @param Term $left
     * @param string $operator
     * @param Term $right
     */
    private function __construct(Term $left, string $operator, Term $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * @param Term $left
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(Term $left, TokenStream $stream): self
    {
        $operator = null;
        switch ($stream->current()->getType()) {
            case TokenType::COMPARATOR_EQ():
            case TokenType::COMPARATOR_NEQ():
            case TokenType::COMPARATOR_GT():
            case TokenType::COMPARATOR_GTE():
            case TokenType::COMPARATOR_LT():
            case TokenType::COMPARATOR_LTE():
                $operator = $stream->current()->getValue();
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::COMPARATOR_EQ(),
                        TokenType::COMPARATOR_GT(),
                        TokenType::COMPARATOR_GTE(),
                        TokenType::COMPARATOR_LT(),
                        TokenType::COMPARATOR_LTE()
                    ]
                );
        }

        $stream->skipWhiteSpaceAndComments();

        return new self(
            $left, 
            $operator, 
            ExpressionParser::parseTerm($stream, ExpressionParser::PRIORITY_COMPARISON)
        );
    }

    /**
     * @return Term
     */
    public function getLeft(): Term
    {
        return $this->left;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return Term
     */
    public function getRight(): Term
    {
        return $this->right;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Comparison',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}