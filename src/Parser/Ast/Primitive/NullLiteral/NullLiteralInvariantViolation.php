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

namespace PackageFactory\ComponentEngine\Parser\Ast\Primitive\NullLiteral;

use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class NullLiteralInvariantViolation extends \Exception
{
    private function __construct(
        public readonly int $errorCode,
        public readonly string $errorMessage,
        public readonly ?\Exception $cause = null
    ) {
        parent::__construct(
            'NullLiteral could not be created: ' . $errorMessage,
            $errorCode,
            $cause
        );
    }

    public static function becauseOfUnexpectedToken(Token $token): self
    {
        return new self(
            errorCode: 1648332644,
            errorMessage: sprintf(
                'Expected token of type %s, got %s ("%s") instead.',
                TokenType::KEYWORD->name,
                $token->type->name,
                $token->value
            )
        );
    }

    public static function becauseOfUnexpectedKeyWord(string $keyword): self
    {
        return new self(
            errorCode: 1648379956,
            errorMessage: sprintf(
                'Expected keyword "null", got "%s" instead.',
                $keyword
            )
        );
    }
}
