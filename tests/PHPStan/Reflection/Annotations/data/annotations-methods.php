<?php

namespace AnnotationsMethods;

use OtherNamespace\Ipsum;
use OtherNamespace\Test as OtherTest;

/**
 * @method int getInteger(int $a, int $b)
 * @method void doSomething(int $a, $b)
 * @method self|Bar getFooOrBar()
 * @method methodWithNoReturnType()
 * @method static int getIntegerStatically(int $a, int $b)
 * @method static void doSomethingStatically(int $a, $b)
 * @method static self|Bar getFooOrBarStatically()
 * @method static methodWithNoReturnTypeStatically()
 * @method int getIntegerWithDescription(int $a, int $b) Get an integer with a description.
 * @method void doSomethingWithDescription(int $a, $b) Do something with a description.
 * @method self|Bar getFooOrBarWithDescription() Get a Foo or a Bar with a description.
 * @method methodWithNoReturnTypeWithDescription() Do something with a description but what, who knows!
 * @method static int getIntegerStaticallyWithDescription(int $a, int $b) Get an integer with a description statically.
 * @method static void doSomethingStaticallyWithDescription(int $a, $b) Do something with a description statically.
 * @method static self|Bar getFooOrBarStaticallyWithDescription() Get a Foo or a Bar with a description statically.
 * @method static methodWithNoReturnTypeStaticallyWithDescription() Do something with a description statically, but what, who knows!
 * @method static bool aStaticMethodThatHasAUniqueReturnTypeInThisClass()
 * @method static string aStaticMethodThatHasAUniqueReturnTypeInThisClassWithDescription() A Description.
 * @method int getIntegerNoParams
 * @method void doSomethingNoParams
 * @method self|Bar getFooOrBarNoParams
 * @method methodWithNoReturnTypeNoParams
 * @method static int getIntegerStaticallyNoParams
 * @method static void doSomethingStaticallyNoParams
 * @method static self|Bar getFooOrBarStaticallyNoParams
 * @method static methodWithNoReturnTypeStaticallyNoParams
 * @method int getIntegerWithDescriptionNoParams Get an integer with a description.
 * @method void doSomethingWithDescriptionNoParams Do something with a description.
 * @method self|Bar getFooOrBarWithDescriptionNoParams Get a Foo or a Bar with a description.
 * @method static int getIntegerStaticallyWithDescriptionNoParams Get an integer with a description statically.
 * @method static void doSomethingStaticallyWithDescriptionNoParams Do something with a description statically.
 * @method static self|Bar getFooOrBarStaticallyWithDescriptionNoParams Get a Foo or a Bar with a description statically.
 * @method static bool|string aStaticMethodThatHasAUniqueReturnTypeInThisClassNoParams
 * @method static string|float aStaticMethodThatHasAUniqueReturnTypeInThisClassWithDescriptionNoParams A Description.
 * @method \Aws\Result publish(array $args = [])
 * @method Image rotate(float $angle, $backgroundColor)
 *
 * Problem signatures
 * ==================
 * The following signatures will fail to test correctly as the __highlighted__ part is considered to be the method name.
 *
 * 1. methodWithNoReturnTypeWithDescriptionNoParams __Do__ something with a description but what, who knows!
 * 2. static methodWithNoReturnTypeStaticallyWithDescriptionNoParams __Do__ something with a description statically, but what, who knows!
 */
class Foo implements FooInterface
{

}

class Bar extends Foo
{

}

/**
 * @method Ipsum  getIpsum($a)
 * @method void doSomething(int $a, $b)
 * @method static Ipsum  getIpsumStatically($a)
 * @method static void doSomethingStatically(int $a, $b)
 * @method Ipsum getIpsumWithDescription($a) Ipsum Lorem
 * @method void doSomethingWithDescription(int $a, $b) Doing something
 * @method static Ipsum getIpsumStaticallyWithDescription($a) Lorem Ipsum Static
 * @method static void doSomethingStaticallyWithDescription(int $a, $b) Statically doing something
 * @method Ipsum  getIpsumNoParams
 * @method void doSomethingNoParams
 * @method static Ipsum  getIpsumStaticallyNoParams
 * @method static void doSomethingStaticallyNoParams
 * @method Ipsum getIpsumWithDescriptionNoParams Ipsum Lorem
 * @method void doSomethingWithDescriptionNoParams Doing something
 * @method static Ipsum getIpsumStaticallyWithDescriptionNoParams Lorem Ipsum Static
 * @method static void doSomethingStaticallyWithDescriptionNoParams Statically doing something
 */
class Baz extends Bar
{

	use FooTrait;

}

/**
 * @method OtherTest getTest()
 * @method static OtherTest getTestStatically()
 * @method OtherTest getTestWithDescription() Get a test
 * @method static OtherTest getTestStaticallyWithDescription() Get a test statically
 * @method OtherTest getTestNoParams
 * @method static OtherTest getTestStaticallyNoParams
 * @method OtherTest getTestWithDescriptionNoParams Get a test
 * @method static OtherTest getTestStaticallyWithDescriptionNoParams Get a test statically
 *
 * @method void doSomethingWithSpecificScalarParamsWithoutDefault(int $a, ?int $b, int &$c, ?int &$d)
 * @method void doSomethingWithSpecificScalarParamsWithDefault(int $a = null, ?int $b = null, int &$c = null, ?int &$d = null)
 * @method void doSomethingWithSpecificObjectParamsWithoutDefault(Ipsum $a, ?Ipsum $b, Ipsum &$c, ?Ipsum &$d)
 * @method void doSomethingWithSpecificObjectParamsWithDefault(Ipsum $a = null, ?Ipsum $b = null, Ipsum &$c = null, ?Ipsum &$d = null)
 * @method void doSomethingWithSpecificVariadicScalarParamsNotNullable(int ...$a)
 * @method void doSomethingWithSpecificVariadicScalarParamsNullable(?int ...$a)
 * @method void doSomethingWithSpecificVariadicObjectParamsNotNullable(Ipsum ...$a)
 * @method void doSomethingWithSpecificVariadicObjectParamsNullable(?Ipsum ...$a)
 * @method void doSomethingWithComplicatedParameters($a, $b = null, string|bool|int|float|OtherTest $c, string|bool|int|float|OtherTest $d = null)
 *
 * @method int | float paramMultipleTypesWithExtraSpaces(string | null $string, OtherTest | null $object)
 */
class BazBaz extends Baz
{

}

/**
 * @method FooInterface methodFromInterface()
 */
interface FooInterface
{

}

/**
 * @method BazBaz methodFromTrait()
 */
trait FooTrait
{

}
