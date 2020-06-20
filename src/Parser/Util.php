<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Util
{   
    /**
     * @param \Iterator<Token> $iterator
     * @return void
     */
    public static function skipWhiteSpaceAndComments(\Iterator $iterator): void
    {
        while (
            $iterator->valid() && 
            (
                $iterator->current()->getType() === TokenType::WHITESPACE() ||
                $iterator->current()->getType() === TokenType::END_OF_LINE() ||
                $iterator->current()->getType() === TokenType::COMMENT_START() ||
                $iterator->current()->getType() === TokenType::COMMENT_CONTENT() ||
                $iterator->current()->getType() === TokenType::COMMENT_END()
            )
        ) {
            $iterator->next();
        }
    }

    /**
     * Undocumented function
     *
     * @param \Iterator<Token> $iterator
     * @param TokenType $type
     * @return Token
     */
    public static function expect(\Iterator $iterator, TokenType $type): Token
    {
        if ($iterator->current()->getType() === $type) {
            $result = $iterator->current();
            $iterator->next();
            return $result;
        }
        else {
            throw new \Exception(
                sprintf(
                    '@TODO: Unexpected %s "%s", expected "%s" (Line %s)',
                    $iterator->current()->getType(),
                    $iterator->current(),
                    $type,
                    $iterator->current()->getStart()->getRowIndex() + 1
                )
            );
        }
    }
}