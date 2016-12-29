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
class Foo
{

}

class Bar extends Foo
{

}

/**
 * @property   Lorem  $bazProperty
 * @property Dolor $conflictingProperty
 */
class Baz extends Bar
{

}

class BazBaz extends Baz
{

}
