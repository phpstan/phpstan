<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\RuleLevelHelper;

class OffsetAccessAssignmentToScalarRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkUnionTypes;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, $this->checkUnionTypes);
		return new OffsetAccessAssignmentToScalarRule($ruleLevelHelper);
	}

	public function testOffsetAccessAssignmentToScalar(): void
	{
		$this->checkUnionTypes = true;
		$this->analyse(
			[__DIR__ . '/data/offset-access-assignment-to-scalar.php'],
			[
				[
					'Cannot use value of type string as an array',
					10,
				],
				[
					'Cannot use value of type string as an array',
					13,
				],
				[
					'Cannot use value of type true as an array',
					22,
				],
				[
					'Cannot use value of type true as an array',
					25,
				],
				[
					'Cannot use value of type resource as an array',
					35,
				],
				[
					'Cannot use value of type resource as an array',
					39,
				],
				[
					'Cannot use value of type int as an array',
					42,
				],
				[
					'Cannot use value of type int as an array',
					45,
				],
				[
					'Cannot use value of type float as an array',
					48,
				],
				[
					'Cannot use value of type float as an array',
					51,
				],
				[
					'Cannot use value of type array|int as an array',
					55,
				],
				[
					'Cannot use value of type array|int as an array',
					59,
				],
			]
		);
	}

	public function testOffsetAccessAssignmentToScalarWithoutMaybes(): void
	{
		$this->checkUnionTypes = false;
		$this->analyse(
			[__DIR__ . '/data/offset-access-assignment-to-scalar.php'],
			[
				[
					'Cannot use value of type string as an array',
					10,
				],
				[
					'Cannot use value of type string as an array',
					13,
				],
				[
					'Cannot use value of type true as an array',
					22,
				],
				[
					'Cannot use value of type true as an array',
					25,
				],
				[
					'Cannot use value of type resource as an array',
					35,
				],
				[
					'Cannot use value of type resource as an array',
					39,
				],
				[
					'Cannot use value of type int as an array',
					42,
				],
				[
					'Cannot use value of type int as an array',
					45,
				],
				[
					'Cannot use value of type float as an array',
					48,
				],
				[
					'Cannot use value of type float as an array',
					51,
				],
			]
		);
	}

}
