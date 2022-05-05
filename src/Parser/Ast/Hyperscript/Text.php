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

namespace PackageFactory\ComponentEngine\Parser\Ast\Hyperscript;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class Text implements \JsonSerializable
{
    private function __construct(
        public readonly string $value
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return null|self
     */
    public static function fromTokens(\Iterator $tokens): ?self
    {
        Scanner::skipSpace($tokens);

        $value = '';
        while (true) {
            switch (Scanner::type($tokens)) {
                case TokenType::BRACKET_OPEN:
                    if (Scanner::value($tokens) === '{') {
                        break 2;
                    }
                case TokenType::TAG_START_OPENING:
                case TokenType::TAG_START_CLOSING:
                    break 2;
                case TokenType::SPACE:
                case TokenType::END_OF_LINE:
                    $value .= ' ';
                    Scanner::skipSpaceAndComments($tokens);
                    break;
                default:
                    $value .= Scanner::value($tokens);
                    Scanner::skipOne($tokens);
                    break;
            }
        }

        return $value ? new self(rtrim($value)) : null;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'Text',
            'payload' => [
                'value' => $this->value
            ]
        ];
    }
}
