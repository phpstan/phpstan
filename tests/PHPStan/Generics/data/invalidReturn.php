<?php

namespace PHPStan\Generics\InvalidReturn;

/**
 * @template T
 * @param T $a
 * @return T
 */
function invalidReturnA($a) {
	return 1;
}

/**
 * @template T of \DateTimeInterface
 * @param T $a
 * @return T
 */
function invalidReturnB($a) {
	return new \DateTime();
}

/**
 * @template T of \DateTime
 * @param T $a
 * @return T
 */
function invalidReturnC($a) {
	return new \DateTime();
}
