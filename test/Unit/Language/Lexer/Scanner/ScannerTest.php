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

namespace PackageFactory\ComponentEngine\Test\Unit\Language\Lexer\Scanner;

use AssertionError;
use PackageFactory\ComponentEngine\Language\Lexer\Scanner\Scanner;
use PackageFactory\ComponentEngine\Language\Lexer\Scanner\ScannerInterface;
use PackageFactory\ComponentEngine\Parser\Source\Position;
use PackageFactory\ComponentEngine\Test\Unit\Language\Lexer\Buffer\BufferTest;
use PackageFactory\ComponentEngine\Test\Unit\Language\Lexer\Matcher\MatcherFixtures;
use PackageFactory\ComponentEngine\Test\Unit\Language\Lexer\Rule\RuleFixtures;
use PHPUnit\Framework\TestCase;

final class ScannerTest extends TestCase
{
    /**
     * @test
     */
    public function testInitialScannerStateWhenSourceIsEmpty(): void
    {
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 0),
            expectedBufferContents: '',
            expectedIsEnd: true,
            actualScanner: new Scanner(''),
        );
    }

    /**
     * @test
     */
    public function testInitialScannerStateWhenSourceIsNotEmpty(): void
    {
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 0),
            expectedBufferContents: '',
            expectedIsEnd: false,
            actualScanner: new Scanner('A'),
        );
    }

    /**
     * @test
     */
    public function scanReturnsTrueAndCapturesMatchingCharactersIfGivenRuleMatches(): void
    {
        $scanner = new Scanner('ABC');
        $rule = RuleFixtures::withMatcher(
            MatcherFixtures::everything()
        );

        $this->assertTrue($scanner->scan($rule));
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 2),
            expectedBufferContents: 'ABC',
            expectedIsEnd: true,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanCapturesEveryCharacterUntilMatchWasFound(): void
    {
        $scanner = new Scanner('ABC');
        $rule = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(1)
        );

        $this->assertTrue($scanner->scan($rule));
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 0),
            expectedBufferContents: 'A',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

        $scanner->commit();

        $this->assertTrue($scanner->scan($rule));
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 1),
            expectedBufferEnd: Position::from(0, 1),
            expectedBufferContents: 'B',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

        $scanner->commit();

        $this->assertTrue($scanner->scan($rule));
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 2),
            expectedBufferEnd: Position::from(0, 2),
            expectedBufferContents: 'C',
            expectedIsEnd: true,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanReturnsFalseButCapturesAllMatchingCharactersUntilFailureIfGivenRuleDoesNotMatch(): void
    {
        $scanner = new Scanner('AABBCC');
        $rule = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(3)
        );

        $this->assertFalse($scanner->scan($rule));
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 3),
            expectedBufferContents: 'AABB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanCannotContinueOnceHalted(): void
    {
        $scanner = new Scanner('ABC');
        $notMatchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );

        $scanner->scan($notMatchingRule);

        $this->expectException(AssertionError::class);
        $scanner->scan($notMatchingRule);
    }

    /**
     * @test
     */
    public function scanReturnsTrueAndCapturesMatchingCharactersIfGivenRuleDoesNotMatchButTheNextRuleDoes(): void
    {
        $scanner = new Scanner('ABC');
        $notMatchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );
        $matchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::everything()
        );

        $scanner->scan($notMatchingRule);
        $scanner->dismiss();
        $scanner->scan($matchingRule);

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 2),
            expectedBufferContents: 'ABC',
            expectedIsEnd: true,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanOneOfCapturesMatchingCharactersAndReturnsTheMatchingRuleIfAnyOfTheGivenRulesMatch(): void
    {
        $scanner = new Scanner('ABC');
        $notMatchingRule1 = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );
        $notMatchingRule2 = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );
        $matchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::everything()
        );

        $this->assertSame(
            $matchingRule,
            $scanner->scanOneOf($notMatchingRule1, $matchingRule, $notMatchingRule2)
        );
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 2),
            expectedBufferContents: 'ABC',
            expectedIsEnd: true,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanOneOfReturnsNullButCapturesAllMatchingCharactersUntilFailureIfNoneOfTheGivenRulesMatch(): void
    {
        //
        // Non-Match first
        //

        $scanner = new Scanner('AABBCC');
        $notMatchingRule1 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(2)
        );
        $notMatchingRule2 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(3)
        );
        $notMatchingRule3 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(4)
        );

        $this->assertNull(
            $scanner->scanOneOf($notMatchingRule1, $notMatchingRule2, $notMatchingRule3)
        );
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 4),
            expectedBufferContents: 'AABBC',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

        //
        // Match first
        //

        $scanner = new Scanner('AAABBBCCC');
        $notMatchingRule1 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(2)
        );
        $notMatchingRule2 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(2)
        );
        $matchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(3)
        );

        $scanner->scanOneOf($notMatchingRule1, $notMatchingRule2, $matchingRule);
        $scanner->commit();
        $scanner->scanOneOf($notMatchingRule1, $notMatchingRule2);

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 3),
            expectedBufferEnd: Position::from(0, 5),
            expectedBufferContents: 'BBB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanOneOfIfTwoCompetingRulesBothMatchAtTheSameOffsetTheFirstOneThatMatchesWins(): void
    {
        $scanner = new Scanner('ABC');
        $matchingRule1 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(2)
        );
        $matchingRule2 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(2)
        );
        $notMatchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );

        $this->assertSame(
            $matchingRule1,
            $scanner->scanOneOf($matchingRule1, $matchingRule2, $notMatchingRule)
        );
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 1),
            expectedBufferContents: 'AB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanOneOfIfTwoCompetingRulesBothMatchAtDifferentOffsetsTheFirstOneThatMatchesWins(): void
    {
        $scanner = new Scanner('ABC');
        $matchingRule1 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(3)
        );
        $matchingRule2 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(2)
        );
        $notMatchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );

        $this->assertSame(
            $matchingRule2,
            $scanner->scanOneOf($matchingRule1, $matchingRule2, $notMatchingRule)
        );
        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 1),
            expectedBufferContents: 'AB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function scanOneOfCannotContinueOnceScannerIsHalted(): void
    {
        $scanner = new Scanner('ABC');
        $rule1 = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );
        $rule2 = RuleFixtures::withMatcher(
            MatcherFixtures::nothing()
        );

        $scanner->scanOneOf($rule1, $rule2);

        $this->expectException(AssertionError::class);
        $scanner->scanOneOf($rule1, $rule2);
    }

    /**
     * @test
     */
    public function dismissReturnsToLastPositionAfterScanMatch(): void
    {
        $scanner = new Scanner('AABBCC');
        $rule = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(3)
        );

        $scanner->scan($rule);
        $scanner->commit();
        $scanner->scan($rule);

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 3),
            expectedBufferEnd: Position::from(0, 5),
            expectedBufferContents: 'BCC',
            expectedIsEnd: true,
            actualScanner: $scanner,
        );

        $scanner->dismiss();

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 2),
            expectedBufferContents: 'AAB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

        $scanner->scan($rule);

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 3),
            expectedBufferEnd: Position::from(0, 5),
            expectedBufferContents: 'BCC',
            expectedIsEnd: true,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function dismissReturnsToLastPositionAfterScanMismatch(): void
    {
        $scanner = new Scanner('AAABBBCCC');
        $matchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(3)
        );
        $notMatchingRule = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(2)
        );

        $scanner->scan($matchingRule);
        $scanner->commit();
        $scanner->scan($notMatchingRule);
        $scanner->dismiss();

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 2),
            expectedBufferContents: 'AAA',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function backspaceReturnsToLastPositionAfterScanOneOfMatch(): void
    {
        $scanner = new Scanner('AABBCC');
        $rule1 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(2)
        );
        $rule2 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(3)
        );
        $rule3 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(4)
        );

        $scanner->scanOneOf($rule1, $rule2, $rule3);
        $scanner->commit();
        $scanner->scanOneOf($rule1, $rule2, $rule3);

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 2),
            expectedBufferEnd: Position::from(0, 3),
            expectedBufferContents: 'BB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

        $scanner->dismiss();

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 1),
            expectedBufferContents: 'AA',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );
    }

    /**
     * @test
     */
    public function backspaceReturnsToLastPositionAfterScanOneOfMismatch(): void
    {
        $scanner = new Scanner('AAABBBCCC');
        $rule1 = RuleFixtures::withMatcher(
            MatcherFixtures::satisfiedAtOffset(2)
        );
        $rule2 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(2)
        );
        $rule3 = RuleFixtures::withMatcher(
            MatcherFixtures::cancelAtOffset(3)
        );

        $scanner->scanOneOf($rule1, $rule2, $rule3);
        $scanner->commit();
        $scanner->scanOneOf($rule2, $rule3);

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 2),
            expectedBufferEnd: Position::from(0, 5),
            expectedBufferContents: 'ABBB',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

        $scanner->dismiss();

        $this->assertScannerState(
            expectedBufferStart: Position::from(0, 0),
            expectedBufferEnd: Position::from(0, 1),
            expectedBufferContents: 'AA',
            expectedIsEnd: false,
            actualScanner: $scanner,
        );

    }

    public static function assertScannerState(
        Position $expectedBufferStart,
        Position $expectedBufferEnd,
        string $expectedBufferContents,
        bool $expectedIsEnd,
        ScannerInterface $actualScanner,
    ): void {
        BufferTest::assertBufferState(
            expectedStart: $expectedBufferStart,
            expectedEnd: $expectedBufferEnd,
            expectedContents: $expectedBufferContents,
            actualBuffer: $actualScanner->getBuffer(),
            message: 'Buffer of scanner was incorrect'
        );

        self::assertEquals(
            $expectedIsEnd,
            $actualScanner->isEnd(),
            $expectedIsEnd
                ?  'Scanner continues unexpectedly.'
                : 'Scanner ended unexpectedly.'
        );
    }
}
