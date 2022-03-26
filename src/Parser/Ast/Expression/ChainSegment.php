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

namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Key;

final class ChainSegment implements \JsonSerializable
{
    private function __construct(
        public readonly bool $isOptional,
        public readonly Key $key,
        public readonly ?Call $call
    ) {
    }

    public static function fromKey(
        bool $isOptional,
        Key $key
    ): self {
        return new self(
            isOptional: $isOptional,
            key: $key,
            call: null
        );
    }

    public function getIsCallable(): bool
    {
        return $this->call !== null;
    }

    public function withCall(Call $call): self
    {
        return new self(
            isOptional: $this->isOptional,
            key: $this->key,
            call: $call
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ChainSegment',
            'isOptional' => $this->isOptional,
            'key' => $this->key,
            'call' => $this->call
        ];
    }
}
