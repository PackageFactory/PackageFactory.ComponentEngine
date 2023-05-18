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
use PackageFactory\ComponentEngine\Parser\Parser\NullLiteral\NullLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\NumberLiteral\NumberLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\StringLiteral\StringLiteralParser;
use PackageFactory\ComponentEngine\Parser\Parser\TernaryOperation\TernaryOperationParser;
use PackageFactory\ComponentEngine\Parser\Parser\UnaryOperation\UnaryOperationParser;
use Parsica\Parsica\Internal\Succeed;
use Parsica\Parsica\Parser;

use Parsica\Parsica\ParseResult;
use Parsica\Parsica\Stream;

use function Parsica\Parsica\{any, between, skipSpace};

final class ExpressionParser
{
    public static function get(Precedence $precedence = Precedence::SEQUENCE): Parser
    {
        return Parser::make('Expression', function (Stream $stream) use ($precedence): ParseResult {
            $expressionRootParser = between(skipSpace(), skipSpace(), any(
                NumberLiteralParser::get(),
                BooleanLiteralParser::get(),
                NullLiteralParser::get(),
                StringLiteralParser::get(),
                IdentifierParser::get(),
                UnaryOperationParser::get()
            ));

            $parseResult = $expressionRootParser->run($stream);
            if ($parseResult->isFail()) {
                return $parseResult;
            }
            $expressionNode = new ExpressionNode($parseResult->output());
            $remainder = $parseResult->remainder();

            while ($parseResult->isSuccess() && !$precedence->mustStopAt(Precedence::fromRemainder($remainder))) {
                $parseResult = any(
                    BinaryOperationParser::get($expressionNode),
                    AccessParser::get($expressionNode),
                    TernaryOperationParser::get($expressionNode),
                )->thenIgnore(skipSpace())->continueFrom($parseResult);

                if ($parseResult->isSuccess()) {
                    $expressionNode = new ExpressionNode($parseResult->output());
                    $remainder = $parseResult->remainder();
                }
            }

            return new Succeed(
                $expressionNode,
                $remainder
            );
        });
    }
}
