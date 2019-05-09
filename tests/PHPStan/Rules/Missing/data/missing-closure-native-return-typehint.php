<?php

namespace MissingClosureNativeReturnTypehint;

class Foo
{

	public function doFoo()
	{
		function () {

		};
		function () {
			return;
		};
		function (bool $bool) {
			if ($bool) {
				return;
			} else {
				yield 1;
			}
		};
		function (bool $bool) {
			if ($bool) {
				return;
			} else {
				return 1;
			}
		};
		function (): int {
			return 1;
		};
		function (bool $bool) {
			if ($bool) {
				return null;
			} else {
				return 1;
			}
		};
		function (bool $bool) {
			if ($bool) {
				return 1;
			}
		};

		function () {
			$array = [
				'foo' => 'bar',
			];

			return $array;
		};
	}

}
