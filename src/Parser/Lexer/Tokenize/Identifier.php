<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Identifier
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

        if (ctype_alpha($iterator->current()->getValue())) {
            $fragment = $iterator->current();
            $iterator->next();
        }
        else {
            // var_dump($iterator->current()->getValue());
            throw new \Exception('@TODO: Exception');
        }

        while (
            ctype_alnum($iterator->current()->getValue()) ||
            $iterator->current()->getValue() === '_'
        ) {
            $fragment = $fragment->append($iterator->current());
            $iterator->next();
        }
        
        yield Token::createFromFragment(
            TokenType::IDENTIFIER(),
            $fragment
        );
    }
}