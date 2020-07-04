<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class PointOperation implements Term, Statement, \JsonSerializable
{
    const OPERATOR_MULTIPLY = '*';
    const OPERATOR_DIVIDE = '/';
    const OPERATOR_MODULO = '%';

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
        if ((
            $operator !== self::OPERATOR_MULTIPLY && 
            $operator !== self::OPERATOR_DIVIDE && 
            $operator !== self::OPERATOR_MODULO
        )) {
            throw new \Exception('@TODO: Unknown Operator');
        }

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
        Util::ensureValid($stream);

        $operator = null;
        switch ($stream->current()->getType()) {
            case TokenType::OPERATOR_MULTIPLY():
            case TokenType::OPERATOR_DIVIDE():
            case TokenType::OPERATOR_MODULO():
                $operator = $stream->current()->getValue();
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::OPERATOR_MULTIPLY(),
                        TokenType::OPERATOR_DIVIDE(),
                        TokenType::OPERATOR_MODULO()
                    ]
                );
        }

        Util::skipWhiteSpaceAndComments($stream);
        Util::ensureValid($stream);

        return new self(
            $left, 
            $operator, 
            ExpressionParser::parseTerm($stream, ExpressionParser::PRIORITY_POINT_OPERATION)
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
            'type' => 'PointOperation',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}