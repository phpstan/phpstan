<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface SubtractableType extends Type
{

	public function subtract(Type $type): Type;

	public function getTypeWithoutSubtractedType(): Type;

	public function changeSubtractedType(?Type $subtractedType): Type;

	public function getSubtractedType(): ?Type;

}
