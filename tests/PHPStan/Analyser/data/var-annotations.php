<?php

namespace VarAnnotations;

class Foo
{

	public function doFoo()
	{
		/** @var int $withTypeAndVariableInteger */
		$withTypeAndVariableInteger = getFoo();

		/** @var bool $withTypeAndVariableBoolean */
		$withTypeAndVariableBoolean = getFoo();

		/** @var string $withTypeAndVariableString */
		$withTypeAndVariableString = getFoo();

		/** @var float $withTypeAndVariableFloat */
		$withTypeAndVariableFloat = getFoo();

		/** @var Lorem $withTypeAndVariableLoremObject */
		$withTypeAndVariableLoremObject = getFoo();

		/** @var \AnotherNamespace\Bar $withTypeAndVariableBarObject */
		$withTypeAndVariableBarObject = getFoo();

		/** @var mixed $withTypeAndVariableMixed */
		$withTypeAndVariableMixed = getFoo();

		/** @var array $withTypeAndVariableArray */
		$withTypeAndVariableArray = getFoo();

		/** @var bool|null $withTypeAndVariableIsNullable */
		$withTypeAndVariableIsNullable = getFoo();

		/** @var callable $withTypeAndVariableCallable */
		$withTypeAndVariableCallable = getFoo();

		/** @var callable(int $x, string ...$y): void $withTypeAndVariableCallableWithTypes */
		$withTypeAndVariableCallableWithTypes = getFoo();

		/** @var \Closure(int $x, string ...$y): void $withTypeAndVariableClosureWithTypes */
		$withTypeAndVariableClosureWithTypes = getFoo();

		/** @var self $withTypeAndVariableSelf */
		$withTypeAndVariableSelf = getFoo();

		/** @var int $withTypeAndVariableInvalidInteger */
		$withTypeAndVariableInvalidInteger = $this->getFloat();

		/** @var static $withTypeAndVariableStatic */
		$withTypeAndVariableStatic = getFoo();

		// - - - - - - -

		/** @var int */
		$withTypeOnlyInteger = getFoo();

		/** @var bool */
		$withTypeOnlyBoolean = getFoo();

		/** @var string */
		$withTypeOnlyString = getFoo();

		/** @var float */
		$withTypeOnlyFloat = getFoo();

		/** @var Lorem */
		$withTypeOnlyLoremObject = getFoo();

		/** @var \AnotherNamespace\Bar */
		$withTypeOnlyBarObject = getFoo();

		/** @var mixed */
		$withTypeOnlyMixed = getFoo();

		/** @var array */
		$withTypeOnlyArray = getFoo();

		/** @var bool|null */
		$withTypeOnlyIsNullable = getFoo();

		/** @var callable */
		$withTypeOnlyCallable = getFoo();

		/** @var callable(int $x, string &...$y): void */
		$withTypeOnlyCallableWithTypes = getFoo();

		/** @var \Closure(int $x, string &...$y): void */
		$withTypeOnlyClosureWithTypes = getFoo();

		/** @var self */
		$withTypeOnlySelf = getFoo();

		/** @var int */
		$withTypeOnlyInvalidInteger = $this->getFloat();

		/** @var static */
		$withTypeOnlyStatic = getFoo();

		die;
	}

	public function getFloat(): float
	{
		return 1.0;
	}

}
