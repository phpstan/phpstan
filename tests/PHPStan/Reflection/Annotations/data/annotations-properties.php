<?php

namespace AnnotationsProperties;

use OtherNamespace\Test as OtherTest;
use OtherNamespace\Ipsum;

/**
 * @property OtherTest $otherTest
 * @property-read Ipsum $otherTestReadOnly
 * @property self|Bar $fooOrBar
 * @property Ipsum $conflictingProperty
 */
class Foo implements FooInterface
{

}

class Bar extends Foo
{

}

/**
 * @property   Lorem  $bazProperty
 * @property Dolor $conflictingProperty
 * @property-write ?Lorem $writeOnlyProperty
 */
class Baz extends Bar
{

	use FooTrait;

}

/**
 * @property int | float $numericBazBazProperty
 */
class BazBaz extends Baz
{

}

/**
 * @property FooInterface $interfaceProperty
 */
interface FooInterface
{

}

/**
 * @property BazBaz $traitProperty
 */
trait FooTrait
{

}
