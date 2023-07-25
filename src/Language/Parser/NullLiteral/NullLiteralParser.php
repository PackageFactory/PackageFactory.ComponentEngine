<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\Parser\NullLiteral;

use PackageFactory\ComponentEngine\Language\AST\NullLiteral\NullLiteralNode;
use PackageFactory\ComponentEngine\Language\Shared\Location\Location;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class NullLiteralParser
{
    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return NullLiteralNode
     */
    public function parse(\Iterator $tokens): NullLiteralNode
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_NULL);

        $token = $tokens->current();

        Scanner::skipOne($tokens);

        return new NullLiteralNode(
            location: new Location(
                sourcePath: $token->sourcePath,
                boundaries: $token->boundaries
            )
        );
    }
}
