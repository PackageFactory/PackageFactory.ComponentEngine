<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Whitespace
{
    /**
     * @param Fragment $fragment
     * @return boolean
     */
    public static function is(Fragment $fragment): bool
    {
        return ctype_space($fragment->getValue());
    }

    /**
     * @phpstan-impure
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        /** @var Fragment|null $capture */
        $capture = null;

        while ($iterator->valid()) {
            if ($iterator->current()->getValue() === PHP_EOL) {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::WHITESPACE(),
                        $capture
                    );

                    $capture = null;
                }

                yield Token::fromFragment(
                    TokenType::END_OF_LINE(),
                    $iterator->current()
                );
                $iterator->next();
            } 
            elseif (self::is($iterator->current())) {
                if ($capture === null) {
                    $capture = $iterator->current();
                }
                else {
                    $capture = $capture->append($iterator->current());
                }

                $iterator->next();
            }
            else {
                if ($capture !== null) {
                    yield Token::fromFragment(
                        TokenType::WHITESPACE(),
                        $capture
                    );

                    $capture = null;
                }
                break;
            }
        }

        if ($capture !== null) {
            yield Token::fromFragment(
                TokenType::WHITESPACE(),
                $capture
            );

            $capture = null;
        }
    }
}