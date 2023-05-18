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

namespace PackageFactory\ComponentEngine\Parser\Parser\UnaryOperation;

use PackageFactory\ComponentEngine\Definition\Precedence;
use PackageFactory\ComponentEngine\Definition\UnaryOperator;
use PackageFactory\ComponentEngine\Parser\Ast\UnaryOperationNode;
use PackageFactory\ComponentEngine\Parser\Parser\Expression\ExpressionParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;

final class UnaryOperationParser
{
    private static ?Parser $instance = null;

    public static function get(): Parser
    {
        return self::$instance ??= self::build();
    }

    private static function build(): Parser
    {
        return collect(
            self::unaryOperator(),
            ExpressionParser::get(Precedence::UNARY)
        )->map(fn ($result) => new UnaryOperationNode($result[0], $result[1]));
    }

    private static function unaryOperator(): Parser
    {
        return char('!')->map(fn () => UnaryOperator::NOT);
    }
}
