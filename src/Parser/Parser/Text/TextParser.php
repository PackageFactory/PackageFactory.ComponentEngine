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

namespace PackageFactory\ComponentEngine\Parser\Parser\Text;

use PackageFactory\ComponentEngine\Parser\Ast\TextNode;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\takeWhile1;

final class TextParser
{
    private static ?Parser $instance = null;

    public static function get(): Parser
    {
        return self::$instance ??= self::build();
    }

    private static function build(): Parser
    {
        // @todo space handling and valid chars
        return takeWhile1(fn ($char) => $char !== '<' && $char !== '{')->map(fn ($text) => new TextNode($text));
    }
}
