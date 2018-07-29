<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class OffsetAccessOnStringRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $reportMaybes;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new OffsetAccessOnStringRule($this->reportMaybes);
	}

	public function testOffsetAccessOnString(): void
	{
		$this->reportMaybes = true;
		$this->analyse(
			[__DIR__ . '/data/offset-access-on-string.php'],
			[
				[
					'[] operator not supported for strings',
					11,
				],
				[
					'[] operator not supported for strings',
					12,
				],
				[
					'Illegal string offset for string',
					15,
				],
				[
					'Illegal string offset for string',
					16,
				],
				[
					'Illegal float offset for string',
					19,
				],
				[
					'Illegal float offset for string',
					20,
				],
				[
					'Illegal int|object offset for string',
					23,
				],
				[
					'Illegal int|object offset for string',
					24,
				],
				[
					'Illegal string offset for string',
					33,
				],
				[
					'Illegal string offset for string',
					34,
				],
				[
					'Illegal float offset for string',
					38,
				],
				[
					'Illegal float offset for string',
					39,
				],
				[
					'Illegal int|object offset for string',
					43,
				],
				[
					'Illegal int|object offset for string',
					44,
				],
			]
		);
	}

	public function testOffsetAccessOnStringWithoutMaybe(): void
	{
		$this->reportMaybes = false;
		$this->analyse(
			[__DIR__ . '/data/offset-access-on-string.php'],
			[
				[
					'[] operator not supported for strings',
					11,
				],
				[
					'[] operator not supported for strings',
					12,
				],
				[
					'Illegal string offset for string',
					15,
				],
				[
					'Illegal string offset for string',
					16,
				],
				[
					'Illegal float offset for string',
					19,
				],
				[
					'Illegal float offset for string',
					20,
				],
			]
		);
	}

}
