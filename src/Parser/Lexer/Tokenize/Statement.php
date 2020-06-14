<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Statement
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        if ($iterator->current()->getValue() === '!') {
            yield Token::createFromFragment(
                TokenType::EXCLAMATION(),
                $iterator->current()
            );
            $iterator->next();
        }

        while($iterator->current()) {
            if (ctype_alpha($iterator->current()->getValue())) {
                foreach (Identifier::tokenize($iterator) as $token) {
                    yield $token;
                }
            }
            elseif ($iterator->current()->getValue() === '.') {
                yield Token::createFromFragment(
                    TokenType::PERIOD(),
                    $iterator->current()
                );
                $iterator->next();
            }
            else {
                break;
            }
        }
    }
}