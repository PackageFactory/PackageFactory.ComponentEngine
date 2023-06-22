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

namespace PackageFactory\ComponentEngine\Target\Php\TargetSpecific;

final class ClassName
{
    /**
     * as per https://www.php.net/manual/en/language.oop5.basic.php
     */
    private const VALID_LABEL_REGEX = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';

    /**
     * as per https://www.php.net/manual/en/reserved.other-reserved-words.php
     * and https://www.php.net/manual/en/reserved.keywords.php
     */
    private const RESERVED_WORDS = [
        'int', 'float', 'bool', 'string', 'true', 'false', 'null', 'void',
        'iterable', 'object', 'mixed', 'never', 'enum', 'resource', 'numeric',
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'break',
        'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue',
        'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty',
        'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile',
        'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach',
        'function', 'global', 'goto', 'if', 'implements', 'include',
        'include_once', 'instanceof', 'insteadof', 'interface', 'isset',
        'list', 'match', 'namespace', 'new', 'or', 'print', 'private',
        'protected', 'public', 'readonly', 'require', 'require_once', 'return',
        'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var',
        'while', 'xor', 'yield', '__class__', '__dir__', '__file__',
        '__function__', '__line__', '__method__', '__namespace__', '__trait__',
    ];

    /**
     * @var array<string,ClassName>
     */
    private static array $instances;

    /**
     * @var string[]
     */
    private array $segments;

    private function __construct(private readonly string $fullyQualifiedClassName)
    {
        $this->segments = explode('\\', $this->fullyQualifiedClassName);

        foreach ($this->segments as $segment) {
            if (preg_match(self::VALID_LABEL_REGEX, $segment) === 0) {
                throw new \Exception('@TODO: Invalid class name (due to format)');
            }

            if (in_array(strtolower($segment) , self::RESERVED_WORDS)) {
                throw new \Exception('@TODO: Invalid class name (due to resrerved word)');
            }
        }
    }

    public static function fromString(string $string): self
    {
        return self::$instances[$string] ??= new self($string);
    }

    public function getFullyQualifiedClassName(): string
    {
        return $this->fullyQualifiedClassName;
    }

    public function getNamespace(): string
    {
        return join('\\', array_slice($this->segments, 0, -1));
    }

    public function getShortClassName(): string
    {
        return $this->segments[count($this->segments) - 1];
    }
}
