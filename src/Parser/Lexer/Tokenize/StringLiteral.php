<?php
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class StringLiteral
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        $delimiter = $iterator->current();

        /** @var Fragment|null $capture */
        $capture = null;

        yield Token::createFromFragment(
            TokenType::STRING_START(),
            $delimiter
        );

        $iterator->next();

        while ($fragment = $iterator->current()) {
            $value = $fragment->getValue();

            switch (true) {
                case $value === '\\':
                    if ($capture !== null) {
                        yield Token::createFromFragment(
                            TokenType::STRING_VALUE(),
                            $capture
                        );

                        $capture = null;
                    }

                    yield Token::createFromFragment(
                        TokenType::STRING_ESCAPE(),
                        $fragment
                    );

                    $iterator->next();

                    if ($iterator->valid()) {
                        yield Token::createFromFragment(
                            TokenType::STRING_ESCAPED_CHARACTER(),
                            $iterator->current()
                        );

                        $iterator->next();
                    } else throw new \Exception('@TODO: Unexpected end of file.');
                break;

                case $value === $delimiter->getValue():
                    if ($capture !== null) {
                        yield Token::createFromFragment(
                            TokenType::STRING_VALUE(),
                            $capture
                        );

                        $capture = null;
                    }

                    yield Token::createFromFragment(
                        TokenType::STRING_END(),
                        $delimiter
                    );

                    $iterator->next();
                    return;

                case $value === "\n":
                    if ($capture !== null) {
                        yield Token::createFromFragment(
                            TokenType::STRING_VALUE(),
                            $capture
                        );

                        $capture = null;
                    }

                    yield Token::createFromFragment(
                        TokenType::END_OF_LINE(),
                        $fragment
                    );

                    $iterator->next();
                break;

                default:
                    if ($capture === null) {
                        $capture = $fragment;
                    } else {
                        $capture = $capture->append($fragment);
                    }
                    $iterator->next();
                break;
            }
        }
    }
}