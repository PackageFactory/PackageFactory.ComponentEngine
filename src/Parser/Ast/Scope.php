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

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Ast\Reference\Identifier;
use PackageFactory\ComponentEngine\Type\Record\RecordType;
use PackageFactory\ComponentEngine\Type\Type;

final class Scope
{
    private function __construct(
        private readonly null | Scope $parent,
        private readonly ModuleNode|RecordType $node
    ) {
    }

    public static function fromGlobals(RecordType $globals): self
    {
        return new self(null, $globals);
    }

    public function push(ModuleNode|RecordType $node): self
    {
        return new self($this, $node);
    }

    public function lookUpType(Identifier $identifier): Type
    {
        $type = match ($this->node::class) {
            Module::class => $this->node->getExport($identifier->name)?->toType($this),
            RecordType::class => $this->node->get($identifier->name)?->type
        };

        return $type ?? $this->parent?->lookUpType($identifier) ?? throw new \Exception('@TODO: ' . $identifier->name . ' is undefined');
    }
}
