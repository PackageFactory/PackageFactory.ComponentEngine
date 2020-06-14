<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Comment
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

        if ($iterator->current()->getValue() === '/') {
            $fragment = $iterator->current();
            $iterator->next();

            if ($iterator->current()->getValue() === '*') {
                $delimiter = '*';
                $fragment = $fragment->append($iterator->current());
                $iterator->next();
            }
            elseif ($iterator->current()->getValue() === '/') {
                $delimiter = PHP_EOL;
                $fragment = $fragment->append($iterator->current());
                $iterator->next();
            }
            else {
                throw new \Exception('@TODO: Exception');
            }

            yield Token::createFromFragment(
                TokenType::COMMENT_START(),
                $fragment
            );
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        $fragment = null;
        while ($iterator->current()) {
            if ($iterator->current()->getValue() === $delimiter) {
                if ($delimiter === '*') {
                    $capture = $iterator->current();
                    $iterator->next();

                    if ($iterator->current()->getValue() === '/') {
                        if ($fragment !== null) {
                            yield Token::createFromFragment(
                                TokenType::COMMENT_CONTENT(),
                                $fragment
                            );
                        }
                        yield Token::createFromFragment(
                            TokenType::COMMENT_END(),
                            $capture->append($iterator->current())
                        );
                        $iterator->next();
                        break;
                    }
                }
                else {
                    if ($fragment !== null) {
                        yield Token::createFromFragment(
                            TokenType::COMMENT_CONTENT(),
                            $fragment
                        );
                    }
                    yield Token::createFromFragment(
                        TokenType::COMMENT_END(),
                        $iterator->current()
                    );
                    $iterator->next();
                }
            }
            else {
                if ($fragment === null) {
                    $fragment = $iterator->current();
                }
                else {
                    $fragment = $fragment->append($iterator->current());
                }
            }

            $iterator->next();
        }
    }
}