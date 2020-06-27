<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Expression
{
    public const PRIORITY_TERNARY = 5;
    public const PRIORITY_DISJUNCTION = 4;
    public const PRIORITY_CONJUNCTION = 3;
    public const PRIORITY_COMPARISON = 2;
    public const PRIORITY_DASH_OPERATION = 1;
    public const PRIORITY_POINT_OPERATION = 0;

    /**
     * @param TokenStream $stream
     * @return null|Operand|Tag
     */
    public static function createFromTokenStream(
        TokenStream $stream,
        int $priority = self::PRIORITY_TERNARY,
        TokenType $delimiter = null
    ) {
        $left = self::createAtomFromTokenStream($stream);
        if ($left === null) {
            return null;
        }

        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                return $left;
            }

            switch ($stream->current()->getType()) {
                case TokenType::COMMA():
                    if ($left instanceof Identifier) {
                        $left = ArrowFunction::createFromTokenStream($left, $stream);
                        break;
                    } else{
                        if ($delimiter === $stream->current()->getType()) {
                        $stream->next();
                        }
                        return $left;
                    }
                case TokenType::ARROW():
                    if ($left instanceof Identifier) {
                        $left = ArrowFunction::createFromTokenStream($left, $stream);
                        break;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                case TokenType::PERIOD():
                case TokenType::BRACKETS_SQUARE_OPEN():
                case TokenType::BRACKETS_ROUND_OPEN():
                    $left = Chain::createFromTokenStream($left, $stream);
                    break;
                case TokenType::OPERATOR_OPTCHAIN():
                    $left = Chain::createFromTokenStream($left, $stream);
                    break;
                case TokenType::QUESTIONMARK():
                    if ($priority < self::PRIORITY_TERNARY) {
                        return $left;
                    }
                    $left = Ternary::createFromTokenStream($left, $stream);
                    break;
                case TokenType::COMPARATOR_EQ():
                case TokenType::COMPARATOR_GT():
                case TokenType::COMPARATOR_GTE():
                case TokenType::COMPARATOR_LT():
                case TokenType::COMPARATOR_LTE():
                    if ($priority < self::PRIORITY_COMPARISON) {
                        return $left;
                    }
                    $left = Comparison::createFromTokenStream($left, $stream);
                    break;
                case TokenType::OPERATOR_LOGICAL_OR():
                    if ($priority < self::PRIORITY_DISJUNCTION) {
                        return $left;
                    }
                    $left = Disjunction::createFromTokenStream($left, $stream);
                    break;
                case TokenType::OPERATOR_LOGICAL_AND():
                    if ($priority < self::PRIORITY_CONJUNCTION) {
                        return $left;
                    }
                    $left = Conjunction::createFromTokenStream($left, $stream);
                    break;
                
                case TokenType::OPERATOR_ADD():
                case TokenType::OPERATOR_SUBTRACT():
                    if ($priority < self::PRIORITY_DASH_OPERATION) {
                        return $left;
                    }
                    $left = DashOperation::createFromTokenStream($left, $stream);
                    break;
                case TokenType::OPERATOR_MULTIPLY():
                case TokenType::OPERATOR_DIVIDE():
                    if ($priority < self::PRIORITY_POINT_OPERATION) {
                        return $left;
                    }
                    $left = PointOperation::createFromTokenStream($left, $stream);
                    break;
                case TokenType::BRACKETS_SQUARE_CLOSE():
                case TokenType::BRACKETS_CURLY_CLOSE():
                case TokenType::BRACKETS_ROUND_CLOSE():
                case TokenType::TEMPLATE_LITERAL_INTERPOLATION_END():
                case TokenType::AFX_EXPRESSION_END():
                case TokenType::MODULE_KEYWORD_IMPORT():
                case TokenType::MODULE_KEYWORD_EXPORT():
                case TokenType::MODULE_KEYWORD_CONST():
                case TokenType::COLON():
                    if ($delimiter === $stream->current()->getType()) {
                        $stream->next();
                    }
                    return $left;
                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        return $left;
    }

    /**
     * @param TokenStream $stream
     * @return null|Operand|Tag
     */
    public static function createAtomFromTokenStream(TokenStream $stream) 
    {
        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            return null;
        }

        $value = null;
        switch ($stream->current()->getType()) {
            case TokenType::KEYWORD_NULL():
                $value = NullLiteral::createFromTokenStream($stream);
                break;
            case TokenType::KEYWORD_TRUE():
            case TokenType::KEYWORD_FALSE():
                $value = BooleanLiteral::createFromTokenStream($stream);
                break;
            case TokenType::NUMBER():
                $value = NumberLiteral::createFromTokenStream($stream);
                break;
            case TokenType::STRING_LITERAL_START():
                $value = StringLiteral::createFromTokenStream($stream);
                break;
            case TokenType::TEMPLATE_LITERAL_START():
                $value = TemplateLiteral::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_SQUARE_OPEN():
                $value = ArrayLiteral::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_CURLY_OPEN():
                $value = ObjectLiteral::createFromTokenStream($stream);
                break;
            case TokenType::OPERATOR_LOGICAL_NOT():
                $value = Negation::createFromTokenStream($stream);
                break;
            case TokenType::OPERATOR_SPREAD():
                $value = Spread::createFromTokenStream($stream);
                break;
            case TokenType::IDENTIFIER():
                $value = Identifier::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_ROUND_OPEN():
                $stream->next();
                Util::skipWhiteSpaceAndComments($stream);

                if ($stream->valid() && $stream->current()->getType() === TokenType::BRACKETS_ROUND_CLOSE()) {
                    $stream->next();
                    $value = ArrowFunction::createFromTokenStream(null, $stream);
                } else {
                    $value = self::createFromTokenStream($stream);
                    Util::expect($stream, TokenType::BRACKETS_ROUND_CLOSE());
                }
                break;
            case TokenType::AFX_TAG_START():
                $value = Tag::createFromTokenStream($stream);
                break;
            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current()->getType());
        }

        return $value;
    }
}