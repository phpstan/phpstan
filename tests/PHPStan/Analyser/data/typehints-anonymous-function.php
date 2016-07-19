<?php

namespace TypesNamespaceTypehints;

class FooWithAnonymousFunction
{

	public function doFoo()
	{
		function (
			Int $integer,
			boOl $boolean,
			String $string,
			Float $float,
			Lorem $loremObject,
			$mixed,
			Array $array,
			bool $isNullable = Null,
			Callable $callable,
			self $self
		) {
			die;
		};
	}

}
