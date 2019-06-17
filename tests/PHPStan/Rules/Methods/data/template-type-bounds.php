<?php

namespace TemplateTypeBound;

class C
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
}
