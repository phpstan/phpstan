<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface TypeWithClassName extends Type
{

	public function getClassName(): string;

}
