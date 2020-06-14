<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Root
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

        if ($jsxdelimiter = $iterator->lookAhead(mb_strlen('```jsx'))) {
            yield Token::createFromFragment(
                TokenType::JSX_DELIMITER(),
                $jsxdelimiter
            );

            $iterator->skip(mb_strlen('```jsx'));
        }

        
        while ($iterator->current()) {
            foreach (Whitespace::tokenize($iterator) as $token) {
                yield $token;
            }

            if ($iterator->current()->getValue() === 'i') {
                foreach (ImportStatement::tokenize($iterator) as $token) {
                    yield $token;
                }

                foreach (Whitespace::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            elseif ($iterator->current()->getValue() === '<') {
                foreach (TagStructure::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            elseif ($iterator->current()->getValue() === '/') {
                foreach (Comment::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
        }
    }
}