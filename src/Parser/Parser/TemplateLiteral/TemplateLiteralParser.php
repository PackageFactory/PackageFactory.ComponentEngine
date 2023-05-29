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

namespace PackageFactory\ComponentEngine\Parser\Parser\TemplateLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Parser\Parser\Expression\ExpressionParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\any;
use function Parsica\Parsica\char;
use function Parsica\Parsica\string;
use function Parsica\Parsica\takeWhile1;
use function Parsica\Parsica\zeroOrMore;

final class TemplateLiteralParser
{
    /** @return Parser<TemplateLiteralNode> */
    public static function get(): Parser
    {
        return char('`')->sequence(
            zeroOrMore(
                any(
                    self::stringLiteral(),
                    self::expression(),
                )->map(fn ($item) => [$item])
            )->map(fn ($collected) => new TemplateLiteralNode(...$collected ?? []))
                ->thenIgnore(char('`'))
        );
    }

    private static function expression(): Parser
    {
        return string('${')->followedBy(ExpressionParser::get())->thenIgnore(char('}'));
    }

    private static function stringLiteral(): Parser
    {
        // @todo escapes? or allow `single unescaped $ dollar?`
        return takeWhile1(fn ($char) => $char !== '$' && $char !== '`')->map(fn ($text) => new StringLiteralNode($text));
    }
}
