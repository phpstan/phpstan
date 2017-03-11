<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionReturnTypeCheck;

class ReturnTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ReturnTypeRule(new FunctionReturnTypeCheck(new \PhpParser\PrettyPrinter\Standard()));
    }

    public function testReturnTypeRule()
    {
        $this->analyse([__DIR__ . '/data/returnTypes.php'], [
            [
                'Function ReturnTypes\returnInteger() should return int but returns string.',
                13,
            ],
            [
                'Function ReturnTypes\returnObject() should return ReturnTypes\Bar but returns int.',
                21,
            ],
            [
                'Function ReturnTypes\returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
                22,
            ],
            [
                'Function ReturnTypes\returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
                30,
            ],
            [
                'Function ReturnTypes\returnVoid() with return type void returns null but should not return anything.',
                53,
            ],
            [
                'Function ReturnTypes\returnVoid() with return type void returns int but should not return anything.',
                54,
            ],
        ]);
    }

    public function testReturnTypeRulePhp70()
    {
        if (PHP_VERSION_ID >= 70100) {
            $this->markTestSkipped(
                'Test can be run only on PHP 7.0 - higher versions fail with the following test in the parse phase.'
            );
        }
        $this->analyse([__DIR__ . '/data/returnTypes-7.0.php'], [
            [
                'Function ReturnTypes\Php70\returnInteger() should return int but empty return statement found.',
                7,
            ],
        ]);
    }
}
