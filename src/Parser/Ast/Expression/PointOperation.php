<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\ContextEvaluatorInterface;

final class PointOperation implements \JsonSerializable, ContextEvaluatorInterface
{
    const OPERATOR_MULTIPLY = '*';
    const OPERATOR_DIVIDE = '/';
    const OPERATOR_MODULO = '%';

    /**
     * @var ExpressionTerm
     */
    private $left;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var ExpressionTerm
     */
    private $right;

    /**
     * @param ExpressionTerm $left
     * @param string $operator
     * @param ExpressionTerm $right
     */
    private function __construct($left, string $operator, $right)
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
     * @param ExpressionTerm $left
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream($left, TokenStream $stream): self 
    {
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $operator = null;
        switch ($stream->current()->getType()) {
            case TokenType::OPERATOR_MULTIPLY():
            case TokenType::OPERATOR_DIVIDE():
            case TokenType::OPERATOR_MODULO():
                $operator = $stream->current()->getValue();
                $stream->next();
                break;
            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $right = null;
        switch ($stream->current()->getType()) {
            case TokenType::KEYWORD_TRUE():
            case TokenType::KEYWORD_FALSE():
                $right = BooleanLiteral::createFromTokenStream($stream);
                break;
            case TokenType::NUMBER():
                $right = NumberLiteral::createFromTokenStream($stream);
                break;
            case TokenType::OPERATOR_LOGICAL_NOT():
                $right = Negation::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_ROUND_OPEN():
                $stream->next();
                $right = Expression::createFromTokenStream(
                    $stream, 
                    Expression::PRIORITY_POINT_OPERATION,
                    TokenType::BRACKETS_ROUND_CLOSE()
                );
                break;
            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        return new self($left, $operator, $right);
    }

    /**
     * @return ExpressionTerm
     */
    public function getLeft()
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
     * @return ExpressionTerm
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @return float
     */
    public function evaluate(Context $context): float
    {
        $left = $this->left->evaluate($context);
        if ($left === 0) {
            return 0;
        }

        $right = $this->right->evaluate($context);

        if ($this->operator === self::OPERATOR_MULTIPLY) {
            return $left * $right;
        } elseif ($this->operator === self::OPERATOR_DIVIDE) {
            return $left / $right;
        } elseif ($this->operator === self::OPERATOR_MODULO) {
            return $left % $right;
        } else {
            throw new \RuntimeException('@TODO: Unknown operator');
        }
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