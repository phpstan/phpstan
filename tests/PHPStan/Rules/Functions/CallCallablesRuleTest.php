<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;

class CallCallablesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallCallablesRule(
			new FunctionCallParametersCheck(
				new RuleLevelHelper($this->createBroker(), true, false, true),
				true,
				true
			),
			true
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/callables.php'], [
			[
				'Trying to invoke string but it might not be a callable.',
				17,
			],
			[
				'Callable \'date\' invoked with 0 parameters, 1-2 required.',
				21,
			],
			[
				'Trying to invoke \'nonexistent\' but it\'s not a callable.',
				25,
			],
			[
				'Parameter #1 $i of callable array($this(CallCallables\Foo), \'doBar\') expects int, string given.',
				33,
			],
			[
				'Callable array(\'CallCallables\\\\Foo\', \'doStaticBaz\') invoked with 1 parameter, 0 required.',
				39,
			],
			[
				'Callable \'CallCallables\\\\Foo:…\' invoked with 1 parameter, 0 required.',
				41,
			],
			[
				'Call to private method privateFooMethod() of class CallCallables\Foo.',
				52,
			],
			[
				'Closure invoked with 0 parameters, 1-2 required.',
				58,
			],
			[
				'Result of closure (void) is used.',
				59,
			],
			[
				'Closure invoked with 0 parameters, at least 2 required.',
				64,
			],
			[
				'Parameter #1 $i of closure expects int, string given.',
				70,
			],
			[
				'Parameter #1 $str of callable class@anonymous/tests/PHPStan/Rules/Functions/data/callables.php:75 expects string, int given.',
				81,
			],
		]);
	}

}
