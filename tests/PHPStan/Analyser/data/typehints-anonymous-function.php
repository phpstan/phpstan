<?php

namespace TypesNamespaceTypehints;

class FooWithAnonymousFunction
{

	public function doFoo()
	{
		function (
			int $integer,
			bool $boolean,
			string $string,
			float $float,
			Lorem $loremObject,
			$mixed,
			array $array,
			bool $isNullable = null,
			callable $callable,
			self $self
		) {
			die;
		};
	}

}
