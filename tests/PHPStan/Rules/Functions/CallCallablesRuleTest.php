<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;

class CallCallablesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, true);
		return new CallCallablesRule(
			new FunctionCallParametersCheck(
				$ruleLevelHelper,
				true,
				true
			),
			$ruleLevelHelper,
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
				'Closure invoked with 0 parameters, at least 1 required.',
				64,
			],
			[
				'Parameter #1 $i of closure expects int, string given.',
				70,
			],
			[
				DIRECTORY_SEPARATOR === '/' ? 'Parameter #1 $str of callable AnonymousClass7bc4507c47746826fae3d9665724d180 expects string, int given.' : 'Parameter #1 $str of callable AnonymousClass49c093c6ce55676fc906e795bc0d425c expects string, int given.',
				81,
			],
			[
				'Trying to invoke \'\' but it\'s not a callable.',
				86,
			],
			[
				'Invoking callable on an unknown class CallCallables\Bar.',
				90,
			],
			[
				'Parameter #1 ...$foo of closure expects CallCallables\Foo, array<CallCallables\Foo> given.',
				106,
			],
			[
				'Trying to invoke CallCallables\Baz but it might not be a callable.',
				113,
			],
			[
				'Trying to invoke array(object, \'bar\') but it\'s not a callable.',
				131,
			],
			[
				'Closure invoked with 0 parameters, 3 required.',
				146,
			],
			[
				'Closure invoked with 1 parameter, 3 required.',
				147,
			],
			[
				'Closure invoked with 2 parameters, 3 required.',
				148,
			],
		]);
	}

}
