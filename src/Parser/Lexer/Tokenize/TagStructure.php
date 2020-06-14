<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class TagStructure
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }

        if ($iterator->current()->getValue() === '<') {
            yield Token::createFromFragment(
                TokenType::TAG_START(),
                $iterator->current()
            );
            $iterator->next();

            if ($iterator->current()->getValue() === '/') {
                yield Token::createFromFragment(
                    TokenType::TAG_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();

                if ($iterator->current()->getValue() === '>') {
                    yield Token::createFromFragment(
                        TokenType::TAG_END(),
                        $iterator->current()
                    );
                    $iterator->next();
                    return;
                }
            }
            elseif ($iterator->current()->getValue() === '>') {
                yield Token::createFromFragment(
                    TokenType::TAG_END(),
                    $iterator->current()
                );
                $iterator->next();
                return;
            }
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        // var_dump('TagName');
        foreach (Identifier::tokenize($iterator) as $token) {
            yield $token;
        }

        if ($iterator->current()->getValue() === ':') {
            yield Token::createFromFragment(
                TokenType::COLON(),
                $iterator->current()
            );
            $iterator->next();

            foreach (Identifier::tokenize($iterator) as $token) {
                yield $token;
            }
        }

        // Attributes
        while ($iterator->current()) {
            foreach (Whitespace::tokenize($iterator) as $token) {
                yield $token;
            }

            if ($iterator->current()->getValue() === '>') {
                // var_dump('Tag End after Attribute List');
                yield Token::createFromFragment(
                    TokenType::TAG_END(),
                    $iterator->current()
                );
                $iterator->next();
                break;
            }
            elseif ($iterator->current()->getValue() === '/') {
                yield Token::createFromFragment(
                    TokenType::TAG_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();

                if ($iterator->current()->getValue() === '>') {
                    yield Token::createFromFragment(
                        TokenType::TAG_END(),
                        $iterator->current()
                    );
                    $iterator->next();
                    return;
                } else {
                    throw new \Exception('@TODO: Exception');
                }
            }
            elseif ($iterator->current()->getValue() === '{') {
                foreach (Expression::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            else {
                // var_dump('Attribute Name');
                foreach (Identifier::tokenize($iterator) as $token) {
                    yield $token;
                }

                if ($iterator->current()->getValue() === ':') {
                    yield Token::createFromFragment(
                        TokenType::COLON(),
                        $iterator->current()
                    );
                    $iterator->next();

                    foreach (Identifier::tokenize($iterator) as $token) {
                        yield $token;
                    }
                }
                
                // var_dump('Attribute Assignment');
                if ($iterator->current()->getValue() === '=') {
                    yield Token::createFromFragment(
                        TokenType::ASSIGNMENT(),
                        $iterator->current()
                    );
                    $iterator->next();

                    // var_dump('Attribute Value');
                    if ($iterator->current()->getValue() === '"') {
                        foreach (StringLiteral::tokenize($iterator) as $token) {
                            yield $token;
                        }
                    }
                    elseif ($iterator->current()->getValue() === '\'') {
                        foreach (StringLiteral::tokenize($iterator) as $token) {
                            yield $token;
                        }
                    }
                    elseif ($iterator->current()->getValue() === '{') {
                        foreach (Expression::tokenize($iterator) as $token) {
                            yield $token;
                        }
                    }
                    else {
                        throw new \Exception('@TODO: Exception');
                    }
                }
            }
        }

        foreach (Content::tokenize($iterator) as $token) {
            yield $token;
        }
    }
}