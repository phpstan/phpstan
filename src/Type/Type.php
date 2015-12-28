<?php declare(strict_types=1);

namespace PHPStan\Type;

interface Type
{

	/**
	 * @return string|null
	 */
	public function getClass();

	public function isNullable(): bool;

	public function combineWith(Type $otherType): Type;

	public function makeNullable(): Type;

}
