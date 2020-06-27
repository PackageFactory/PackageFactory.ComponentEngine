<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class DashOperation implements \JsonSerializable
{
    const OPERATOR_ADD = '+';
    const OPERATOR_SUBTRACT = '-';

    /**
     * @var Operand
     */
    private $left;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var Operand
     */
    private $right;

    /**
     * @param Operand $left
     * @param string $operator
     * @param Operand $right
     */
    private function __construct($left, string $operator, $right)
    {
        if ($operator !== self::OPERATOR_ADD && $operator !== self::OPERATOR_SUBTRACT) {
            throw new \Exception('@TODO: Unknown Operator');
        }

        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * @param Operand $left
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

        if ($right === null) {
            throw new \Exception('@TODO: Unexpected empty operand');
        }

        return new self($left, $operator, $right);
    }

    /**
     * @return Operand
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
     * @return Operand
     */
    public function getRight()
    {
        return $this->right;
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