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

namespace PackageFactory\ComponentEngine\Language\Parser\BooleanLiteral;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\BooleanLiteral\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class BooleanLiteralParser
{
    use Singleton;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return BooleanLiteralNode
     */
    public function parse(\Iterator &$tokens): BooleanLiteralNode
    {
        Scanner::assertType($tokens, TokenType::KEYWORD_TRUE, TokenType::KEYWORD_FALSE);

        $token = $tokens->current();
        $value = $token->type === TokenType::KEYWORD_TRUE;

        Scanner::skipOne($tokens);

        return new BooleanLiteralNode(
            rangeInSource: $token->boundaries,
            value: $value
        );
    }
}
