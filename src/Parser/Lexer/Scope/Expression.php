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
        while ($iterator->valid()) {
            foreach (Whitespace::tokenize($iterator) as $token) {
                yield $token;
            }

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
                continue;
            } elseif ($keyword = Keyword::extract($iterator, self::KEYWORD_FALSE)) {
                yield Token::createFromFragment(
                    TokenType::KEYWORD_FALSE(),
                    $keyword
                );
                continue;
            } elseif ($keyword = Keyword::extract($iterator, self::KEYWORD_NULL)) {
                yield Token::createFromFragment(
                    TokenType::KEYWORD_NULL(),
                    $keyword
                );
                continue;
            } 
            
            $value = $iterator->current()->getValue();

            if ($value === '!') {
                yield Token::createFromFragment(
                    TokenType::OPERATOR_LOGICAL_NOT(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === '&') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === '&&') {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_LOGICAL_AND(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        continue;
                    }
                }
            } elseif ($value === '|') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === '||') {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_LOGICAL_OR(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        continue;
                    }
                }
            } elseif ($value === '.') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    $lookAheadValue = $lookAhead->getValue();

                    if (Number::is($lookAheadValue[1])) {
                        foreach (Number::tokenize($iterator) as $token) {
                            yield $token;
                        }
                        continue;
                    }  elseif ($lookAhead = $iterator->lookAhead(3)) {
                        if ($lookAhead->getValue() === '...') {
                            yield Token::createFromFragment(
                                TokenType::OPERATOR_SPREAD(),
                                $lookAhead
                            );
                            $iterator->skip(3);
                            continue;
                        } else {
                            yield Token::createFromFragment(
                                TokenType::PERIOD(),
                                $iterator->current()
                            );
                            $iterator->next();
                            continue;
                        }
                    }  else {
                        yield Token::createFromFragment(
                            TokenType::PERIOD(),
                            $iterator->current()
                        );
                        $iterator->next();
                        continue;
                    }
                } else {
                    yield Token::createFromFragment(
                        TokenType::PERIOD(),
                        $iterator->current()
                    );
                    $iterator->next();
                    continue;
                }
            } elseif ($value === '+') {
                yield Token::createFromFragment(
                    TokenType::OPERATOR_ADD(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === '-') {
                yield Token::createFromFragment(
                    TokenType::OPERATOR_SUBTRACT(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === '*') {
                yield Token::createFromFragment(
                    TokenType::OPERATOR_MULTIPLY(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === '/') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === '/*') {
                        foreach (Comment::tokenize($iterator) as $token) {
                            yield $token;
                        }
                    } else {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_DIVIDE(),
                            $iterator->current()
                        );
                        $iterator->next();
                        continue;
                    }
                } else {
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_DIVIDE(),
                        $iterator->current()
                    );
                    $iterator->next();
                    continue;
                }
            } elseif ($value === '%') {
                yield Token::createFromFragment(
                    TokenType::OPERATOR_MODULO(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === '=') {
                if ($lookAhead = $iterator->lookAhead(3)) {
                    if ($lookAhead->getValue() === '===') {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_EQ(),
                            $lookAhead
                        );
                        $iterator->skip(3);
                        continue;
                    }
                }
            } elseif ($value === '>') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === '>=') {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_GTE(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        continue;
                    } else {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_GT(),
                            $iterator->current()
                        );
                        $iterator->next();
                        continue;
                    }
                } else {
                    yield Token::createFromFragment(
                        TokenType::COMPARATOR_GT(),
                        $iterator->current()
                    );
                    $iterator->next();
                    continue;
                }
            } elseif ($value === '<') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === '<=') {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_LTE(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        continue;
                    } else {
                        yield Token::createFromFragment(
                            TokenType::COMPARATOR_LT(),
                            $iterator->current()
                        );
                        $iterator->next();
                        continue;
                    }
                } else {
                    yield Token::createFromFragment(
                        TokenType::COMPARATOR_LT(),
                        $iterator->current()
                    );
                    $iterator->next();
                    continue;
                }
            } elseif ($value === '(') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_ROUND_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
                $brackets++;
                continue;
            } elseif ($value === ')') {
                if ($brackets > 0) {
                    yield Token::createFromFragment(
                        TokenType::BRACKETS_ROUND_CLOSE(),
                        $iterator->current()
                    );
                    $iterator->next();
                    $brackets--;
                    continue;
                } else {
                    return;
                }
            } elseif ($value === '[') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_SQUARE_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
                $brackets++;
                continue;
            } elseif ($value === ']') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_SQUARE_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
                $brackets--;
                continue;
            } elseif ($value === '{') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_CURLY_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
                $brackets++;
                continue;
            } elseif ($value === '}') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_CURLY_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
                $brackets--;
                continue;
            } elseif ($value === ':') {
                yield Token::createFromFragment(
                    TokenType::COLON(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === ',') {
                yield Token::createFromFragment(
                    TokenType::COMMA(),
                    $iterator->current()
                );
                $iterator->next();
                continue;
            } elseif ($value === '?') {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === '?.') {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_OPTCHAIN(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        continue;
                    } elseif ($lookAhead->getValue() === '??') {
                        yield Token::createFromFragment(
                            TokenType::OPERATOR_NULLISH_COALESCE(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        continue;
                    } else {
                        yield Token::createFromFragment(
                            TokenType::QUESTIONMARK(),
                            $iterator->current()
                        );
                        $iterator->next();
                        continue;
                    }
                } else {
                    yield Token::createFromFragment(
                        TokenType::QUESTIONMARK(),
                        $iterator->current()
                    );
                    $iterator->next();
                    continue;
                }
            } elseif ($value === '"' || $value === '\'') {
                foreach (StringLiteral::tokenize($iterator) as $token) {
                    yield $token;
                }
                continue;
            } elseif ($value === '`') {
                foreach (TemplateLiteral::tokenize($iterator) as $token) {
                    yield $token;
                }
                continue;
            } elseif (Number::is($value)) {
                foreach (Number::tokenize($iterator) as $token) {
                    yield $token;
                }
                continue;
            } elseif (Identifier::is($value)) {
                foreach (Identifier::tokenize($iterator) as $token) {
                    yield $token;
                }
                continue;
            } else {
                break;
            }

            $iterator->next();
        }
    }
}