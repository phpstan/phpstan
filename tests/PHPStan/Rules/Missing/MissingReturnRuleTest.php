<?php declare(strict_types = 1);

namespace PHPStan\Rules\Missing;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class MissingReturnRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new MissingReturnRule();
	}

	public function testRule(): void
	{
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
				39,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				41,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				43,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				39,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				47,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				49,
			],
			[
				'Method MissingReturn\Foo::doLorem() should return int but return statement is missing.',
				47,
			],
			[
				'Anonymous function should return int but return statement is missing.',
				105,
			],
			[
				'Function MissingReturn\doFoo() should return int but return statement is missing.',
				112,
			],
		]);
	}

}
