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

namespace PackageFactory\ComponentEngine\Target\Php\Transpiler\TemplateLiteral;

use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Target\Php\Transpiler\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class TemplateLiteralTranspiler
{
    private ?ExpressionTranspiler $expressionTranspiler = null;

    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    public function transpile(TemplateLiteralNode $templateLiteralNode): string
    {
        $lines = [];
        $emptyLines = 0;
        $isFirstLine = true;
        foreach ($templateLiteralNode->lines->items as $line) {
            if (count($line->segments->items) === 0) {
                $emptyLines++;
                continue;
            }

            $segments = [];
            foreach ($line->segments->items as $segmentNode) {
                $segments[] = match ($segmentNode::class) {
                    TemplateLiteralStringSegmentNode::class => $this->transpileStringSegment($segmentNode),
                    TemplateLiteralExpressionSegmentNode::class => $this->transpileExpressionSegment($segmentNode)
                };
            }

            $next = str_repeat(' ', $line->indentation - $templateLiteralNode->indentation) . join(' . ', $segments);
            if (!$isFirstLine) {
                $next = ' . "' . str_repeat('\n', $emptyLines + 1) . '" . ' . $next;
            }

            $lines[] = $next;
            $emptyLines = 0;
            $isFirstLine = false;
        }

        return join('', $lines);
    }

    private function transpileStringSegment(TemplateLiteralStringSegmentNode $segmentNode): string
    {
        $result = $segmentNode->value;
        $shouldAddTrailingQuote = true;
        $shouldAddLeadingQuote = true;

        if (strpos($result, "\n") !== false) {
            $lines = explode("\n", $result);
            $result = array_shift($lines);
            $additionalLineBreaks = '';
            $shouldAddLeadingQuote = $result !== '';

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

    private function transpileExpressionSegment(TemplateLiteralExpressionSegmentNode $segmentNode): string
    {
        $this->expressionTranspiler ??= new ExpressionTranspiler(
            scope: $this->scope,
            shouldAddQuotesIfNecessary: true
        );

        return $this->expressionTranspiler->transpile($segmentNode->expression);
    }
}
