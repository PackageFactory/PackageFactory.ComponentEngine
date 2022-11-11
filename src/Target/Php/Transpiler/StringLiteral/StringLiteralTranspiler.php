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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\StringLiteral;

use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;

final class StringLiteralTranspiler
{
    public function __construct(
        private bool $shouldAddQuotes = false
    ) {
    }

    public function transpile(StringLiteralNode $stringLiteralNode): string
    {
        $result = $stringLiteralNode->value;
        $shouldAddLeadingQuote = $this->shouldAddQuotes;
        $shouldAddTrailingQuote = $this->shouldAddQuotes;

        if (strpos($result, "\n") !== false) {
            $lines = explode("\n", $result);
            $result = array_shift($lines);
            $additionalLineBreaks = '';
            $shouldAddLeadingQuote = $shouldAddLeadingQuote && $result !== '';
            foreach ($lines as $line) {
                if ($line === '') {
                    $additionalLineBreaks .= '\n';
                } else {
                    $result .= $result 
                        ?  '\' . "\n' . $additionalLineBreaks . '" . \'' . $line
                        :  '"\n' . $additionalLineBreaks . '" . \'' . $line;
                    $additionalLineBreaks = '';
                }
            }

            if ($additionalLineBreaks) {
                $result .= $result 
                    ? '\' . "' . $additionalLineBreaks . '"'
                    : '"' . $additionalLineBreaks . '"';
                $shouldAddTrailingQuote = false;
            }
        }

        if ($shouldAddLeadingQuote) {
            $result = '\'' . $result;
        }

        if ($shouldAddTrailingQuote) {
            $result .= '\'';
        }

        return $result;
    }
}
