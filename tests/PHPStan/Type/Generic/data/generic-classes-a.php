<?php declare(strict_types=1);

namespace PHPStan\Type\Test\A;

/** @template T */
class A {}

/** @extends A<\DateTime> */
class AOfDateTime extends A {}

/**
 * @template U
 * @extends A<U>
 */
class SubA extends A {}
