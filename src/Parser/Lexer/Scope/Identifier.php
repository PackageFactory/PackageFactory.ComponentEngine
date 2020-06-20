<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Identifier
{
    /**
     * @param string $char
     * @return boolean
     */
    public static function is(string $char): bool
    {
        return ctype_alnum($char) || $char === '_' || $char === '$';
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        $capture = null;
        while ($iterator->valid()) {
            $value = $iterator->current()->getValue();

            if (self::is($value)) {
                if ($capture === null) {
                    $capture = $iterator->current();
                } else {
                    $capture = $capture->append($iterator->current());
                }
            } else {
                break;
            }

            $iterator->next();
        }

        if ($capture !== null) {
            yield Token::createFromFragment(
                TokenType::IDENTIFIER(),
                $capture
            );
        }
    }
}