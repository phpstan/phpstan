<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\Properties\PropertyReflectionFinder;

class AppendedArrayKeyTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new AppendedArrayKeyTypeRule(
            new PropertyReflectionFinder(),
            true
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/appended-array-key.php'], [
            [
                'Array (array<int, mixed>) does not accept key int|string.',
                28,
            ],
            [
                'Array (array<int, mixed>) does not accept key string.',
                30,
            ],
            [
                'Array (array<string, mixed>) does not accept key int.',
                31,
            ],
            [
                'Array (array<string, mixed>) does not accept key int|string.',
                33,
            ],
            [
                'Array (array<string, mixed>) does not accept key 0.',
                38,
            ],
            [
                'Array (array<string, mixed>) does not accept key 1.',
                46,
            ],
        ]);
    }
}
