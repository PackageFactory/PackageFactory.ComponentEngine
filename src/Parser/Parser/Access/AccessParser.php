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

namespace PackageFactory\ComponentEngine\Parser\Parser\Access;

use PackageFactory\ComponentEngine\Definition\AccessType;
use PackageFactory\ComponentEngine\Parser\Ast\AccessChainSegmentNode;
use PackageFactory\ComponentEngine\Parser\Ast\AccessChainSegmentNodes;
use PackageFactory\ComponentEngine\Parser\Ast\AccessNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Parser\Identifier\IdentifierParser;
use Parsica\Parsica\Parser;

use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\either;
use function Parsica\Parsica\string;

final class AccessParser
{
    public static function get(ExpressionNode $subject): Parser
    {
        return atLeastOne(
            collect(
                self::accessType(),
                IdentifierParser::get()
            )->map(fn ($result) => [new AccessChainSegmentNode($result[0], $result[1])])
        )->map(fn ($segments) => new AccessNode(
            $subject,
            new AccessChainSegmentNodes(...$segments)
        ));
    }

    private static function accessType(): Parser
    {
        return either(
            string('?.')->map(fn () => AccessType::OPTIONAL),
            char('.')->map(fn () => AccessType::MANDATORY)
        );
    }
}
