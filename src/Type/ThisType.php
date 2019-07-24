<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ThisType extends StaticType
{

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('$this(%s)', $this->getStaticObjectType()->describe($level));
	}

}
