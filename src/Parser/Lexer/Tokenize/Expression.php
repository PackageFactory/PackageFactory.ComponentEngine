<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Expression
{
    /**
     * @param Fragment $fragment
     * @return boolean
     */
    public static function is(Fragment $fragment): bool
    {
        return $fragment->getValue() === '{';
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }

        // var_dump('Expression Start');
        if ($iterator->current()->getValue() === '{') {
            yield Token::createFromFragment(
                TokenType::EXPRESSION_START(),
                $iterator->current()
            );
            $iterator->next();
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        foreach (self::tokenizeBody($iterator) as $token) {
            yield $token;
        }

        // var_dump('Expression End');
        if ($iterator->current()->getValue() === '}') {
            yield Token::createFromFragment(
                TokenType::EXPRESSION_END(),
                $iterator->current()
            );
            $iterator->next();
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeBody(SourceIterator $iterator): \Iterator
    {
        // var_dump('Expression Body');
        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }

        if ($iterator->current()->getValue() === '{') {
            foreach (ObjectLiteral::tokenize($iterator) as $token) {
                yield $token;
            }
        }
        elseif ($iterator->current()->getValue() === '"') {
            foreach (StringLiteral::tokenize($iterator) as $token) {
                yield $token;
            }
        }
        elseif ($iterator->current()->getValue() === '\'') {
            foreach (StringLiteral::tokenize($iterator) as $token) {
                yield $token;
            }
        }
        elseif ($iterator->current()->getValue() === '/') {
            foreach (Comment::tokenize($iterator) as $token) {
                yield $token;
            }
        }
        else {
            foreach (Statement::tokenize($iterator) as $token) {
                yield $token;
            }
        }

        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }
    }
}