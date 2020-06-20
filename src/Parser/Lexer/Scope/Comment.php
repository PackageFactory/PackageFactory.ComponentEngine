<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Comment
{
    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        /** @var Fragment|null $capture */
        $capture = null;
        
        $delimiter = null;
        if ($lookAhead = $iterator->lookAhead(2)) {
            if ($lookAhead->getValue() === '//') {
                yield Token::createFromFragment(
                    TokenType::COMMENT_START(),
                    $lookAhead
                );
                $delimiter = PHP_EOL;
                $iterator->skip(2);
            } elseif ($lookAhead->getValue() === '/*') {
                yield Token::createFromFragment(
                    TokenType::COMMENT_START(),
                    $lookAhead
                );
                $delimiter = '*/';
                $iterator->skip(2);
            }
        }

        while ($iterator->valid()) {
            $fragment = $iterator->current();
            $value = $fragment->getValue();

            if ($delimiter && $value === $delimiter) {
                break;
            } elseif ($delimiter && $value === $delimiter[0]) {
                if ($lookAhead = $iterator->lookAhead(2)) {
                    if ($lookAhead->getValue() === $delimiter) {
                        if ($capture !== null) {
                            yield Token::createFromFragment(
                                TokenType::COMMENT_CONTENT(),
                                $capture
                            );
                
                            $capture = null;
                        }

                        yield Token::createFromFragment(
                            TokenType::COMMENT_END(),
                            $lookAhead
                        );
                        $iterator->skip(2);
                        break;
                    }
                }
            } elseif ($capture === null) {
                $capture = $fragment;
                $iterator->next();
            } else {
                $capture = $capture->append($fragment);
                $iterator->next();
            }
        }

        if ($capture !== null) {
            yield Token::createFromFragment(
                TokenType::COMMENT_CONTENT(),
                $capture
            );

            $capture = null;
        }
    }
}