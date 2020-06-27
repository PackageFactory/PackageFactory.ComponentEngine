<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Expression
{
    public const KEYWORD_TRUE = 'true';
    public const KEYWORD_FALSE = 'false';
    public const KEYWORD_NULL = 'null';

    /**
     * @param SourceIterator $iterator
     * @param array $escapeSequences
     * @return \Iterator<Token>
     */
    public static function tokenize(
        SourceIterator $iterator, 
        array $escapeSequences = []
    ): \Iterator {
        $brackets = 0;
        $operand = false;
        while ($iterator->valid()) {
            yield from Whitespace::tokenize($iterator);

            if ($brackets === 0) {
                foreach ($escapeSequences as $escapeSequence) {
                    $length = mb_strlen($escapeSequence);
                    if ($iterator->current()->getValue() === $escapeSequence[0]) {
                        if ($lookAhead = $iterator->lookAhead($length)) {
                            if ($lookAhead->getValue() === $escapeSequence) {
                                return;
                            }
                        }
                    }
                }
            }

            if ($keyword = Keyword::extract($iterator, self::KEYWORD_TRUE)) {
                yield Token::createFromFragment(
                    TokenType::KEYWORD_TRUE(),
                    $keyword
                );
                $operand = true;
                continue;
            } elseif ($keyword = Keyword::extract($iterator, self::KEYWORD_FALSE)) {
                yield Token::createFromFragment(
                    TokenType::KEYWORD_FALSE(),
                    $keyword
                );
                $operand = true;
                continue;
            } elseif ($keyword = Keyword::extract($iterator, self::KEYWORD_NULL)) {
                yield Token::createFromFragment(
                    TokenType::KEYWORD_NULL(),
                    $keyword
                );
                $operand = true;
                continue;
            } 
            
            switch ($iterator->current()->getValue()) {
                case '!':
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_LOGICAL_NOT(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '&':
                    if ($lookAhead = $iterator->willBe('&&')) {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_LOGICAL_AND(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        $operand = false;
                    } else {
                        throw new \Exception('@TODO: Unexpected Fragment: ' . $iterator->current());
                    }
                    break;
                case '|':
                    if ($lookAhead = $iterator->willBe('||')) {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_LOGICAL_OR(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        $operand = false;
                    } else {
                        throw new \Exception('@TODO: Unexpected Fragment: ' . $iterator->current());
                    }
                    break;
                case '.':
                    if ($lookAhead = $iterator->lookAhead(2)) {
                        if (Number::is($lookAhead->getValue()[1])) {
                            yield from Number::tokenize($iterator);
                            $operand = true;
                            break;
                        }  elseif ($lookAhead = $iterator->willBe('...')) {
                            yield Token::createFromFragment(
                                TokenType::OPERATOR_SPREAD(),
                                $lookAhead
                            );
                            $iterator->skip(3);
                            $operand = true;
                            break;
                        }
                    }

                    yield Token::createFromFragment(
                        TokenType::PERIOD(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '+':
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_ADD(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '-':
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_SUBTRACT(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '*':
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_MULTIPLY(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '/':
                    if ($lookAhead = $iterator->willBe('/*')) {
                        yield from Comment::tokenize($iterator);
                    } else {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_DIVIDE(),
                            $iterator->current()
                        );
                        $iterator->next();
                    }
                    $operand = false;
                    break;
                case '%':
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_MODULO(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '=':
                    if ($lookAhead = $iterator->willBe('=>')) {
                        yield Token::createFromFragment(
                            TokenType::ARROW(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                    } elseif ($lookAhead = $iterator->willBe('===')) {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_EQ(),
                            $lookAhead
                        );
                        $iterator->skip(3);
                    } else {
                        throw new \Exception('@TODO: Unexpected Fragment: ' . $iterator->current());
                    }
                    $operand = false;
                    break;
                case '>':
                    if ($lookAhead = $iterator->willBe('>=')) {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_GTE(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                    } else {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_GT(),
                            $iterator->current()
                        );
                        $iterator->next();
                    }
                    $operand = false;
                    break;
                case '<':
                    if ($lookAhead = $iterator->willBe('<=')) {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_LTE(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                    } elseif($operand) {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_LT(),
                            $iterator->current()
                        );
                        $iterator->next();
                    } else {
                        yield from Afx::tokenize($iterator);
                    }
                    $operand = false;
                    break;
                case '(':
                    yield Token::createFromFragment(
                        TokenType::BRACKETS_ROUND_OPEN(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $brackets++;
                    $operand = false;
                    break;
                case ')':
                    if ($brackets > 0) {
                        yield Token::createFromFragment(
                            TokenType::BRACKETS_ROUND_CLOSE(),
                            $iterator->current()
                        );
                        $iterator->next();
                        $brackets--;
                    } else {
                        return;
                    }
                    $operand = false;
                    break;
                case '[':
                    yield Token::createFromFragment(
                        TokenType::BRACKETS_SQUARE_OPEN(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $brackets++;
                    $operand = false;
                    break;
                case ']':
                    if ($brackets > 0) {
                        yield Token::createFromFragment(
                            TokenType::BRACKETS_SQUARE_CLOSE(),
                            $iterator->current()
                        );
                        $iterator->next();
                        $brackets--;
                        $operand = true;
                    } else {
                        return;
                    }
                    $operand = false;
                    break;
                case '{':
                    yield Token::createFromFragment(
                        TokenType::BRACKETS_CURLY_OPEN(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $brackets++;
                    $operand = false;
                    break;
                case '}':
                    if ($brackets > 0) {
                        yield Token::createFromFragment(
                            TokenType::BRACKETS_CURLY_CLOSE(),
                            $iterator->current()
                        );
                        $iterator->next();
                        $brackets--;
                        $operand = true;
                    } else {
                        return;
                    }
                    $operand = false;
                    break;
                case ':':
                    yield Token::createFromFragment(
                        TokenType::COLON(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case ',':
                    yield Token::createFromFragment(
                        TokenType::COMMA(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $operand = false;
                    break;
                case '?':
                    if ($lookAhead = $iterator->willBe('?.')) {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_OPTCHAIN(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        $operand = false;
                    } elseif ($lookAhead = $iterator->willBe('??')) {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_NULLISH_COALESCE(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        $operand = false;
                    } else {
                        yield Token::createFromFragment(
                            TokenType::QUESTIONMARK(),
                            $iterator->current()
                        );
                        $iterator->next();
                        $operand = false;
                    }
                    break;
                case '"':
                case '\'':
                    yield from StringLiteral::tokenize($iterator);
                    $operand = true;
                    break;
                case '`':
                    yield from TemplateLiteral::tokenize($iterator);
                    $operand = true;
                    break;
                default:
                    $value = $iterator->current()->getValue();
                    if (Number::is($value)) {
                        yield from Number::tokenize($iterator);
                        $operand = true;
                    } elseif (Identifier::is($value)) {
                        yield from Identifier::tokenize($iterator);
                        $operand = true;
                    } else {
                        $operand = false;
                        return;
                    }
                break;
            }
        }
    }
}