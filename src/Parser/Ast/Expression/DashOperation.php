<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;

final class DashOperation implements \JsonSerializable
{
    const OPERATOR_ADD = '+';
    const OPERATOR_SUBTRACT = '-';

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
        if ($operator !== self::OPERATOR_ADD && $operator !== self::OPERATOR_SUBTRACT) {
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
            case TokenType::OPERATOR_ADD():
            case TokenType::OPERATOR_SUBTRACT():
                $operator = $stream->current()->getValue();
                $stream->next();
                break;
            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        $right = Expression::createFromTokenStream(
            $stream, 
            Expression::PRIORITY_DASH_OPERATION
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
     * @return float|string
     */
    public function evaluate(Context $context = null)
    {
        $left = $this->left->evaluate($context);
        $right = $this->right->evaluate($context);

        if ($this->operator === self::OPERATOR_ADD) {
            if (is_string($left) || is_string($right)) {
                return $left . $right;
            } else {
                return $left + $right;
            }
        } elseif ($this->operator === self::OPERATOR_SUBTRACT) {
            return $left - $right;
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
            'type' => 'DashOperation',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}