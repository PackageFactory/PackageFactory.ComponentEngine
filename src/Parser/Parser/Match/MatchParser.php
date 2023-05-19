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

namespace PackageFactory\ComponentEngine\Parser\Parser\Match;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNodes;
use PackageFactory\ComponentEngine\Parser\Ast\MatchArmNode;
use PackageFactory\ComponentEngine\Parser\Ast\MatchArmNodes;
use PackageFactory\ComponentEngine\Parser\Ast\MatchNode;
use PackageFactory\ComponentEngine\Parser\Parser\Expression\ExpressionParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\either;
use function Parsica\Parsica\skipSpace;
use function Parsica\Parsica\string;
use function Parsica\Parsica\zeroOrMore;

final class MatchParser
{
    private static ?Parser $instance = null;

    public static function get(): Parser
    {
        return self::$instance ??= self::build();
    }

    private static function build(): Parser
    {
        // @todo for some reason we must use bind here to avoid infinite recursion
        return string('match')
            ->bind(fn () => skipSpace()
                ->followedBy(char('('))
                ->followedBy(ExpressionParser::get())
                ->thenIgnore(char(')'))
                ->thenIgnore(skipSpace())
                ->thenIgnore(char('{'))
                ->thenIgnore(skipSpace())
                ->bind(fn ($expression) => self::getMatchArmsParser()->map(fn ($matchArmNodes) => new MatchNode($expression, $matchArmNodes)))
                ->thenIgnore(skipSpace())
                ->thenIgnore(char('}'))
            );
    }

    private static function getMatchArmParser(): Parser
    {
        return collect(
            either(
                string('default')->map(fn () => null),
                ExpressionParser::get()->map(fn (ExpressionNode $expressionNode) => new ExpressionNodes($expressionNode))
            ),
            skipSpace(),
            string('->'),
            skipSpace(),
            ExpressionParser::get(),
            skipSpace(),
        )->map(fn ($collected) => new MatchArmNode($collected[0], $collected[4]));

    }

    private static function getMatchArmsParser(): Parser
    {
        return zeroOrMore(
            self::getMatchArmParser()->map(fn ($matchArmNode) => [$matchArmNode])
        )->map(fn ($matchArmNodes) => new MatchArmNodes(...$matchArmNodes ? $matchArmNodes : []));
    }
}
