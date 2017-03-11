<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class ThisVariableRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ThisVariableRule();
    }

    public function testReturnTypeRule()
    {
        $this->analyse([__DIR__ . '/data/this.php'], [
            [
                'Using $this in static method ThisVariable\Foo::doBar().',
                15,
            ],
            [
                'Using $this outside a class.',
                22,
            ],
            [
                'Using $this in static method class@anonymous',
                33,
                false,
            ],
        ]);
    }
}
