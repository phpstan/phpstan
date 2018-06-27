<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class ImpossibleCheckTypeFunctionCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

    /** @var bool */
    private $checkAlwaysTrueCheckTypeFunctionCall;

    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ImpossibleCheckTypeFunctionCallRule(
            new ImpossibleCheckTypeHelper(
                $this->getTypeSpecifier()
            ),
            $this->checkAlwaysTrueCheckTypeFunctionCall
        );
    }

    public function testImpossibleCheckTypeFunctionCall(): void
    {
        $this->checkAlwaysTrueCheckTypeFunctionCall = true;
        $this->analyse(
            [__DIR__ . '/data/check-type-function-call.php'],
            [
                [
                    'Call to function is_int() with int will always evaluate to true.',
                    25,
                ],
                [
                    'Call to function is_int() with string will always evaluate to false.',
                    31,
                ],
                [
                    'Call to function is_callable() with array<int> will always evaluate to false.',
                    44,
                ],
                [
                    'Call to function assert() with false will always evaluate to false.',
                    48,
                ],
                [
                    'Call to function is_callable() with \'date\' will always evaluate to true.',
                    84,
                ],
                [
                    'Call to function is_callable() with \'nonexistentFunction\' will always evaluate to false.',
                    87,
                ],
                [
                    'Call to function is_numeric() with \'123\' will always evaluate to true.',
                    102,
                ],
                [
                    'Call to function is_numeric() with \'blabla\' will always evaluate to false.',
                    105,
                ],
                [
                    'Call to function is_numeric() with 123|float will always evaluate to true.',
                    118,
                ],
            ]
        );
    }

    public function testImpossibleCheckTypeFunctionCallWithoutAlwaysTrue(): void
    {
        $this->checkAlwaysTrueCheckTypeFunctionCall = false;
        $this->analyse(
            [__DIR__ . '/data/check-type-function-call.php'],
            [
                [
                    'Call to function is_int() with string will always evaluate to false.',
                    31,
                ],
                [
                    'Call to function is_callable() with array<int> will always evaluate to false.',
                    44,
                ],
                [
                    'Call to function assert() with false will always evaluate to false.',
                    48,
                ],
                [
                    'Call to function is_callable() with \'nonexistentFunction\' will always evaluate to false.',
                    87,
                ],
                [
                    'Call to function is_numeric() with \'blabla\' will always evaluate to false.',
                    105,
                ],
            ]
        );
    }
}
