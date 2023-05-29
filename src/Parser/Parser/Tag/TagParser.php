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

namespace PackageFactory\ComponentEngine\Parser\Parser\Tag;

use PackageFactory\ComponentEngine\Parser\Ast\TagContentNodes;
use PackageFactory\ComponentEngine\Parser\Ast\TagNode;
use PackageFactory\ComponentEngine\Parser\Parser\Attribute\AttributeParser;
use PackageFactory\ComponentEngine\Parser\Parser\TagContent\TagContentParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\alphaChar;
use function Parsica\Parsica\assemble;
use function Parsica\Parsica\char;
use function Parsica\Parsica\either;
use function Parsica\Parsica\isAlphaNum;
use function Parsica\Parsica\isEqual;
use function Parsica\Parsica\orPred;
use function Parsica\Parsica\skipSpace;
use function Parsica\Parsica\string;
use function Parsica\Parsica\takeWhile;

final class TagParser
{
    private static ?Parser $instance = null;

    public static function get(): Parser
    {
        return self::$instance ??= self::build();
    }
    private static function build(): Parser
    {
        return char('<')->sequence(
            self::tagName()->bind(fn (string $tagName) =>
                skipSpace()->followedBy(AttributeParser::get())->thenIgnore(skipSpace())->bind(fn ($attributeNodes) =>
                    either(
                        string('/>')
                            ->map(fn () => new TagNode($tagName, $attributeNodes, new TagContentNodes(), true)),
                        char('>')->followedBy(TagContentParser::get())->thenIgnore(self::tagClosing($tagName))
                            ->map(fn ($tagContents) => new TagNode($tagName, $attributeNodes, $tagContents, false))
                    )
                )
            )
        );
    }

    private static function tagName(): Parser
    {
        // @todo specification
        return alphaChar()->append(takeWhile(orPred(isAlphaNum(), isEqual('-'))));
    }

    private static function tagClosing(string $tagName): Parser
    {
        return assemble(
            string('</'),
            skipSpace(),
            string($tagName),
            skipSpace(),
            char('>')
        );
    }
}
