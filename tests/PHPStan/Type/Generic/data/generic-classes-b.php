<?php declare(strict_types=1);

namespace PHPStan\Type\Test\B;

/** @template T */
interface I {}

/**
 * @template T
 * @implements I<T>
 */
class IImpl implements I {}
