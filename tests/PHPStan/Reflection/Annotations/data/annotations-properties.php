<?php

namespace AnnotationsProperties;

use OtherNamespace\Test as OtherTest;
use OtherNamespace\Ipsum;

/**
 * @property OtherTest $otherTest
 * @property-read Ipsum $otherTestReadOnly
 * @property self|Bar $fooOrBar
 * @property Ipsum $conflictingProperty
 * @property Foo $overridenProperty
 */
class Foo implements FooInterface
{

	/** @var Foo */
	public $overridenPropertyWithAnnotation;

}

/**
 * @property Bar $overridenPropertyWithAnnotation
 * @property Foo $conflictingAnnotationProperty
 */
class Bar extends Foo
{

	/** @var Bar */
	public $overridenProperty;

	/** @var Bar */
	public $conflictingAnnotationProperty;

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
