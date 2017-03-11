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
                16,
            ],
            [
                'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns int.',
                24,
            ],
            [
                'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
                25,
            ],
            [
                'Method ReturnTypes\Foo::returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
                33,
            ],
            [
                'Method ReturnTypes\Foo::returnVoid() with return type void returns null but should not return anything.',
                56,
            ],
            [
                'Method ReturnTypes\Foo::returnVoid() with return type void returns int but should not return anything.',
                57,
            ],
            [
                'Method ReturnTypes\Foo::returnStatic() should return static(ReturnTypes\Foo) but returns ReturnTypes\FooParent.',
                68,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Foo.',
                90,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar.',
                92,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar[].',
                93,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns int.',
                94,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but empty return statement found.',
                95,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar[]|ReturnTypes\Collection.',
                99,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Foo[]|ReturnTypes\AnotherCollection.',
                103,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Foo[]|ReturnTypes\Collection|ReturnTypes\AnotherCollection.',
                107,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns ReturnTypes\Bar[]|ReturnTypes\AnotherCollection.',
                111,
            ],
            [
                'Method ReturnTypes\Foo::returnUnionIterableType() should return ReturnTypes\Foo[]|ReturnTypes\Collection but returns null.',
                113,
            ],
            [
                'Method ReturnTypes\Foo::returnThis() should return $this but returns new self().',
                132,
            ],
            [
                'Method ReturnTypes\Foo::returnThis() should return $this but returns 1.',
                133,
            ],
            [
                'Method ReturnTypes\Foo::returnThis() should return $this but returns null.',
                134,
            ],
            [
                'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns new self().',
                146,
            ],
            [
                'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns 1.',
                147,
            ],
            [
                'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns $this->returnStaticThatReturnsNewStatic().',
                150,
            ],
            [
                'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns int.',
                165,
            ],
            [
                'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns null.',
                166,
            ],
            [
                'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns int.',
                172,
            ],
            [
                'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns null.',
                173,
            ],
            [
                'Method ReturnTypes\Foo::returnScalar() should return int|float|string|bool but returns stdClass.',
                185,
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
