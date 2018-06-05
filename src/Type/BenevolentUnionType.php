<?php declare(strict_types = 1);

namespace PHPStan\Type;

class BenevolentUnionType extends UnionType
{

	public function describe(VerbosityLevel $level): string
	{
		return '(' . parent::describe($level) . ')';
	}

	protected function unionTypes(callable $getType): Type
	{
		$resultTypes = [];
		foreach ($this->getTypes() as $type) {
			$result = $getType($type);
			if ($result instanceof ErrorType) {
				continue;
			}

			$resultTypes[] = $result;
		}

		if (count($resultTypes) === 0) {
			return new ErrorType();
		}

		return TypeCombinator::union(...$resultTypes);
	}

}
