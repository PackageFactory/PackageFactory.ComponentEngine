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

namespace PackageFactory\ComponentEngine\Parser\Parser\BinaryOperation;

use PackageFactory\ComponentEngine\Definition\BinaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\BinaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Parser\Expression\ExpressionParser;
use Parsica\Parsica\Internal\Succeed;
use Parsica\Parsica\Parser;

use Parsica\Parsica\ParseResult;
use Parsica\Parsica\Stream;

use function Parsica\Parsica\any;
use function Parsica\Parsica\char;
use function Parsica\Parsica\string;

final class BinaryOperationParser
{
    public static function get(ExpressionNode $left): Parser
    {
        return Parser::make('binary operation', function (Stream $stream) use ($left): ParseResult {
            $result = self::binaryOperatorParser()->run($stream);
            if ($result->isFail()) {
                return $result;
            }
            $binaryOperator = $result->output();
            assert($binaryOperator instanceof BinaryOperator);
            $result = ExpressionParser::get($binaryOperator->toPrecedence())->continueFrom($result);
            if ($result->isFail()) {
                return $result;
            }
            return new Succeed(
                new BinaryOperationNode($left, $binaryOperator, $result->output()),
                $result->remainder()
            );
        });
    }

    public static function binaryOperatorParser(): Parser
    {
        return any(
            char('+')->map(fn () => BinaryOperator::PLUS),
            char('-')->map(fn () => BinaryOperator::MINUS),
            char('*')->map(fn () => BinaryOperator::MULTIPLY_BY),
            char('/')->map(fn () => BinaryOperator::DIVIDE_BY),
            char('%')->map(fn () => BinaryOperator::MODULO),
            string('&&')->map(fn () => BinaryOperator::AND),
            string('||')->map(fn () => BinaryOperator::OR),
            string('>=')->map(fn () => BinaryOperator::GREATER_THAN_OR_EQUAL),
            char('>')->map(fn () => BinaryOperator::GREATER_THAN),
            string('<=')->map(fn () => BinaryOperator::LESS_THAN_OR_EQUAL),
            char('<')->map(fn () => BinaryOperator::LESS_THAN),
            string('===')->map(fn () => BinaryOperator::EQUAL),
            string('!==')->map(fn () => BinaryOperator::NOT_EQUAL),
        );
    }
}
