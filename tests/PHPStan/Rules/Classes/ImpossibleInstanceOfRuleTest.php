<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class ImpossibleInstanceOfRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkAlwaysTrueInstanceOf;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleInstanceOfRule($this->checkAlwaysTrueInstanceOf);
	}

	public function testInstanceof(): void
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
				[
					'Instanceof between ImpossibleInstanceOf\Test|null and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					155,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Test and ImpossibleInstanceOf\Test will always evaluate to true.',
					160,
				],
				[
					'Instanceof between callable and ImpossibleInstanceOf\FinalClassWithoutInvoke will always evaluate to false.',
					204,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Dolor and ImpossibleInstanceOf\Dolor will always evaluate to true.',
					226,
				],
				[
					'Instanceof between *NEVER* and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					228,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Bar&ImpossibleInstanceOf\Foo and ImpossibleInstanceOf\Foo will always evaluate to true.',
					232,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Bar&ImpossibleInstanceOf\Foo and ImpossibleInstanceOf\Bar will always evaluate to true.',
					232,
				],
				[
					'Instanceof between *NEVER* and ImpossibleInstanceOf\Foo will always evaluate to false.',
					234,
				],
				[
					'Instanceof between ImpossibleInstanceOf\Bar&ImpossibleInstanceOf\Foo and ImpossibleInstanceOf\Foo will always evaluate to true.',
					238,
				],
				[
					'Instanceof between *NEVER* and ImpossibleInstanceOf\Bar will always evaluate to false.',
					240,
				],
				[
					'Instanceof between int|string|null and ImpossibleInstanceOf\Foo will always evaluate to false.',
					287,
				],
			]
		);
	}

	public function testInstanceofWithoutAlwaysTrue(): void
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
				[
					'Instanceof between ImpossibleInstanceOf\Test|null and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					155,
				],
				[
					'Instanceof between callable and ImpossibleInstanceOf\FinalClassWithoutInvoke will always evaluate to false.',
					204,
				],
				[
					'Instanceof between *NEVER* and ImpossibleInstanceOf\Lorem will always evaluate to false.',
					228,
				],
				[
					'Instanceof between *NEVER* and ImpossibleInstanceOf\Foo will always evaluate to false.',
					234,
				],
				[
					'Instanceof between *NEVER* and ImpossibleInstanceOf\Bar will always evaluate to false.',
					240,
				],
				[
					'Instanceof between int|string|null and ImpossibleInstanceOf\Foo will always evaluate to false.',
					287,
				],
			]
		);
	}

}
