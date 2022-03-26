<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;

final class Export implements \JsonSerializable
{
    private function __construct(
        public readonly Identifier $name,
        public readonly Term $value
    ) {
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $stream->consume(TokenType::MODULE_KEYWORD_EXPORT);
        $stream->skipWhiteSpaceAndComments();

        switch ($stream->current()->type) {
            case TokenType::MODULE_KEYWORD_CONST:
                return self::fromConstant(
                    Constant::fromTokenStream($stream)
                );
            case TokenType::MODULE_KEYWORD_DEFAULT:
                $name = Identifier::fromToken($stream->current());
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::MODULE_KEYWORD_CONST,
                        TokenType::MODULE_KEYWORD_DEFAULT
                    ]
                );
        }

        $value = null;
        $brackets = 0;
        while ($value === null) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::BRACKETS_ROUND_OPEN:
                    $brackets++;
                    $stream->next();
                    break;
                case TokenType::AFX_TAG_START:
                    $value = Tag::fromTokenStream($stream);
                    break;
                default:
                    $value = ExpressionParser::parseTerm($stream);
                    break;
            }
        }

        while ($brackets > 0) {
            $stream->skipWhiteSpaceAndComments();
            $stream->consume(TokenType::BRACKETS_ROUND_CLOSE);
            $brackets--;
        }

        return new self(
            name: $name,
            value: $value
        );
    }

    public static function fromConstant(Constant $constant): self
    {
        throw new \Exception('@TODO: Export::fromTokenStream');
    }

    public function jsonSerialize(): mixed
    {
        throw new \Exception('@TODO: Export::jsonSerialize');
    }
}
