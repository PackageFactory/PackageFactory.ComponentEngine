<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Content
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        $capture = null;
        while ($iterator->current()) {
            if ($iterator->current()->getValue() === '{') {
                if ($capture !== null) {
                    yield Token::createFromFragment(
                        TokenType::CONTENT(),
                        $capture
                    );
        
                    $capture = null;
                }
                foreach (Expression::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            elseif ($iterator->current()->getValue() === '<') {
                if ($capture !== null) {
                    yield Token::createFromFragment(
                        TokenType::CONTENT(),
                        $capture
                    );
        
                    $capture = null;
                }
                foreach (TagStructure::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            elseif (Whitespace::is($iterator->current())) {
                if ($capture !== null) {
                    yield Token::createFromFragment(
                        TokenType::CONTENT(),
                        $capture
                    );

                    $capture = null;
                }

                foreach (Whitespace::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            else {
                if ($capture === null) {
                    $capture = $iterator->current();
                }
                else {
                    $capture = $capture->append($iterator->current());
                }
                $iterator->next();
            }
        }
    }
}