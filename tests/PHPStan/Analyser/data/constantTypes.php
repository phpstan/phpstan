<?php

namespace ConstantTypesIncrementDecrement;

class Foo
{

	/** @var int */
	private $intProperty;

	/** @var int */
	private static $staticIntProperty;

	/** @var int */
	private $anotherIntProperty;

	/** @var int */
	private static $anotherStaticIntProperty;

	public function doFoo()
	{
		$postIncrement = 1;
		$postIncrement++;

		$preIncrement = 1;
		++$preIncrement;

		$postDecrement = 5;
		$postDecrement--;

		$preDecrement = 5;
		--$preDecrement;

		$literalArray = [
			'a' => 1,
			'b' => 5,
			'c' => 1,
			'd' => 5,
		];
		$literalArray['a']++;
		$literalArray['b']--;
		++$literalArray['c'];
		--$literalArray['d'];

		$nullIncremented = null;
		$nullDecremented = null;
		$nullIncremented++;
		$nullDecremented--;

		$incrementInIf = 1;
		$anotherIncrementInIf = 1;
		$valueOverwrittenInIf = 1;
		$anotherValueOverwrittenInIf = 10;
		$appendingToArrayInBranches = [];

		$this->anotherIntProperty = 1;
		self::$anotherStaticIntProperty = 1;
		if (doFoo()) {
			$incrementInIf++;
			$anotherIncrementInIf++;
			$valueOverwrittenInIf = 2;
			$anotherValueOverwrittenInIf /= 2;
			$appendingToArrayInBranches[] = 1;
			$appendingToArrayInBranches[] = 2;
			$this->anotherIntProperty++;
			self::$anotherStaticIntProperty++;
		} elseif (doBar()) {
			$incrementInIf++;
			$incrementInIf++;
			$anotherIncrementInIf++;
			$anotherIncrementInIf++;
		} else {
			$anotherIncrementInIf++;
		}

		$incrementInForLoop = 1;
		$valueOverwrittenInForLoop = 1;
		$arrayOverwrittenInForLoop = [
			'a' => 1,
			'b' => 'foo',
		];

		$this->intProperty = 1;
		self::$staticIntProperty = 1;
		for ($i = 0; $i < 10; $i++) {
			$incrementInForLoop++;
			$valueOverwrittenInForLoop = 2;
			$arrayOverwrittenInForLoop['a']++;
			$arrayOverwrittenInForLoop['b'] = 'bar';
			$this->intProperty++;
			self::$staticIntProperty++;
		}

		$intProperty = $this->intProperty;
		$staticIntProperty = self::$staticIntProperty;
		$anotherIntProperty = $this->anotherIntProperty;
		$anotherStaticIntProperty = self::$anotherStaticIntProperty;

		die;
	}

}
