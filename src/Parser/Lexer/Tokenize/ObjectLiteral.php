<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class ObjectLiteral
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

        if ($iterator->current()->getValue() === '{') {
            yield Token::createFromFragment(
                TokenType::OBJECT_START(),
                $iterator->current()
            );
            $iterator->next();
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        
        while ($iterator->current()) {
            foreach (Whitespace::tokenize($iterator) as $token) {
                yield $token;
            }

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
            elseif ($iterator->current()->getValue() === '[') {
                yield Token::createFromFragment(
                    TokenType::COMPUTED_KEY_START(),
                    $iterator->current()
                );
                $iterator->next();

                foreach (Statement::tokenize($iterator) as $token) {
                    yield $token;
                }

                if ($iterator->current()->getValue() === ']') {
                    yield Token::createFromFragment(
                        TokenType::COMPUTED_KEY_END(),
                        $iterator->current()
                    );
                    $iterator->next();
                }
                else {
                    throw new \Exception('@TODO: Exception');
                }
            }
            else {
                foreach (Identifier::tokenize($iterator) as $token) {
                    yield $token;
                }
            }

            foreach (Whitespace::tokenize($iterator) as $token) {
                yield $token;
            }

            if ($iterator->current()->getValue() === ':') {
                yield Token::createFromFragment(
                    TokenType::ASSIGNMENT(),
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

            foreach (Expression::tokenizeBody($iterator) as $token) {
                yield $token;
            }
            
            foreach (Whitespace::tokenize($iterator) as $token) {
                yield $token;
            }

            if ($iterator->current()->getValue() === ',') {
                yield Token::createFromFragment(
                    TokenType::COMMA(),
                    $iterator->current()
                );
                $iterator->next();
            }
            else {
                break;
            }
        }

        if ($iterator->current()->getValue() === '}') {
            yield Token::createFromFragment(
                TokenType::OBJECT_END(),
                $iterator->current()
            );
            $iterator->next();
        }
        else {
            throw new \Exception('@TODO: Exception');
        }
    }
}