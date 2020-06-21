<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\ContextEvaluatorInterface;

final class Disjunction implements \JsonSerializable, ContextEvaluatorInterface
{
    const OPERATOR_LOGICAL_OR = '||';

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

    private function __construct($left, string $operator, $right)
    {
        if ($operator !== self::OPERATOR_LOGICAL_OR) {
            throw new \Exception('@TODO: Unknown Operator');
        }

        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public static function createFromTokenStream($left, TokenStream $stream): self 
    {
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $operator = null;
        switch ($stream->current()->getType()) {
            case TokenType::OPERATOR_LOGICAL_OR():
                $operator = $stream->current()->getValue();
                $stream->next();
                break;
            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        $right = Expression::createFromTokenStream(
            $stream,
            Expression::PRIORITY_DISJUNCTION
        );

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
     * @return bool|mixed
     */
    public function evaluate(Context $context = null)
    {
        $left = $this->left->evaluate($context);
        if (is_string($left)) {
            $left = $left !== '';
        } elseif (is_numeric($left)) {
            $left = $left !== 0.0;
        } elseif (is_null($left)) {
            $left = false;
        } elseif (is_bool($left)) {
            $left = $left;
        } else {
            $left = (bool) $left;
        }

        if ($this->operator === self::OPERATOR_LOGICAL_OR) {
            if ($left) {
                return true;
            } else {
                return $this->right->evaluate($context);
            }
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
            'type' => 'Disjunction',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}