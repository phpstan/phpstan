<?php declare(strict_types = 1);

namespace PHPStan\Type;

class BenevolentUnionType extends UnionType
{

	public function describe(VerbosityLevel $level): string
	{
		return '(' . parent::describe($level) . ')';
	}

}
