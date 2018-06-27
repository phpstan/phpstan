<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\Rules\RuleLevelHelper;

class AppendedArrayItemTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new AppendedArrayItemTypeRule(
            new PropertyReflectionFinder(),
            new RuleLevelHelper($this->createBroker(), true, false, true)
        );
    }

    public function testAppendedArrayItemType(): void
    {
        $this->analyse(
            [__DIR__ . '/data/appended-array-item.php'],
            [
                [
                    'Array (array<int>) does not accept string.',
                    18,
                ],
                [
                    'Array (array<callable>) does not accept array<int, int>.',
                    20,
                ],
                [
                    'Array (array<callable>) does not accept array<int, string>.',
                    23,
                ],
                [
                    'Array (array<callable>) does not accept array<int, string>.',
                    25,
                ],
                [
                    'Array (array<int>) does not accept string.',
                    30,
                ],
            ]
        );
    }
}
