<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Comparison implements \JsonSerializable
{
    const COMPARATOR_EQ = '===';
    const COMPARATOR_GT = '>';
    const COMPARATOR_GTE = '>=';
    const COMPARATOR_LT = '<';
    const COMPARATOR_LTE = '<=';

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
            $operator !== self::COMPARATOR_EQ &&
            $operator !== self::COMPARATOR_GT &&
            $operator !== self::COMPARATOR_GTE &&
            $operator !== self::COMPARATOR_LT &&
            $operator !== self::COMPARATOR_LTE
        )) {
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
            case TokenType::COMPARATOR_EQ():
            case TokenType::COMPARATOR_GT():
            case TokenType::COMPARATOR_GTE():
            case TokenType::COMPARATOR_LT():
            case TokenType::COMPARATOR_LTE():
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
            case TokenType::KEYWORD_NULL():
                $right = NullLiteral::createFromTokenStream($stream);
                break;
            case TokenType::NUMBER():
                $right = NumberLiteral::createFromTokenStream($stream);
                break;
            case TokenType::STRING_LITERAL_START():
                $right = StringLiteral::createFromTokenStream($stream);
                break;
            case TokenType::TEMPLATE_LITERAL_START():
                $right = TemplateLiteral::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_CURLY_OPEN():
                $right = ObjectLiteral::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_SQUARE_OPEN():
                $right = ArrayLiteral::createFromTokenStream($stream);
                break;
            case TokenType::OPERATOR_LOGICAL_NOT():
                $right = Negation::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_ROUND_OPEN():
                $stream->next();
                $right = Expression::createFromTokenStream(
                    $stream, 
                    Expression::PRIORITY_COMPARISON,
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