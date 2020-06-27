<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class PointOperation implements \JsonSerializable
{
    const OPERATOR_MULTIPLY = '*';
    const OPERATOR_DIVIDE = '/';
    const OPERATOR_MODULO = '%';

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
            'type' => 'PointOperation',
            'left' => $this->left,
            'operator' => $this->operator,
            'right' => $this->right
        ];
    }
}