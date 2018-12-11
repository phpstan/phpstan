<?php

namespace Php73Functions;

class Foo
{

	/**
	 * @param $mixed
	 * @param array $mixedArray
	 * @param array $nonEmptyArray
	 * @param array<string, mixed> $arrayWithStringKeys
	 */
	public function doFoo(
		$mixed,
		array $mixedArray,
		array $nonEmptyArray,
		array $arrayWithStringKeys
	)
	{
		if (count($nonEmptyArray) === 0) {
			return;
		}

		$emptyArray = [];
		$literalArray = [1, 2, 3];
		$anotherLiteralArray = $literalArray;
		if (rand(0, 1) === 0) {
			$anotherLiteralArray[] = 4;
		}

		/** @var bool $bool */
		$bool = doBar();

		$hrtime1 = hrtime();
		$hrtime2 = hrtime(false);
		$hrtime3 = hrtime(true);
		$hrtime4 = hrtime($bool);

		die;
	}

}
