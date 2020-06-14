<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Tokenize;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class ImportStatement
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

        
        if ($importKeyWord = $iterator->lookAhead(mb_strlen('import'))) {
            yield Token::createFromFragment(
                TokenType::KEYWORD_IMPORT(),
                $importKeyWord
            );

            $iterator->skip(mb_strlen('import'));
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }

        foreach (Identifier::tokenize($iterator) as $token) {
            yield $token;
        }

        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }

        if ($fromKeyWord = $iterator->lookAhead(mb_strlen('from'))) {
            yield Token::createFromFragment(
                TokenType::KEYWORD_FROM(),
                $fromKeyWord
            );

            $iterator->skip(mb_strlen('from'));
        }
        else {
            throw new \Exception('@TODO: Exception');
        }

        foreach (Whitespace::tokenize($iterator) as $token) {
            yield $token;
        }

        foreach (StringLiteral::tokenize($iterator) as $token) {
            yield $token;
        }
    }
}