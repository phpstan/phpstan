<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class ImpossibleInstanceOfRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/** @var bool */
	private $checkAlwaysTrueInstanceOf;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleInstanceOfRule($this->checkAlwaysTrueInstanceOf);
	}

	public function testInstanceof()
	{
		$this->checkAlwaysTrueInstanceOf = true;
		$this->analyse(
			[__DIR__ . '/data/impossible-instanceof.php'],
			[
				[
					'Instanceof between ImpossibleInstanceOf\Lorem and ImpossibleInstanceOf\Lorem will always evaluate to true.',
					59,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Ipsum and ImpossibleInstanceOf\Lorem will always evaluate to true.',
					65,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Ipsum and ImpossibleInstanceOf\Ipsum will always evaluate to true.',
					68,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Dolor and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					71,
				],
				[
					'Instanceof between ImpossibleInstanceOf\FooImpl and ImpossibleInstanceOf\Foo will always evaluate to true.',
					74,
				],
				[
					'Instanceof between ImpossibleInstanceOf\BarChild and ImpossibleInstanceOf\Bar will always evaluate to true.',
					77,
				],
				[
					'Instanceof between string and ImpossibleInstanceOf\Foo will always evaluate to false.',
					94,
				],
			]
		);
	}

	public function testInstanceofWithoutAlwaysTrue()
	{
		$this->checkAlwaysTrueInstanceOf = false;
		$this->analyse(
			[__DIR__ . '/data/impossible-instanceof.php'],
			[
				[
					'Instanceof between ImpossibleInstanceOf\Dolor and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					71,
				],
				[
					'Instanceof between string and ImpossibleInstanceOf\Foo will always evaluate to false.',
					94,
				],
			]
		);
	}

}
