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

namespace PackageFactory\ComponentEngine\Parser\Parser\Expression;

use PackageFactory\ComponentEngine\Definition\Precedence;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Parser\Access\AccessParser;
use PackageFactory\ComponentEngine\Parser\Parser\BinaryOperation\BinaryOperationParser;
use PackageFactory\ComponentEngine\Parser\Parser\BooleanLiteral\BooleanLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\Identifier\IdentifierParser;
use PackageFactory\ComponentEngine\Parser\Parser\Match\MatchParser;
use PackageFactory\ComponentEngine\Parser\Parser\NullLiteral\NullLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\NumberLiteral\NumberLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\PrecedenceParser;
use PackageFactory\ComponentEngine\Parser\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Parser\Parser\TernaryOperation\TernaryOperationParser;
use PackageFactory\ComponentEngine\Parser\Parser\UnaryOperation\UnaryOperationParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\{any, between, either, pure, skipSpace};

final class ExpressionParser
{
    /** @return Parser<ExpressionNode> */
    public static function get(Precedence $precedence = Precedence::SEQUENCE): Parser
    {
        $expressionRootParser = between(skipSpace(), skipSpace(), any(
            NumberLiteralParser::get(),
            BooleanLiteralParser::get(),
            NullLiteralParser::get(),
            MatchParser::get(),
            TagParser::get(),
            StringLiteralParser::get(),
            IdentifierParser::get(),
            UnaryOperationParser::get()
        ));

        return $expressionRootParser->map(fn ($expressionRoot) => new ExpressionNode($expressionRoot))
            ->bind(fn ($expressionNode) => self::continueParsingWhilePrecedence($expressionNode, $precedence)
        );
    }

    /** @return Parser<ExpressionNode> */
    private static function continueParsingWhilePrecedence(ExpressionNode $expressionNode, Precedence $precedence): Parser
    {
        $continuationParser = any(
            BinaryOperationParser::get($expressionNode),
            AccessParser::get($expressionNode),
            TernaryOperationParser::get($expressionNode)
        )
            ->thenIgnore(skipSpace())
            ->bind(
                fn ($expressionRoot) => self::continueParsingWhilePrecedence(new ExpressionNode($expressionRoot), $precedence)
            );

        return either(
            PrecedenceParser::hasPrecedence($precedence)->sequence($continuationParser),
            pure($expressionNode)
        );
    }
}
