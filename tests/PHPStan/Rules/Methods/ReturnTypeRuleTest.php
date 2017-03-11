<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

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
                'Method ReturnTypes\Foo::returnInteger() should return int but returns string.',
                15,
            ],
            [
                'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns int.',
                23,
            ],
            [
                'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
                24,
            ],
            [
                'Method ReturnTypes\Foo::returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
                32,
            ],
            [
                'Method ReturnTypes\Foo::returnVoid() with return type void returns null but should not return anything.',
                55,
            ],
            [
                'Method ReturnTypes\Foo::returnVoid() with return type void returns int but should not return anything.',
                56,
            ],
            [
                'Method ReturnTypes\Foo::returnStatic() should return static(ReturnTypes\Foo) but returns ReturnTypes\FooParent.',
                67,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Foo.',
                89,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar.',
                91,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar[].',
                92,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns int.',
                93,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but empty return statement found.',
                94,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar[]|ReturnTypes\Collection.',
                98,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Foo[]|ReturnTypes\AnotherCollection.',
                102,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Foo[]|ReturnTypes\Collection|ReturnTypes\AnotherCollection.',
                106,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar[]|ReturnTypes\AnotherCollection.',
                110,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns null.',
                112,
            ],
            [
                'Method ReturnTypes\Foo::returnThis() should return $this but returns new self().',
                131,
            ],
            [
                'Method ReturnTypes\Foo::returnThis() should return $this but returns 1.',
                132,
            ],
            [
                'Method ReturnTypes\Foo::returnThis() should return $this but returns null.',
                133,
            ],
            [
                'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns new self().',
                145,
            ],
            [
                'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns 1.',
                146,
            ],
            [
                'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns $this->returnStaticThatReturnsNewStatic().',
                149,
            ],
            [
                'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns int.',
                164,
            ],
            [
                'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns null.',
                165,
            ],
            [
                'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns int.',
                171,
            ],
            [
                'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns null.',
                172,
            ],
            [
                'Method ReturnTypes\Foo::returnScalar() should return int|float|string|bool but returns stdClass.',
                184,
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
                'Method ReturnTypes\FooPhp70::returnInteger() should return int but empty return statement found.',
                10,
            ],
        ]);
    }
}
