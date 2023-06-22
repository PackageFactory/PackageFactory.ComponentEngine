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

namespace PackageFactory\ComponentEngine\Parser\Parser\TagContent;

use PackageFactory\ComponentEngine\Parser\Ast\TagContentNode;
use PackageFactory\ComponentEngine\Parser\Ast\TagContentNodes;
use PackageFactory\ComponentEngine\Parser\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Parser\Tag\TagParser;
use PackageFactory\ComponentEngine\Parser\Parser\Text\TextParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\any;
use function Parsica\Parsica\char;
use function Parsica\Parsica\many;
use function Parsica\Parsica\skipSpace;

final class TagContentParser
{
    /** @return Parser<TagContentNodes> */
    public static function get(): Parser
    {
        return skipSpace()->sequence(
            many(
                self::tagContent()
            )->map(fn ($collected) => new TagContentNodes(...array_filter($collected ?? [])))
        );
    }

    private static function tagContent(): Parser
    {
        return any(
            TagParser::get(),
            TextParser::get(),
            char('{')->followedBy(ExpressionParser::get())->thenIgnore(char('}')),
        )->map(fn ($item) => $item ? new TagContentNode($item) : null);
    }
}