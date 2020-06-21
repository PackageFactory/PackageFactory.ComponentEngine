<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Afx
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        $open = false;
        $tags = 0;
        while ($iterator->valid()) {
            yield from Whitespace::tokenize($iterator);

            $value = $iterator->current()->getValue();

            if ($value === '<') {
                yield Token::createFromFragment(
                    TokenType::AFX_TAG_START(),
                    $iterator->current()
                );
                $iterator->next();
                $open = true;
            } elseif ($value === '/') {
                yield Token::createFromFragment(
                    TokenType::AFX_TAG_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
                if ($open) {
                    $open = false;
                }
            } elseif ($value === '>') {
                yield Token::createFromFragment(
                    TokenType::AFX_TAG_END(),
                    $iterator->current()
                );
                $iterator->next();

                if ($open) {
                    $tags++;
                } else {
                    $tags--;
                }

                if ($tags === 0) {
                    return;
                }

                yield from Whitespace::tokenize($iterator);

                if ($lookAhead = $iterator->lookAhead(1)) {
                    if ($lookAhead->getValue() !== '<') {
                        yield from self::tokenizeContent($iterator);
                    }
                }
            } elseif ($value === '=') {
                yield Token::createFromFragment(
                    TokenType::AFX_ATTRIBUTE_ASSIGNMENT(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '"') {
                yield from StringLiteral::tokenize($iterator);
            } elseif ($value === '{') {
                yield Token::createFromFragment(
                    TokenType::AFX_EXPRESSION_START(),
                    $iterator->current()
                );
                $iterator->next();

                yield from Expression::tokenize($iterator, ['}']);
            } elseif ($value === '}') { 
                yield Token::createFromFragment(
                    TokenType::AFX_EXPRESSION_END(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif (Identifier::is($value)) {
                yield from Identifier::tokenize($iterator);
            } else {
                break;
            }
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeContent(SourceIterator $iterator): \Iterator
    {
        /** @var null|Fragment $capture */
        $capture = null;

        while ($iterator->valid()) {
            $value = $iterator->current()->getValue();

            if ($value === '<') {
                break;
            } elseif (ctype_space($value)) {
                if ($capture !== null) {
                    yield Token::createFromFragment(
                        TokenType::AFX_TAG_CONTENT(),
                        $capture
                    );

                    $capture = null;
                }

                yield from Whitespace::tokenize($iterator);
            } elseif ($value === '{') {
                if ($capture !== null) {
                    yield Token::createFromFragment(
                        TokenType::AFX_TAG_CONTENT(),
                        $capture
                    );

                    $capture = null;
                }

                yield Token::createFromFragment(
                    TokenType::AFX_EXPRESSION_START(),
                    $iterator->current()
                );
                $iterator->next();

                yield from Expression::tokenize($iterator, ['}']);
            } elseif ($value === '}') {
                if ($capture !== null) {
                    yield Token::createFromFragment(
                        TokenType::AFX_TAG_CONTENT(),
                        $capture
                    );

                    $capture = null;
                }

                yield Token::createFromFragment(
                    TokenType::AFX_EXPRESSION_END(),
                    $iterator->current()
                );
                $iterator->next();
            } else {
                if ($capture === null) {
                    $capture = $iterator->current();
                } else {
                    $capture = $capture->append($iterator->current());
                }
                $iterator->next();
            }
        }

        if ($capture !== null) {
            yield Token::createFromFragment(
                TokenType::AFX_TAG_CONTENT(),
                $capture
            );
        }
    }
}