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

namespace PackageFactory\ComponentEngine\Exception;

use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class ParserFailed extends \Exception
{

    private function __construct(public readonly ?Token $token, string $message)
    {
        parent::__construct('Parser failed: ' . $message);
    }

    /**
     * @param Token $token
     * @param array|TokenType[] $expectedTypes
     * @return self
     */
    public static function becauseOfUnexpectedToken(
        Token $token,
        array $expectedTypes = []
    ): self {
        $message = sprintf(
            'Encountered unexpected token "%s" of type %s.',
            $token->value,
            $token->type->name
        );

        if ($count = count($expectedTypes)) {
            if ($count > 1) {
                $last = array_pop($expectedTypes);

                $message .= sprintf(
                    ' Expected one of %s or %s.',
                    join(', ', array_map(fn (TokenType $type) => $type->name, $expectedTypes)),
                    $last->name
                );
            } else {
                $message .= sprintf(
                    ' Expected %s.',
                    $expectedTypes[0]->name
                );
            }
        }

        return new self($token, $message);
    }

    /**
     * @param Token $token
     * @param Term $term
     * @param array<int, class-string> $expectedTypes
     * @return self
     */
    public static function becauseOfUnexpectedTerm(
        Token $token,
        Term $term,
        array $expectedTypes = []
    ): self {
        $message = sprintf(
            'Encountered unexpected term of type %s.',
            (new \ReflectionClass($term))->getShortName()
        );

        if ($count = count($expectedTypes)) {
            if ($count > 1) {
                $last = array_pop($expectedTypes);

                $message .= sprintf(
                    ' Expected one of %s or %s.',
                    join(', ', array_map(
                        function (string $type) {
                            /** @var class-string $type */
                            return (new \ReflectionClass($type))->getShortName();
                        },
                        $expectedTypes
                    )),
                    $last
                );
            } else {
                $message .= sprintf(
                    ' Expected %s.',
                    (new \ReflectionClass($expectedTypes[0]))->getShortName()
                );
            }
        }

        return new self($token, $message);
    }

    public static function becauseOfUnexpectedClosingTag(Token $token): self
    {
        return new self($token, 'Encountered unexpected closing tag.');
    }

    public static function becauseOfUnexpectedEndOfFile(TokenStream $stream): self
    {
        return new self($stream->getLast(), 'Encountered unexpected end of file.');
    }

    public static function becauseOfUnknownOperator(Token $token): self
    {
        return new self(
            $token,
            sprintf(
                'Encountered unknown operator "%s".',
                $token->value
            )
        );
    }

    public static function becauseOfUnknownComparator(Token $token): self
    {
        return new self(
            $token,
            sprintf(
                'Encountered unknown comparator "%s".',
                $token->value
            )
        );
    }
}
