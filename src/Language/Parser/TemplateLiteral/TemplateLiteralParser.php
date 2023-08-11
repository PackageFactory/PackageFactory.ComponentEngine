<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2023 Contributors of PackageFactory.ComponentEngine
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

namespace PackageFactory\ComponentEngine\Language\Parser\TemplateLiteral;

use PackageFactory\ComponentEngine\Framework\PHP\Singleton\Singleton;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralExpressionSegmentNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralLine;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralLines;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralNode;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralSegments;
use PackageFactory\ComponentEngine\Language\AST\Node\TemplateLiteral\TemplateLiteralStringSegmentNode;
use PackageFactory\ComponentEngine\Language\Lexer\Lexer;
use PackageFactory\ComponentEngine\Language\Lexer\Token\TokenType;
use PackageFactory\ComponentEngine\Language\Parser\Expression\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Source\Range;

final class TemplateLiteralParser
{
    use Singleton;

    private ?ExpressionParser $expressionParser = null;

    public function parse(Lexer $lexer): TemplateLiteralNode
    {
        $lexer->read(TokenType::TEMPLATE_LITERAL_DELIMITER);
        $start = $lexer->getStartPosition();

        $lines = $this->parseLines($lexer);

        $lexer->read(TokenType::TEMPLATE_LITERAL_DELIMITER);
        $end = $lexer->getEndPosition();

        return new TemplateLiteralNode(
            rangeInSource: Range::from($start, $end),
            indentation: $lexer->getStartPosition()->columnNumber,
            lines: $lines
        );
    }

    public function parseLines(Lexer $lexer): TemplateLiteralLines
    {
        $lexer->read(TokenType::END_OF_LINE);
        $lexer->probe(TokenType::SPACE);

        $items = [];
        while (!$lexer->peek(TokenType::TEMPLATE_LITERAL_DELIMITER)) {
            $items[] = $this->parseLine($lexer);
            $lexer->read(TokenType::END_OF_LINE);
            $lexer->probe(TokenType::SPACE);
        }

        return new TemplateLiteralLines(...$items);
    }

    public function parseLine(Lexer $lexer): TemplateLiteralLine
    {
        $segments = $this->parseSegments($lexer);
        $indentation = $segments->items[0]?->rangeInSource->start->columnNumber ?? 0;

        return new TemplateLiteralLine(
            indentation: $indentation,
            segments: $segments
        );
    }

    public function parseSegments(Lexer $lexer): TemplateLiteralSegments
    {
        $items = [];
        while (!$lexer->peek(TokenType::END_OF_LINE)) {
            if ($lexer->peek(TokenType::BRACKET_CURLY_OPEN)) {
                $items[] = $this->parseExpressionSegment($lexer);
                continue;
            }
            $items[] = $this->parseStringSegment($lexer);
        }

        return new TemplateLiteralSegments(...$items);
    }

    public function parseStringSegment(Lexer $lexer): TemplateLiteralStringSegmentNode
    {
        $lexer->read(TokenType::TEMPLATE_LITERAL_CONTENT);

        return new TemplateLiteralStringSegmentNode(
            rangeInSource: $lexer->getCursorRange(),
            value: $lexer->getBuffer()
        );
    }

    public function parseExpressionSegment(Lexer $lexer): TemplateLiteralExpressionSegmentNode
    {
        $this->expressionParser ??= new ExpressionParser();

        $lexer->read(TokenType::BRACKET_CURLY_OPEN);
        $start = $lexer->getStartPosition();
        $lexer->skipSpaceAndComments();

        $expression = $this->expressionParser->parse($lexer);

        $lexer->skipSpaceAndComments();
        $lexer->read(TokenType::BRACKET_CURLY_CLOSE);
        $end = $lexer->getEndPosition();

        return new TemplateLiteralExpressionSegmentNode(
            rangeInSource: Range::from($start, $end),
            expression: $expression
        );
    }
}
