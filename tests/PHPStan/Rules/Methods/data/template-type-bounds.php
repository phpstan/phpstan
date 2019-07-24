<?php

namespace TemplateTypeBoundMethods;

class Foo
{
	/**
	 * @template T of int|float
	 * @param T $a
	 */
	public function a($a): void {
	}

	/**
	 * @template T of \DateTime
	 * @template U of \DateTime|\DateTimeImmutable
	 * @param T $a
	 * @param U $b
	 */
	public function b($a, $b): void {
	}

	/**
	 * @template T of NonexistentClass
	 * @param T $a
	 */
	function c($a): void {

	}

	/**
	 * @template T of foo
	 * @param T $a
	 */
	function d($a): void {

	}
}
