<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

class InvalidCastRuleTest extends \PHPStan\Testing\RuleTestCase
{

    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new InvalidCastRule($this->createBroker());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/invalid-cast.php'], [
            [
                'Cannot cast stdClass to string.',
                7,
            ],
            [
                'Cannot cast array() to int.',
                16,
            ],
            [
                'Cannot cast \'blabla\' to int.',
                21,
            ],
            [
                'Cannot cast stdClass to int.',
                23,
            ],
            [
                'Cannot cast stdClass to float.',
                24,
            ],
        ]);
    }
}
