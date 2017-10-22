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
				[
					'Instanceof between string and string will always evaluate to false.',
					98,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test and ImpossibleInstanceOf\Test will always evaluate to true.',
					107,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test|null and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					119,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test and ImpossibleInstanceOf\Test will always evaluate to true.',
					124,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test|null and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					137,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test and ImpossibleInstanceOf\Test will always evaluate to true.',
					142,
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
				[
					'Instanceof between string and string will always evaluate to false.',
					98,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test|null and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					119,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test|null and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					137,
				],
			]
		);
	}

}
