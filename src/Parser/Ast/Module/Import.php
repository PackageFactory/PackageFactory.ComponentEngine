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
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;

final class Import implements \JsonSerializable
{
    private function __construct(
        public readonly string $domesticName,
        public readonly string $foreignName,
        public readonly string $target
    ) {
    }

    /**
     * @param TokenStream $stream
     * @return \Iterator<mixed, self>
     */
    public static function fromTokenStream(TokenStream $stream): \Iterator
    {
        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::MODULE_KEYWORD_IMPORT);
        $stream->skipWhiteSpaceAndComments();

        $importMap = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->type) {
                case TokenType::IDENTIFIER:
                    $importMap[$stream->current()->value] = 'default';
                    $stream->next();
                    break;
                case TokenType::MODULE_KEYWORD_FROM:
                    $stream->next();
                    break 2;
                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::IDENTIFIER,
                            TokenType::MODULE_KEYWORD_FROM
                        ]
                    );
            }
        }

        $stream->skipWhiteSpaceAndComments();

        $target = StringLiteral::fromTokenStream($stream);

        foreach ($importMap as $domesticName => $foreignName) {
            /** @var string $domesticName */
            yield $domesticName => new self(
                domesticName: $domesticName,
                foreignName: $foreignName,
                target: (string) $target
            );
        }
    }

    public function jsonSerialize(): mixed
    {
        throw new \Exception('@TODO: Import::jsonSerialize');
    }
}
