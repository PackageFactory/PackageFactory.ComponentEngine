<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Afx;
use PackageFactory\ComponentEngine\Parser\Ast\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class ExpressionParser
{
    public const PRIORITY_LIST = 6;
    public const PRIORITY_TERNARY = 5;
    public const PRIORITY_DISJUNCTION = 4;
    public const PRIORITY_CONJUNCTION = 3;
    public const PRIORITY_COMPARISON = 2;
    public const PRIORITY_DASH_OPERATION = 1;
    public const PRIORITY_POINT_OPERATION = 0;

    /**
     * @param TokenStream $stream
     * @return null|Statement
     */
    public static function parse(TokenStream $stream): ?Statement
    {
        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            return null;
        }

        return self::parseStatement($stream);
    }

    /**
     * @param TokenStream $stream
     * @param int $priority
     * @return Statement
     */
    public static function parseStatement(
        TokenStream $stream,
        int $priority = self::PRIORITY_TERNARY
    ): Statement {
        switch ($stream->current()->getType()) {
            case TokenType::OPERATOR_SPREAD():
                return Expression\Spread::createFromTokenStream($stream);
            default:
                /** @var Statement $statement */
                $statement = self::parseTerm($stream, $priority);
                return $statement;
        }
    }

    /**
     * @param TokenStream $stream
     * @param int $priority
     * @return Term
     */
    public static function parseTerm(
        TokenStream $stream, 
        int $priority = self::PRIORITY_TERNARY,
        $bracket = false
    ): Term {
        switch ($stream->current()->getType()) {
            case TokenType::KEYWORD_NULL():
            case TokenType::KEYWORD_TRUE():
            case TokenType::KEYWORD_FALSE():
            case TokenType::NUMBER():
            case TokenType::STRING_LITERAL_START():
            case TokenType::TEMPLATE_LITERAL_START():
            case TokenType::BRACKETS_SQUARE_OPEN():
            case TokenType::BRACKETS_CURLY_OPEN():
                $term = self::parseLiteral($stream);
                break;
            case TokenType::OPERATOR_LOGICAL_NOT():
                $term = Expression\Negation::createFromTokenStream($stream);
                break;
            case TokenType::IDENTIFIER():
                $term = Expression\Identifier::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_ROUND_OPEN():
                $stream->next();
                Util::skipWhiteSpaceAndComments($stream);
                Util::ensureValid($stream);
                $term = self::parseTerm($stream, $priority, true);
                break;
            case TokenType::AFX_TAG_START():
                $term = Afx\Tag::createFromTokenStream($stream);
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::KEYWORD_NULL(),
                        TokenType::KEYWORD_TRUE(),
                        TokenType::KEYWORD_FALSE(),
                        TokenType::NUMBER(),
                        TokenType::STRING_LITERAL_START(),
                        TokenType::TEMPLATE_LITERAL_START(),
                        TokenType::BRACKETS_SQUARE_OPEN(),
                        TokenType::BRACKETS_CURLY_OPEN(),
                        TokenType::OPERATOR_LOGICAL_NOT(),
                        TokenType::IDENTIFIER(),
                        TokenType::BRACKETS_ROUND_OPEN(),
                        TokenType::AFX_TAG_START()
                    ]
                );
        }


        /** @var Term $term */
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);

            switch ($stream->current()->getType()) {
                case TokenType::COMMA():
                case TokenType::ARROW():
                    if ($term instanceof Expression\Identifier) {
                        $term = Expression\ArrowFunction::createFromTokenStream($term, $stream);
                        break;
                    } elseif ($priority >= self::PRIORITY_LIST) {
                        return $term;
                    } else {
                        throw ParserFailed::becauseOfUnexpectedToken($stream->current());
                    }
                case TokenType::PERIOD():
                case TokenType::BRACKETS_SQUARE_OPEN():
                case TokenType::BRACKETS_ROUND_OPEN():
                case TokenType::OPERATOR_OPTCHAIN():
                    $term = Expression\Chain::createFromTokenStream($term, $stream);
                    break;
                case TokenType::QUESTIONMARK():
                    if ($priority < self::PRIORITY_TERNARY) {
                        return $term;
                    }
                    $term = Expression\Ternary::createFromTokenStream($term, $stream);
                    break;
                case TokenType::COMPARATOR_EQ():
                case TokenType::COMPARATOR_GT():
                case TokenType::COMPARATOR_GTE():
                case TokenType::COMPARATOR_LT():
                case TokenType::COMPARATOR_LTE():
                    if ($priority < self::PRIORITY_COMPARISON) {
                        return $term;
                    }
                    $term = Expression\Comparison::createFromTokenStream($term, $stream);
                    break;
                case TokenType::OPERATOR_LOGICAL_OR():
                    if ($priority < self::PRIORITY_DISJUNCTION) {
                        return $term;
                    }
                    $term = Expression\Disjunction::createFromTokenStream($term, $stream);
                    break;
                case TokenType::OPERATOR_LOGICAL_AND():
                    if ($priority < self::PRIORITY_CONJUNCTION) {
                        return $term;
                    }
                    $term = Expression\Conjunction::createFromTokenStream($term, $stream);
                    break;
                
                case TokenType::OPERATOR_ADD():
                case TokenType::OPERATOR_SUBTRACT():
                    if ($priority < self::PRIORITY_DASH_OPERATION) {
                        return $term;
                    }
                    $term = Expression\DashOperation::createFromTokenStream($term, $stream);
                    break;
                case TokenType::OPERATOR_MULTIPLY():
                case TokenType::OPERATOR_DIVIDE():
                    if ($priority < self::PRIORITY_POINT_OPERATION) {
                        return $term;
                    }
                    $term = Expression\PointOperation::createFromTokenStream($term, $stream);
                    break;
                case TokenType::BRACKETS_ROUND_CLOSE():
                    if ($bracket) {
                        $stream->next();
                    }
                    break 2;
                default:
                    break 2;
            }
        }

        return $term;
    }

    /**
     * @param TokenStream $stream
     * @return Literal
     */
    public static function parseLiteral(TokenStream $stream): Literal
    {
        switch ($stream->current()->getType()) {
            case TokenType::KEYWORD_NULL():
                return Expression\NullLiteral::createFromTokenStream($stream);
            case TokenType::KEYWORD_TRUE():
            case TokenType::KEYWORD_FALSE():
                return Expression\BooleanLiteral::createFromTokenStream($stream);
            case TokenType::NUMBER():
                return Expression\NumberLiteral::createFromTokenStream($stream);
            case TokenType::STRING_LITERAL_START():
                return Expression\StringLiteral::createFromTokenStream($stream);
            case TokenType::TEMPLATE_LITERAL_START():
                return Expression\TemplateLiteral::createFromTokenStream($stream);
            case TokenType::BRACKETS_SQUARE_OPEN():
                return Expression\ArrayLiteral::createFromTokenStream($stream);
            case TokenType::BRACKETS_CURLY_OPEN():
                return Expression\ObjectLiteral::createFromTokenStream($stream);
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::KEYWORD_NULL(),
                        TokenType::KEYWORD_TRUE(),
                        TokenType::KEYWORD_FALSE(),
                        TokenType::NUMBER(),
                        TokenType::STRING_LITERAL_START(),
                        TokenType::TEMPLATE_LITERAL_START(),
                        TokenType::BRACKETS_SQUARE_OPEN(),
                        TokenType::BRACKETS_CURLY_OPEN()
                    ]
                );
        }
    }
}