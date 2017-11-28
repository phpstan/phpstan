<?php

namespace VarAnnotations;

class Foo
{

	public function doFoo()
	{
		/** @var int $integer */
		$integer = getFoo();

		/** @var bool $boolean */
		$boolean = getFoo();

		/** @var string $string */
		$string = getFoo();

		/** @var float $float */
		$float = getFoo();

		/** @var Lorem $loremObject */
		$loremObject = getFoo();

		/** @var \AnotherNamespace\Bar $barObject */
		$barObject = getFoo();

		/** @var $mixed */
		$mixed = getFoo();

		/** @var array $array */
		$array = getFoo();

		/** @var bool|null $isNullable */
		$isNullable = getFoo();

		/** @var callable $callable */
		$callable = getFoo();

		/** @var self $self */
		$self = getFoo();

		/** @var int $invalidInt */
		$invalidInteger = 1.0;

		/** @var static $static */
		$static = getFoo();

		die;
	}

	public function doFooBar()
	{
		/** @var int */
		$integer = getFoo();

		/** @var bool */
		$boolean = getFoo();

		/** @var string */
		$string = getFoo();

		/** @var float */
		$float = getFoo();

		/** @var Lorem */
		$loremObject = getFoo();

		/** @var \AnotherNamespace\Bar */
		$barObject = getFoo();

		/** @var */
		$mixed = getFoo();

		/** @var array */
		$array = getFoo();

		/** @var bool|null */
		$isNullable = getFoo();

		/** @var callable */
		$callable = getFoo();

		/** @var self */
		$self = getFoo();

		/** @var float */
		$invalidInteger = 1.0;

		/** @var static */
		$static = getFoo();

		die;
	}

}
