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

namespace PackageFactory\ComponentEngine\Language\Parser\ValueReference;

use PackageFactory\ComponentEngine\Domain\VariableName\VariableName;
use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\ValueReference\ValueReferenceNode;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ValueReferenceParser
{
    use Singleton;

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return ValueReferenceNode
     */
    public function parse(\Iterator &$tokens): ValueReferenceNode
    {
        Scanner::assertType($tokens, TokenType::STRING);

        $token = $tokens->current();

        Scanner::skipOne($tokens);

        return new ValueReferenceNode(
            rangeInSource: $token->boundaries,
            name: VariableName::from($token->value)
        );
    }
}
