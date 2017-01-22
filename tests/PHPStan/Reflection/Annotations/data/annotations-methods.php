<?php

namespace AnnotationsMethods;

use OtherNamespace\Ipsum;
use OtherNamespace\Test as OtherTest;

/**
 * @method int getInteger(int $a, int $b)
 * @method void doSomething(int $a, $b)
 * @method self|Bar getFooOrBar()
 * @method methodWithNoReturnType()
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
 */
class Baz extends Bar
{

}

/**
 * @method OtherTest getTest()
 */
class BazBaz extends Baz
{

}
