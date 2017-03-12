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
 */
class Foo
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
 */
class Baz extends Bar
{

}

/**
 * @method OtherTest getTest()
 * @method static OtherTest getTestStatically()
 * @method OtherTest getTestWithDescription() Get a test
 * @method static OtherTest getTestStaticallyWithDescription() Get a test statically
 */
class BazBaz extends Baz
{

}
