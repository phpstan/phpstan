<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface SubtractableType extends Type
{

	public function subtract(Type $type): Type;

	public function combineWith(Type $type): Type;

}
