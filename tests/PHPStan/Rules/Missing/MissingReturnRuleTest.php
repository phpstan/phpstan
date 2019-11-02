<?php declare(strict_types = 1);

namespace PHPStan\Rules\Missing;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class MissingReturnRuleTest extends RuleTestCase
{

	/** @var bool */
	private $checkExplicitMixedMissingReturn;

	protected function getRule(): Rule
	{
		return new MissingReturnRule($this->checkExplicitMixedMissingReturn, true);
	}

	public function testRule(): void
	{
		$this->checkExplicitMixedMissingReturn = true;

		$this->analyse([__DIR__ . '/data/missing-return.php'], [
			[
				'Method MissingReturn\Foo::doFoo() should return int but return statement is missing.',
				8,
			],
			[
				'Method MissingReturn\Foo::doBar() should return int but return statement is missing.',
				16,
			],
			[
				'Method MissingReturn\Foo::doBaz() should return int but return statement is missing.',
				21,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				36,
			],
			[
				'Anonymous function should return int but return statement is missing.',
				105,
			],
			[
				'Function MissingReturn\doFoo() should return int but return statement is missing.',
				112,
			],
			[
				'Method MissingReturn\SwitchBranches::doBar() should return int but return statement is missing.',
				146,
			],
			[
				'Method MissingReturn\SwitchBranches::doLorem() should return int but return statement is missing.',
				172,
			],
			[
				'Method MissingReturn\SwitchBranches::doIpsum() should return int but return statement is missing.',
				182,
			],
			[
				'Method MissingReturn\SwitchBranches::doDolor() should return int but return statement is missing.',
				193,
			],
			[
				'Method MissingReturn\TryCatchFinally::doBaz() should return int but return statement is missing.',
				234,
			],
			[
				'Method MissingReturn\TryCatchFinally::doDolor() should return int but return statement is missing.',
				263,
			],
			[
				'Method MissingReturn\ReturnInPhpDoc::doFoo() should return int but return statement is missing.',
				290,
			],
			[
				'Method MissingReturn\FooTemplateMixedType::doFoo() should return T but return statement is missing.',
				321,
			],
		]);
	}

	public function testCheckMissingReturnWithTemplateMixedType(): void
	{
		$this->checkExplicitMixedMissingReturn = false;
		$this->analyse([__DIR__ . '/data/missing-return-template-mixed-type.php'], [
			[
				'Method MissingReturnTemplateMixedType\Foo::doFoo() should return T but return statement is missing.',
				13,
			],
		]);
	}
}
